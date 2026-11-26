# Mutability

Every collection type in the library has both a mutable and an immutable variant. The choice affects how mutating operations (add, removeElement, sort, clear) behave.

## Mutable collections

Mutable collections modify themselves in-place. Mutating methods return `$this`, enabling method chaining:

```php
$list = mutableListOf([3, 1, 2]);

$list->add(4)->sort()->removeElement(1);
// $list is now [2, 3, 4]
```

### The `tracked()` method {#change-tracking}

Mutable collections can track whether operations actually change the data. To enable tracking, call `tracked()` to get a tracked wrapper:

```php
$tracked = mutableSetOf([1, 2, 3])->tracked();

$tracked->add(4)->changed; // true - element was added
$tracked->add(4)->changed; // false - already present
$tracked->removeElement(99)->changed; // false - not in set
$tracked->reverse()->changed; // true - order changed
```

The tracked wrapper shares the same underlying store as the original collection, so changes through either reference are visible to both:

```php
$set = mutableSetOf([1, 2, 3]);
$tracked = $set->tracked();

$tracked->add(4);
$set->contains(4); // true - same underlying data
```

This is useful for conditional logic:

```php
$tracked = $cache->tracked();
if ($tracked->remove($expiredKey)->changed) {
    $logger->info("Evicted cache entry: $expiredKey");
}
```

::: tip `$changed` is only on mutation results
The `$changed` property is available on the **return value** of each mutation method, not on the tracked wrapper itself. This is by design, because it prevents a common mistake where `tracked()` is called after a mutation and `$changed` is read from the wrong object:

```php
// Correct - $changed is on the mutation result
$tracked->add(4)->changed; // true

// Also correct - capture the result
$result = $tracked->add(4);
$result->changed; // true

// Wrong - $tracked itself does not expose $changed
$tracked->add(4);
$tracked->changed; // PHPStan error - $changed is not on MutableTrackedCollection
```

Each mutation method returns a fresh `$changed` value reflecting whether **that specific call** modified the data, not cumulative changes.
:::

## Immutable collections

Immutable collections never modify themselves. Every mutating method returns a **new** instance:

```php
$original = listOf([1, 2, 3]);
$modified = $original->add(4)->removeElement(1);

// $original is still [1, 2, 3]
// $modified is [2, 3, 4]
```

### The `#[NoDiscard]` attribute

All mutating methods on immutable collections are marked with `#[NoDiscard]`. This prevents a common mistake (calling a method and forgetting to use the return value):

```php
$list = listOf([1, 2, 3]);

// Warning: this does nothing - return value is discarded
$list->add(4);

// Correct
$list = $list->add(4);
```

Static analyzers and IDEs that support `#[NoDiscard]` will flag the first form as a likely bug.

## Choosing between mutable and immutable

**Use immutable** when:
- Building a value that should not change after construction
- Returning collections from public APIs
- Passing collections to code that should not modify them
- Working with shared state where predictability matters

**Use mutable** when:
- Building up a collection incrementally (loops, batch processing)
- Performance matters and copying is wasteful
- Tracking whether an operation had an effect (via `tracked()`)
- The collection is local to a method and not shared

## Transformation methods always return immutable

All transformation methods (`filter`, `map`, `sorted`, `reversed`, `distinct`, etc.) **always return immutable collections**, regardless of whether the source is mutable or immutable:

```php
$mutableList = mutableListOf([3, 1, 2]);
$filtered = $mutableList->filter(fn($v) => $v > 1); // ImmutableList<int>
$sorted = $mutableList->sorted(); // ImmutableList<int>

$mutableMap = mutableMapOf(['a' => 1, 'b' => 2]);
$filtered = $mutableMap->filter(fn($v, $k) => $v > 1); // ImmutableMap<string, int>
```

Transformation methods are marked `#[NoDiscard]`, so you must capture the return value, which makes it clear that you're working with a new, immutable result rather than a modified original.

Transformation methods create derived data. Making the result immutable by default prevents accidental mutation of derived collections and keeps the API predictable, so you always know what you're getting.

## Conversion methods return immutable

Conversion methods like `toList()`, `toSet()`, and `toMap()` also return immutable types:

