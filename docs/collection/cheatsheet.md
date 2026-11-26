# Cheatsheet

A scannable, example-driven tour of the API. For exact signatures and edge cases, see the [API Reference](./api/collection).

## How to read this

- Examples use the [factory functions](./api/functions): `listOf`, `setOf`, `mapOf` (immutable) and their `mutable*` variants.
- A trailing comment shows the **result's contents**: `[...]` is a list, `{...}` is a set or map.
- Unless noted, **transformation and ordering methods return a new immutable collection**. The source is never changed, even when it is mutable.
- Predicates/selectors on **List/Set** receive `(element, int $index)`; on **Map**, `(value, key)`.
- Some examples reuse `$isFruit = fn($e) => in_array($e, ['apple', 'orange', 'lemon', 'grape'], true);`.

## Creating collections

```php
listOf(['apple', 'orange', 'lemon']); // ImmutableList
mutableListOf([1, 2, 3]); // MutableList
setOf(['apple', 'apple', 'orange']); // ImmutableSet {apple, orange}: duplicates dropped
mutableSetOf(['a','b']); // MutableSet
mapOf(['apple' => 3, 'banana' => 5]); // ImmutableMap (fruit → count)
mutableMapOf(['x' => 10]); // MutableMap

mapOfPairs([['1','a'], [$user,'b']]); // preserves exact key types
stringMapOf(['name' => 'Amy']); // optimized for string keys
intMapOf([1 => 'a', 2 => 'b']); // optimized for int keys

listOf(fn() => loadRows()); // lazy init: loaded on first access
```

## List & Set

Inherited from `Collection<E>`, so available on **both lists and sets**.

### Element access

```php
listOf(['apple', 'orange', 'grape'])->first(); // apple
listOf([])->firstOrNull(); // null
listOf(['apple', 'orange', 'grape'])->last(); // grape
listOf(['apple'])->single(); // apple (throws unless exactly one element)
listOf(['apple', 'orange', 'grape'])->find($isFruit); // apple (first match)
listOf(['apple', 'orange', 'grape'])->findLast($isFruit); // grape (last match)
listOf(['carrot', 'broccoli'])->expect($isFruit); // throws NoSuchElementException
listOf(['apple', 'orange', 'grape'])->random(); // e.g. orange
```

### Querying

```php
listOf(['apple', 'orange', 'lemon'])->contains('orange'); // true
listOf(['apple', 'orange', 'lemon'])->containsAll(['apple', 'lemon']); // true
listOf(['apple', 'orange'])->all($isFruit); // true
listOf(['apple', 'carrot'])->any($isFruit); // true
listOf(['carrot', 'broccoli'])->none($isFruit); // true
listOf(['apple', 'orange', 'lemon'])->count(); // 3
listOf(['apple', 'carrot', 'orange'])->countWhere($isFruit); // 2
listOf([])->isEmpty(); // true
```

### Aggregation

Arithmetic, so these use plain numbers. `fold` starts from an initial value, `reduce` from the first element.

```php
listOf([1, 2, 3])->fold(10, fn($acc, $n) => $acc + $n); // 16
listOf([1, 2, 3])->reduce(fn($acc, $n) => $acc + $n); // 6
listOf([])->reduceOrNull(fn($a, $b) => $a + $b); // null
listOf([10, 20, 30])->sum(); // 60
listOf([10, 20, 30])->avg(); // 20.0
listOf([3, 1, 2])->min(); // 1
listOf([3, 1, 2])->max(); // 3
```

`min`/`max` return the **element**; `minOf`/`maxOf` return the selector **value**.

```php
$people = listOf([
    ['name' => 'Amy', 'age' => 30],
    ['name' => 'Bob', 'age' => 25],
]);
$people->min(fn($p) => $p['age']); // ['name' => 'Bob', 'age' => 25]
$people->minOf(fn($p) => $p['age']); // 25
```

```php
listOf(['apple', 'orange', 'lemon'])->joinToString(', '); // "apple, orange, lemon"
listOf(['apple', 'orange'])->joinToString(', ', '[', ']'); // "[apple, orange]"
listOf(['apple', 'orange', 'apple'])->countBy(fn($e) => $e); // {apple: 2, orange: 1}
```

