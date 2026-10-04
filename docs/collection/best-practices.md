# Best Practices

Practical patterns and common pitfalls when working with collections.

## Prefer Type-Specific Maps

When your keys are exclusively strings or ints, use `stringMapOf` / `intMapOf` (and their mutable variants `mutableStringMapOf` / `mutableIntMapOf`). They use single-array storage (~50% less memory), have no key hashing overhead, and the API is identical to `mapOf()`.

They are also safer with numeric string keys. `stringMapOf` accepts `array<string|int, V>` and normalizes all keys to `string` — so PHP's automatic `'1'` → `1` cast is handled for you:

```php
// Surprise - mapOf sees int key because PHP already cast '1' to 1
$map = mapOf(['1' => 'a']);
$map->containsKey('1'); // false — key is int(1)

// Safe - StringMap normalizes back to string
$map = stringMapOf(['1' => 'a']);
$map->containsKey('1'); // true
$map->containsKey(1); // false
```

::: warning
`mapOf(['1' => 'a'])` creates a `Map<int, string>` because PHP cast the key before the map received it. PHPStan will infer `Map<int, string>` and flag `$map->get('1')` as a type error. Use `stringMapOf()` or `mapOfPairs([['1', 'a']])` when you need string keys.
:::

### Use `mapOfPairs` for Object Keys

`mapOfPairs` returns the same `Map` as `mapOf` — it's just an alternative constructor for cases where PHP arrays can't represent your keys. Since PHP arrays cannot store objects, bools, or floats as keys, pairs are the only way to construct such maps. As a bonus, it also preserves string key types that PHP arrays would cast:

```php
$map = mapOfPairs([
    [$user, 'Rodney'],
    [$admin, 'Weir'],
    ['1', 'also works — key stays string'],
]);
```

## Build Mutable, Then Convert

When you need to construct a collection incrementally, build it as mutable and convert at the end:

```php
// Good - single mutable instance, one conversion at the end
$map = mutableMapOf();
foreach ($rows as $row) {
    $map->put($row->id, $row->name);
}
$result = $map->toImmutable();
```

```php
// Bad - each put() clones the entire internal store
$map = mapOf();
foreach ($rows as $row) {
    $map = $map->put($row->id, $row->name);
}
```

Every mutating method on an immutable collection creates a copy of the underlying data. With 1,000 rows, the first approach allocates once; the second allocates 1,000 times; each bigger than the last.

::: tip
`toImmutable()` and `toMutable()` use copy-on-write internally, so the conversion itself is cheap — no data is duplicated until one side is modified.
:::

## Use Lazy Init for Expensive Data

Wrap expensive computations in a closure to defer them until first access:

```php
// Good - query runs only when the map is actually accessed
$users = mapOf(fn() => $db->fetchAll('SELECT id, name FROM users'));

// Bad - query runs immediately, even if $users is never used
$users = mapOf($db->fetchAll('SELECT id, name FROM users'));
```

This is especially useful for collections passed to templates or optional code paths where the data might not be needed.

### Closures vs Generators

Always use closures (`fn() => ...`) for lazy initialization, not generators:

```php
// Good - closure is called when needed, can be retried
$list = listOf(fn() => loadElements());

// Bad - generator is consumed on construction, not truly lazy
$list = listOf(loadElementsGenerator());
```

Generators are consumed immediately when the collection is created. Closures defer execution until the first access to the collection.

