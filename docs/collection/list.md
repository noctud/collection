# List

`ListInterface<E>` is an ordered, indexed collection of elements. It supports duplicate values and provides positional access via integer indices.

## Creating

```php
use function Noctud\Collection\listOf;
use function Noctud\Collection\mutableListOf;

$immutable = listOf([1, 2, 3]);
$mutable = mutableListOf([1, 2, 3]);
$empty = listOf();
$lazy = listOf(fn() => loadElementsFromDatabase());
```

## Accessing elements

Lists implement `ArrayAccess`, so you can use familiar array syntax. Array access is strict and throws on invalid indices:

```php
$list = listOf(['a', 'b', 'c']);

$list[0]; // 'a' - throws IndexOutOfBoundsException if out of bounds
$list[0] ?? null; // 'a' - null if out of bounds (via ?? operator)
isset($list[0]); // true
isset($list[5]); // false

$list->get(0); // 'a' - throws if out of bounds
$list->getOrNull(0); // 'a' - returns null
$list->getOrDefault(5, 'x'); // 'x'
$list->getOrCompute(5, fn() => 'computed'); // 'computed'

$list->first(); // 'a' - throws if empty
$list->firstOrNull(); // 'a' - returns null if empty
$list->last(); // 'c'
$list->lastOrNull(); // 'c'
$list->find(fn($v) => $v === 'b'); // 'b' - first match or null
$list->findLast(fn($v) => $v >= 'b'); // 'c' - last match or null
$list->expect(fn($v) => $v === 'b'); // 'b' - first match or throws

$list->indexOf('b'); // 1
$list->lastIndexOf('b'); // 1
$list->indexOfFirst(fn($v) => $v === 'b'); // 1
$list->indexOfLast(fn($v) => $v === 'b'); // 1
```

## Slicing

```php
$list = listOf([10, 20, 30, 40, 50]);

$list->slice(1, 3); // [20, 30]
$list->takeFirst(2); // [10, 20]
$list->takeLast(2); // [40, 50]
$list->dropFirst(3); // [40, 50]
$list->dropLast(3); // [10, 20]
$list->takeWhile(fn($n) => $n < 40); // [10, 20, 30]
$list->dropWhile(fn($n) => $n < 40); // [40, 50]
$list->takeLastWhile(fn($n) => $n > 20); // [30, 40, 50]
$list->dropLastWhile(fn($n) => $n > 20); // [10, 20]
```

## Transforming

All transformation methods return **immutable collections**. The original is never modified, and the result is always immutable regardless of whether the source is mutable or immutable.

```php
$list = mutableListOf([1, 2, 3, 4]);

$list->filter(fn($n) => $n % 2 === 0); // ImmutableList [2, 4]
$list->filterInstanceOf(Dog::class); // ImmutableList<Dog> - narrows the generic type
$list->map(fn($n) => $n * 10); // ImmutableList [10, 20, 30, 40]
$list->mapNotNull(fn($n) => $n > 2 ? $n * 10 : null); // ImmutableList [30, 40]
$list->flatMap(fn($n) => [$n, $n * 2]); // ImmutableList [1, 2, 2, 4, 3, 6, 4, 8]
$list->distinct(); // ImmutableList [1, 2, 3, 4]
$list->chunked(2); // ImmutableList [[1, 2], [3, 4]]
$list->chunked(2)->map(fn($c) => $c->sum()); // ImmutableList [3, 7]
$list->windowed(3); // [[1, 2, 3], [2, 3, 4]]
$list->windowed(3, 2, true); // [[1, 2, 3], [3, 4]]
$list->zip(['a', 'b']); // ImmutableList [[1, 'a'], [2, 'b']]
$list->zip(['a', 'b'])->unzip(); // [ImmutableList [1, 2], ImmutableList ['a', 'b']]
$list->zipWithNext(); // ImmutableList [[1, 2], [2, 3], [3, 4]]
$list->partition(fn($n) => $n > 2); // [ImmutableList [3, 4], ImmutableList [1, 2]]
$list->groupBy(fn($n) => $n % 2 === 0 ? 'even' : 'odd');
// ImmutableMap { 'odd' => [1, 3], 'even' => [2, 4] }
$list->groupBy(fn($n) => $n % 2 === 0 ? 'even' : 'odd', fn($n) => $n * 10);
// ImmutableMap { 'odd' => [10, 30], 'even' => [20, 40] }
$list->countBy(fn($n) => $n % 2 === 0 ? 'even' : 'odd');
// ImmutableMap { 'odd' => 2, 'even' => 2 }
```