### Transformation

```php
listOf(['apple', 'carrot', 'orange', 'broccoli'])->filter($isFruit); // [apple, orange]
listOf(['apple', null, 'orange'])->filterNotNull(); // [apple, orange]
listOf($animals)->filterInstanceOf(Dog::class); // ImmutableList<Dog>
listOf(['caterpillar', 'caterpillar'])->map(fn($e) => 'butterfly'); // [butterfly, butterfly]
listOf([1, 2, 3, 4])->mapNotNull(fn($n) => $n % 2 ? null : $n); // [2, 4]
listOf([['apple', 'orange'], ['lemon']])->flatMap(fn($g) => $g); // [apple, orange, lemon]
listOf([['apple', 'orange'], ['lemon']])->flatten(); // [apple, orange, lemon]
```

```php
listOf(['apple', 'orange', 'lemon', 'grape'])->takeFirst(2); // [apple, orange]
listOf(['apple', 'orange', 'lemon', 'grape'])->takeLast(2); // [lemon, grape]
listOf(['apple', 'orange', 'lemon', 'grape'])->dropFirst(2); // [lemon, grape]
listOf(['apple', 'orange', 'lemon', 'grape'])->dropLast(2); // [apple, orange]

listOf(['apple', 'orange', 'carrot', 'lemon'])->takeWhile($isFruit); // [apple, orange]
listOf(['apple', 'orange', 'carrot', 'lemon'])->dropWhile($isFruit); // [carrot, lemon]

listOf(['apple', 'orange', 'apple', 'lemon'])->distinct(); // [apple, orange, lemon]
$people->distinctBy(fn($p) => $p['age']); // first of each distinct age
```

`chunked` splits into fixed-size pieces; `windowed` slides a window across.

```php
listOf([1, 2, 3, 4, 5])->chunked(2); // [[1,2], [3,4], [5]]
listOf([1, 2, 3, 4])->windowed(2); // [[1,2], [2,3], [3,4]]
listOf([1, 2, 3, 4, 5])->windowed(3, step: 2); // [[1,2,3], [3,4,5]]
```

`zip` pairs two lists (stops at the shorter); `zipWithNext` pairs adjacent elements; `unzip` is the inverse of `zip`.

```php
listOf(['Amy', 'Bob'])->zip(['hat', 'cap']); // [[Amy, hat], [Bob, cap]]
listOf(['apple', 'orange', 'lemon'])->zipWithNext(); // [[apple, orange], [orange, lemon]]
listOf([['Amy', 'hat'], ['Bob', 'cap']])->unzip(); // [[Amy, Bob], [hat, cap]]
```

`partition` splits into `[matching, rest]`; `groupBy` returns a `Map` of lists.

```php
listOf(['apple', 'carrot', 'orange', 'broccoli'])->partition($isFruit); // [[apple, orange], [carrot, broccoli]]
listOf([1, 2, 3, 4])->groupBy(fn($n) => $n % 2 ? 'odd' : 'even');
// {odd: [1, 3], even: [2, 4]}
```

Set operations always return an `ImmutableSet`, typed as `Set`.

```php
listOf(['apple', 'orange', 'lemon'])->intersect(['orange', 'lemon', 'grape']); // {orange, lemon}
listOf(['apple', 'orange'])->union(['orange', 'grape']); // {apple, orange, grape}
listOf(['apple', 'orange', 'lemon'])->subtract(['orange', 'lemon']); // {apple}
```

### Ordering

```php
listOf([3, 1, 2])->sorted(); // [1, 2, 3]
listOf([3, 1, 2])->sortedDesc(); // [3, 2, 1]
$people->sortedBy(fn($p) => $p['age']); // youngest → oldest
$people->sortedByDesc(fn($p) => $p['age']); // oldest → youngest
listOf([3, 1, 2])->sortedWith(fn($a, $b) => $a <=> $b); // [1, 2, 3]
listOf(['apple', 'orange', 'lemon'])->reversed(); // [lemon, orange, apple]
listOf(['apple', 'orange', 'lemon'])->shuffled(); // random order
```