Sequences are the exception — `sequenceOf($generator)` stays lazy, but can be iterated only once. See [Sequence](./sequence#iterating-more-than-once).

## Use Views for Aggregation

Map views (`$keys`, `$values`, `$entries`) are full collection objects. Use them instead of manual loops:

```php
// Good - expressive and concise
$total = $orders->values->sum();
$avgPrice = $orders->values->avg();
$topKey = $orders->entries->find(fn($e) => $e->value > 1000)?->key;
$allKeys = $orders->keys->joinToString(', ');

// Verbose - manual iteration for the same result
$total = 0;
foreach ($orders as $key => $value) {
    $total += $value;
}
```

Views are connected directly to the map's internal store — there is no copying overhead. On mutable maps, views stay in sync automatically after mutations.

## Chain Transformations Freely

Transformation methods (`filter`, `map`, `sorted`, etc.) always return immutable collections and are designed for chaining:

```php
$result = $users
    ->filter(fn($user) => $user->isActive())
    ->sortedWith(fn($a, $b) => $a->name <=> $b->name)
    ->map(fn($user) => $user->name)
    ->distinct()
    ->joinToString(', ');
```

Each step in the chain creates a new immutable collection. This is fine for typical data sizes. For very large collections where you need to chain multiple transformations, consider working with mutable:

```php
$result = $users->toMutable()
    ->removeIf(fn($user) => !$user->isActive())
    ->sortWith(fn($a, $b) => $a->name <=> $b->name)
    ->map(fn($user) => $user->name)
    ->distinct()
    ->joinToString(', ');
```

When the chain needs no sorting, or ends early with `first()`, `find()` or `takeFirst()`, a [sequence](./sequence) skips the intermediate collections altogether:

```php
$result = $users->asSequence()
    ->filter(fn($user) => $user->isActive())
    ->map(fn($user) => $user->name)
    ->distinct()
    ->joinToString(', ');
```

## Materialize Sequences You Read Twice

A sequence stores nothing — every terminal operation runs the whole pipeline again, including the query behind it. Keep the lazy part for what shrinks the data, then materialize it once:

```php
// Bad - two terminal operations, the query runs twice
$paid = sequenceOf(fn() => $db->cursor('SELECT * FROM orders'))
    ->filter(fn($order) => $order['status'] === 'paid');
$total = $paid->sum(fn($order) => $order['amount']);
$count = $paid->count();

// Good - one pass, only the paid orders are kept in memory
$paid = sequenceOf(fn() => $db->cursor('SELECT * FROM orders'))
    ->filter(fn($order) => $order['status'] === 'paid')
    ->toList();
$total = $paid->sum(fn($order) => $order['amount']);
$count = $paid->count(); // O(1) on a list
```

When a second pass would always be a bug, pass `constrainOnce: true` to `sequenceOf()` — the second terminal operation then throws instead of quietly running the query again.

## Don't Discard Immutable Results

All mutating methods on immutable collections are marked `#[NoDiscard]`. The most common mistake is calling a method without using the return value:

```php
// Bug - the result is thrown away, $list is unchanged
$list = listOf([1, 2, 3]);
$list->add(4);
$list->sorted();

// Correct - capture the returned collection
$list = listOf([1, 2, 3]);
$list = $list->add(4);
$list = $list->sorted();

// Also correct - chain in one expression
$list = listOf([1, 2, 3])->add(4)->sorted();
```

PHP 8.5 natively catches this and produces a warning at runtime.

## Use `tracked()` for Conditional Logic

When you need to know whether a mutation actually changed something, use `tracked()` instead of before/after comparisons:

```php
// Good
$tracked = $cache->tracked();
if ($tracked->remove($key)->changed) {
    $logger->info("Cache entry evicted: $key");
}

// Verbose - manual comparison
$before = $cache->count();
$cache->remove($key);
if ($cache->count() < $before) {
    $logger->info("Cache entry evicted: $key");
}
```

The `$changed` property is on the return value of each mutation method — each call gives you a fresh result reflecting whether that specific operation modified the data.

## Return Immutable from Public APIs

When exposing collections from methods or properties, prefer immutable types:

```php
class UserRepository
{
    // Good - callers cannot corrupt internal state
    public function findActive(): ImmutableList
    {
        return $this->users->filter(fn($u) => $u->isActive());
    }

    // Risky - callers can modify the returned collection
    public function findActive(): MutableList
    {
        return $this->activeUsers;
    }
}
```

Transformation methods already return immutable collections, so this comes naturally.

## Avoid `toArray()` with Mixed Key Types

`toArray()` throws when PHP's key casting would cause collisions. If you have mixed-type keys, choose an explicit strategy:

```php
$map = mapOfPairs([['1', 'a'], [1, 'b']]);

$map->toArray(); // throws ConversionException

// Pick a strategy
$map->toArray(KeyCollisionStrategy::KeepFirst); // [1 => 'a']
$map->toArray(KeyCollisionStrategy::KeepLast); // [1 => 'b']

// Or transform keys first
$map->mapKeys(fn($v, $k) => gettype($k) . "_$k")->toArray(); // ['string_1' => 'a', 'integer_1' => 'b']

// Or use toPairs() which always works
$map->toPairs(); // [['1', 'a'], [1, 'b']]
```

Maps created via `stringMapOf()` or `intMapOf()` are always safe to call `toArray()` on.