## Aggregation

```php
$list = listOf([3, 1, 4, 1, 5]);

$list->sum(); // 14
$list->avg(); // 2.8
$list->min(); // 1
$list->max(); // 5
$list->count(); // 5
$list->reduce(fn($a, $b) => $a + $b); // 14
$list->joinToString(', '); // '3, 1, 4, 1, 5'
```

Selector variants extract values from objects:

```php
$users->min(fn($u) => $u->age); // User with lowest age
$users->minOf(fn($u) => $u->age); // lowest age value (int)
$users->sum(fn($u) => $u->score); // sum of all scores
$users->joinToString(', ', transform: fn($u) => $u->name); // 'Rodney, Sheppard, Teyla'
```

## Quantifiers

```php
$list = listOf([2, 4, 6]);

$list->all(fn($n) => $n % 2 === 0); // true
$list->any(fn($n) => $n > 5); // true
$list->none(fn($n) => $n < 0); // true
$list->contains(4); // true
$list->containsAll([2, 6]); // true
```

## Mutating (MutableList)

Mutable methods modify the list in-place and return `$this` for chaining:

```php
$list = mutableListOf([1, 2, 3]);

$list->addFirst(0); // [0, 1, 2, 3]
$list->add(4); // [0, 1, 2, 3, 4]
$list->addAll([5, 6]); // [0, 1, 2, 3, 4, 5, 6]
$list->set(0, 99); // [99, 1, 2, 3, 4, 5, 6]
$list->removeElement(3); // [99, 1, 2, 4, 5, 6] - first occurrence
$list->removeAt(0); // [1, 2, 4, 5, 6]
$list->removeEvery(4); // [1, 2, 5, 6] - all occurrences
$list->removeIf(fn($n) => $n > 5); // [1, 2, 5]
$list->clear(); // []
```

Array access syntax also works for mutation:

```php
$list = mutableListOf(['a', 'b', 'c']);

$list[0] = 'x'; // ['x', 'b', 'c'] - same as set()
$list[] = 'd'; // ['x', 'b', 'c', 'd'] - same as add()
unset($list[1]); // ['x', 'c', 'd'] - same as removeAt()
```

Track changes with `tracked()`:

```php
$tracked = mutableListOf([1, 2, 3])->tracked();
$tracked->removeElement(99)->changed; // false - element not found
$tracked->removeElement(2)->changed; // true
```

## Mutating (ImmutableList)

Immutable methods return a **new** list. The original is untouched:

```php
$list = listOf([1, 2, 3]);

$new = $list->add(4); // $new = [1, 2, 3, 4], $list = [1, 2, 3]
$new = $list->addFirst(0); // $new = [0, 1, 2, 3]
$new = $list->set(0, 99); // $new = [99, 2, 3]
$new = $list->removeAt(1); // $new = [1, 3]
```

All mutating methods on `ImmutableList` are marked `#[NoDiscard]`, so calling them without using the return value triggers a warning.

::: warning Array access mutation
Array access mutation (`$list[0] = 'x'`, `$list[] = 'y'`, `unset($list[0])`) throws `UnsupportedOperationException` on immutable lists. Use the method equivalents which return new instances.
:::

## Conversion

Conversion methods return immutable collections:

```php
$list = mutableListOf([1, 2, 3]);

$list->toArray(); // [1, 2, 3]
$list->toSet(); // ImmutableSet<int>
$list->toMutable(); // MutableList<int> (always a new copy)
$list->toImmutable(); // ImmutableList<int>
$list->toIndexedMap(); // ImmutableMap<int, int> { 0 => 1, 1 => 2, 2 => 3 }
$list->toMap(
    fn($v) => "key_$v",
    fn($v) => $v * 10,
); // ImmutableMap { 'key_1' => 10, 'key_2' => 20, 'key_3' => 30 }

json_encode($list); // [1,2,3]
```