### Iteration & conversion

```php
listOf(['apple', 'orange'])->forEach(fn($e) => send($e)); // returns nothing
listOf(['apple', 'orange'])->onEach(fn($e) => send($e))->toSet(); // same walk, keeps the chain

listOf(['apple', 'orange', 'apple'])->toSet(); // {apple, orange}
setOf(['apple', 'orange'])->toList(); // [apple, orange]
listOf(['apple', 'orange'])->toArray(); // ['apple', 'orange'] (native PHP array)
listOf(['Amy','Bob'])->toMap(fn($n) => $n[0]); // {A: 'Amy', B: 'Bob'}
listOf([1, 2])->toMutable(); // MutableList (always a fresh copy)
mutableListOf([1, 2])->toImmutable(); // ImmutableList
```

## List only

Positional access by integer index (`Set` has none). `slice(from, to)` is from-inclusive, to-exclusive.

```php
listOf(['apple', 'orange', 'lemon'])->get(1); // orange (alias: $list[1])
listOf(['apple', 'orange', 'lemon'])->getOrNull(9); // null (same as $list[9] ?? null)
listOf(['apple', 'orange'])->getOrDefault(9, 'none'); // none
listOf(['apple', 'orange', 'lemon'])->indexOf('orange'); // 1 (-1 if absent)
listOf([10, 20, 30])->indexOfFirst(fn($n) => $n > 15); // 1
listOf(['apple', 'orange', 'lemon', 'grape'])->slice(1, 3); // [orange, lemon]
```

Mutation adds `set(index, value)`, `removeAt(index)`, `removeEvery(value)`.

## Set only

Sets hold **unique** elements (duplicates dropped on insert) and add no methods of their own. One twist: `addFirst()` uses **move-to-front** semantics.

```php
setOf(['apple', 'apple', 'orange']); // {apple, orange}
mutableSetOf(['apple', 'orange', 'lemon'])->addFirst('lemon'); // {lemon, apple, orange}
```

## Map

A key-value collection with **strict** key handling. Keys are never silently cast. Predicates receive `(value, key)`.

### Keys, values & entries

A map exposes three **live, read-only views** as properties: `$keys` is a `Set`, `$values` is a `Collection`, and `$entries` is a `Set` of `MapEntry`. Keys are unique (so a `Set`); values may repeat (so a `Collection`).

```php
$colors = mapOf(['apple' => 'red', 'banana' => 'yellow', 'grape' => 'purple']);

$colors->keys; // Set<string> {apple, banana, grape}
$colors->values; // Collection<string> [red, yellow, purple]
$colors->entries; // Set<MapEntry> {apple→red, banana→yellow, grape→purple}
```

Split apart, the same map decomposes like this:

| `$colors` entry | `->keys` (Set) | `->values` (Collection) |
| --- | --- | --- |
| apple → red | apple | red |
| banana → yellow | banana | yellow |
| grape → purple | grape | purple |

### Access & querying

```php
mapOf(['apple' => 3])->get('apple'); // 3 (alias: $map['apple'])
mapOf(['apple' => 3])->getOrNull('banana'); // null
mapOf(['apple' => 3])->getOrDefault('banana', 0); // 0
mutableMapOf(['apple' => 3])->getOrPut('banana', fn() => 5); // 5 (stored)

mapOf(['apple' => 3])->containsKey('apple'); // true
mapOf(['apple' => 3])->containsValue(3); // true
mapOf(['apple' => 3, 'banana' => 5])->count(); // 2
mapOf(['apple' => 3, 'banana' => 5])->all(fn($v, $k) => $v > 0); // true
```

### Transformation

```php
mapOf(['apple' => 3, 'banana' => 5])->filter(fn($v, $k) => $v > 4); // {banana: 5}
mapOf(['apple' => 3, 'banana' => 5])->filterValues(fn($v) => $v > 4); // {banana: 5}
mapOf(['apple' => 3])->mapValues(fn($v, $k) => $v * 10); // {apple: 30}
mapOf(['apple' => 3])->mapKeys(fn($v, $k) => "fruit:$k"); // {fruit:apple: 3}
mapOf(['apple' => 3, 'banana' => 5])->flip(); // {3: apple, 5: banana}

mapOf(['apple' => 3, 'banana' => 5])->map(fn($v, $k) => "$k=$v");
// ['apple=3', 'banana=5'] (a list)
```

