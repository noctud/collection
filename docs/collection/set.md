# Set

`Set<E>` is an ordered collection of **unique** elements. Duplicate values are silently ignored on insertion. Sets do not support indexed access by design. Use `contains()` to check membership.

## Creating

```php
use function Noctud\Collection\setOf;
use function Noctud\Collection\mutableSetOf;

$immutable = setOf(['a', 'b', 'c']);
$mutable = mutableSetOf(['a', 'b', 'c']);
$empty = setOf();
$deduped = setOf([1, 2, 2, 3]); // {1, 2, 3}
$lazy = setOf(fn() => loadUniqueElements());
```

## Querying

```php
$set = setOf([1, 2, 3, 4, 5]);

$set->contains(3); // true
$set->containsAll([1, 3]); // true
$set->count(); // 5
$set->isEmpty(); // false
$set->isNotEmpty(); // true
```

## Element access

Sets support the same access methods as other collections:

```php
$set = setOf([10, 20, 30]);

$set->first(); // 10
$set->last(); // 30
$set->find(fn($n) => $n > 15); // 20 - first match or null
$set->findLast(fn($n) => $n > 15); // 30 - last match or null
$set->expect(fn($n) => $n > 15); // 20 - first match or throws
$set->random(); // random element
```

## Transforming

All transformation methods return **immutable collections**. The original is never modified, and the result is always immutable regardless of whether the source is mutable or immutable.

```php
$set = mutableSetOf([1, 2, 3, 4, 5]);

$set->filter(fn($n) => $n > 3); // ImmutableSet {4, 5}
$set->map(fn($n) => $n * 2); // ImmutableSet<int>
$set->mapNotNull(fn($n) => $n > 3 ? $n * 2 : null); // ImmutableSet {8, 10}
$set->flatMap(fn($n) => [$n, -$n]); // ImmutableSet<int>
$set->takeFirst(3); // ImmutableSet {1, 2, 3}
$set->takeLast(2); // ImmutableSet {4, 5}
$set->dropFirst(2); // ImmutableSet {3, 4, 5}
$set->dropLast(2); // ImmutableSet {1, 2, 3}
$set->partition(fn($n) => $n % 2 === 0); // [ImmutableSet {2, 4}, ImmutableSet {1, 3, 5}]
$set->groupBy(fn($n) => $n % 2 === 0 ? 'even' : 'odd'); // ImmutableMap
```

## Aggregation

```php
$set = setOf([3, 1, 4, 5]);

$set->sum(); // 13
$set->avg(); // 3.25
$set->min(); // 1
$set->max(); // 5
$set->reduce(fn($a, $b) => $a + $b); // 13
$set->joinToString(', '); // '3, 1, 4, 5'
$set->joinToString(', ', transform: fn($n) => "#$n"); // '#3, #1, #4, #5'
```

## Quantifiers

```php
$set = setOf(['apple', 'avocado', 'banana']);

$set->all(fn($s) => strlen($s) > 3); // true
$set->any(fn($s) => $s[0] === 'b'); // true
$set->none(fn($s) => $s === 'cherry'); // true
```

## Mutating (MutableSet)

Mutable methods modify the set in-place. Adding a duplicate element is a no-op:

```php
$set = mutableSetOf(['a', 'b']);

$set->add('c'); // {'a', 'b', 'c'}
$set->add('a'); // no-op - already present
$set->addAll(['d', 'e']); // {'a', 'b', 'c', 'd', 'e'}
$set->removeElement('b'); // {'a', 'c', 'd', 'e'}
$set->removeIf(fn($v) => $v > 'c'); // {'a', 'c'}
$set->removeAll(['a']); // {'c'}
$set->clear(); // {}
```

Duplicates are found by hash, the same way `contains()` finds elements. Two `Hashable` objects with the same `identity()` count as one element, so the set keeps the instance it already holds, even when the new one carries different data:

```php
// User implements Hashable, identity() returns its id
$set = mutableSetOf([new User(1, 'old@example.com')]);
$set->add(new User(1, 'new@example.com'));
$set->first()->email; // 'old@example.com'
```

To store the newer object, remove the old one and add it again. `addFirst()` follows the same rule and moves the stored instance to the front.

### addFirst: move to front

`addFirst()` guarantees the element is first after the call. If the element already exists in the set, it is **moved to the front**. If it is already first, the call is a no-op:

```php
$set = mutableSetOf([1, 2, 3]);

$set->addFirst(3); // {3, 1, 2} - moved to front
$set->addFirst(3); // {3, 1, 2} - no-op, already first
$set->addFirst(0); // {0, 3, 1, 2} - new element prepended
```

This follows the same semantics as Java 21's `SequencedSet.addFirst()`. The post-condition is that the element is always at the first position, while uniqueness is preserved.

Track changes with `tracked()`:

```php
$tracked = mutableSetOf([1, 2, 3])->tracked();
$tracked->removeElement(99)->changed; // false - not in set
$tracked->removeElement(2)->changed; // true
```

## Mutating (ImmutableSet)

Immutable methods return a new set:

```php
$set = setOf([1, 2, 3]);

$new = $set->add(4); // {1, 2, 3, 4}
$new = $set->addFirst(3); // {3, 1, 2} - moved to front
$new = $set->removeElement(2); // {1, 3}
$new = $set->addAll([4, 5]); // {1, 2, 3, 4, 5}
// $set is unchanged
```

## Sorting

```php
$set = setOf([3, 1, 2]);

$set->sorted(); // {1, 2, 3}
$set->sortedDesc(); // {3, 2, 1}
$set->sortedBy(fn($n) => -$n); // {3, 2, 1}
$set->sortedWith(fn($a, $b) => $a <=> $b); // {1, 2, 3}
```

Mutable sets also support in-place sorting:

```php
$set = mutableSetOf([3, 1, 2]);
$set->sort(); // modifies in place
```

## Conversion

Conversion methods return immutable collections:

```php
$set = mutableSetOf([1, 2, 3]);

$set->toArray(); // [1, 2, 3]
$set->toList(); // ImmutableList<int>
$set->toMutable(); // MutableSet<int> (always a new copy)
$set->toImmutable(); // ImmutableSet<int>
$set->toMap(fn($v) => "key_$v"); // ImmutableMap { 'key_1' => 1, ... }

json_encode($set); // [1,2,3]
```