```php
$mutableSet = mutableSetOf([1, 2, 3]);
$mutableSet->toList(); // ImmutableList<int>
$mutableSet->toMap(fn($v) => "k$v"); // ImmutableMap<string, int>

$mutableList = mutableListOf([1, 2, 3]);
$mutableList->toSet(); // ImmutableSet<int>
$mutableList->toIndexedMap(); // ImmutableMap<int, int>
```

If you need a mutable result, chain with `toMutable()`, it has zero memory overhead, because it utilizes copy-on-write.

```php
$mutableSet->toList()->toMutable(); // MutableList<int>
```

## Map views

Map views (`$keys`, `$values`, `$entries`) are read-only regardless of whether the map is mutable or immutable. They expose query and iteration methods but no mutating operations:

```php
$map = mutableMapOf(['a' => 1, 'b' => 2]);
$map->keys; // read-only Set<string>
$map->values; // read-only Collection<int>
$map->entries; // read-only Set<MapEntry<string, int>>
```

Transformation and conversion methods on views also return immutable collections:

```php
$map->keys->filter(fn($k) => strlen($k) > 1); // ImmutableSet<string>
$map->values->toList(); // ImmutableList<int>
```

## Converting between variants

```php
$mutable = mutableListOf([1, 2, 3]);
$immutable = $mutable->toImmutable(); // new ImmutableList

$immutable = listOf([1, 2, 3]);
$mutable = $immutable->toMutable(); // new MutableList (always a copy)
```

`toMutable()` always creates a new copy, even if the source is already mutable. `toImmutable()` may return the same instance if the source is already immutable.

Methods `toMutable()`, `toImmutable()`, and factory functions like `listOf($mutableList)` all share data via copy-on-write, so memory is only duplicated when either side is modified.

## Type safety and widening

Mutable and immutable collections handle generic types differently when adding elements.

### Mutable: strict typing

Mutable collections enforce strict typing. You can only add elements that match the declared type. PHPStan will warn if you try to add incompatible types:

```php
$map = mutableMapOf(['a' => 1]); // MutableMap<string, int>
$map['b'] = 2; // OK - int matches int
$map['c'] = 'text'; // PHPStan error: string is not int

$list = mutableListOf([1, 2, 3]); // MutableList<int>
$list[] = 'text'; // PHPStan error: string is not int
```

This catches bugs at analysis time. You declared a type, and the mutable collection holds you to it.

### Immutable: type widening

Immutable collections allow type widening. Since mutating operations return a **new** instance, that instance can have a wider type:

```php
$map = mapOf(['a' => 1]); // ImmutableMap<string, int>

$new = $map->put('b', 'text');
// $new is ImmutableMap<string, int|string>
// $map is still ImmutableMap<string, int>

$list = listOf([1, 2, 3]); // ImmutableList<int>
$extended = $list->add('text');
// $extended is ImmutableList<int|string>
```

This is useful for building up data structures incrementally:

```php
$base = mapOf(['id' => 1]);
$withName = $base->put('name', 'Rodney');
$complete = $withName->put('active', true);
// $complete is ImmutableMap<string, int|string|bool>
```

### Why the difference?

With a mutable collection, you declared the type upfront. Adding incompatible data corrupts your declared contract, which is likely a bug. With an immutable one, you're creating a new value, and the new value can have whatever type makes sense for its contents.

### Compared to native arrays

PHP arrays don't enforce types on assignment, so PHPStan silently widens the type:

```php
/** @var array<string, int> $array */
$array = ['a' => 1];

$array['b'] = 'text'; // No warning! Type becomes array<string, int|string>
```

Mutable collections catch this class of bugs that native arrays miss.

::: warning Type-specific maps also check keys at runtime
Maps created via `mutableStringMapOf()` / `mutableIntMapOf()` enforce key types at runtime, so inserting a wrong key type throws `InvalidKeyTypeException`. Their immutable counterparts from `stringMapOf()` / `intMapOf()` widen instead, so `put()` with a wrong key type returns a general map that accepts any key. The generic `mapOf()` accepts any key type and has no runtime checks. This only applies when explicitly choosing a type-specific factory function.
:::