### Ordering & conversion

```php
mapOf(['banana' => 5, 'apple' => 3])->sortedByValue(); // {apple: 3, banana: 5}
mapOf(['banana' => 5, 'apple' => 3])->reversed(); // {apple: 3, banana: 5}
mapOf(['apple' => 3])->toArray(); // ['apple' => 3]
mapOf(['apple' => 3])->toPairs(); // [['apple', 3]]
```

### Mutation

```php
mutableMapOf(['apple' => 3])->put('banana', 5); // adds/replaces
mutableMapOf(['apple' => 3])->putIfAbsent('apple', 9); // no-op: key exists
mutableMapOf(['apple' => 3, 'banana' => 5])->remove('apple'); // {banana: 5}
```

Also: `putFirst`, `putAll`, `putAllPairs`, `removeIf`, `removeIfKey`, `removeIfValue`, `removeNullValues`, `clear`, plus the in-place `sortByKey` / `sortByValue` / ... family.

## Sequence

A **lazy** pipeline. Intermediate operations return a new `Sequence` and run nothing; a terminal operation pulls elements one at a time and stops as soon as it can.

```php
sequenceOf(['apple', 'carrot', 'orange']); // from an array, so replayable
sequenceOf(fn() => loadRows()); // closure runs on every pass
mutableListOf(['apple', 'orange'])->asSequence(); // no copy, sees later changes
generateSequence(1, fn($n) => $n * 2); // 1, 2, 4, 8, ... (infinite)
```

```php
sequenceOf(['carrot', 'apple', 'orange', 'broccoli'])
    ->filter($isFruit) // nothing runs yet
    ->map(fn($e) => "$e!") // still nothing
    ->first(); // apple! (pulled carrot and apple, never orange or broccoli)
```

On an infinite sequence, only operations that stop early can return.

```php
$naturals = generateSequence(1, fn($n) => $n + 1); // 1, 2, 3, ...

$naturals->takeFirst(3)->toList(); // [1, 2, 3]
$naturals->dropFirst(2)->first(); // 3
$naturals->find(fn($n) => $n * $n > 50); // 8
$naturals->takeWhile(fn($n) => $n < 5)->sum(); // 10
$naturals->chunked(2)->first(); // [1, 2]
$naturals->joinToString(limit: 3); // "1, 2, 3, ..."
$naturals->count(); // never returns: drains forever
```

Every terminal operation is a new pass. A `Generator` can't replay:

```php
$once = sequenceOf($generator);
$once->toList(); // [...]
$once->toList(); // throws NonReplayableSourceException
```

No `sorted*`, `reversed`, `shuffled`, `random`, `takeLast*`, `dropLast*` or set operations, because most of them need everything up front. Call `toList()` first: `$seq->toList()->sorted()`.

## Mutable vs. immutable

Same method names, different behavior. On a **mutable** collection they change it in place and return it (handy for chaining); on an **immutable** collection they return a **new** collection and leave the original untouched.

```php
$m = mutableListOf(['apple', 'orange']);
$m->add('lemon'); // $m is now [apple, orange, lemon]

$i = listOf(['apple', 'orange']);
$new = $i->add('lemon'); // $i stays [apple, orange]; $new is [apple, orange, lemon]
```

Shared mutators (List & Set): `add`, `addFirst`, `addAll`, `removeElement`, `removeFirst`, `removeLast`, `removeIf`, `removeAll`, `retainAll`. Mutable collections also have `clear` and the in-place ordering `sort` / `sortDesc` / `sortBy` / `sortByDesc` / `sortWith` / `reverse` / `shuffle`.

### Tracking changes

`tracked()` wraps a mutable collection so each mutation reports whether it actually changed anything:

```php
$t = mutableSetOf(['apple', 'orange'])->tracked();
$t->add('lemon')->changed; // true
$t->add('lemon')->changed; // false: already present
```
