---
pageClass: api-reference
---

# Collection API

`Collection<E>` is the base interface for ordered element collections (List and Set). It extends `IteratorAggregate<int, E>` and `Countable`, and provides element access, querying, aggregation, transformation, and ordering methods.

## Element Access

```php
first(): E
```
Returns the first element. Throws `NoSuchElementException` if empty.

```php
firstOrNull(): E|null
```
Returns the first element, or `null` if empty.

```php
last(): E
```
Returns the last element. Throws `NoSuchElementException` if empty.

```php
lastOrNull(): E|null
```
Returns the last element, or `null` if empty.

```php
single(): E
```
Returns the single element. Throws `NoSuchElementException` if empty or more than one element.

```php
singleOrNull(): E|null
```
Returns the single element. Returns `null` if empty or more than one element.

```php
find(Closure $predicate): E|null
```
Returns the first element matching the predicate `(E, int): bool`, or `null` if no element matches. Uses early termination.

```php
findLast(Closure $predicate): E|null
```
Returns the last element matching the predicate `(E, int): bool`, or `null` if no element matches.

```php
expect(Closure $predicate): E
```
Returns the first element matching the predicate `(E, int): bool`. Throws `NoSuchElementException` if no element matches. Uses early termination.

```php
expectLast(Closure $predicate): E
```
Returns the last element matching the predicate `(E, int): bool`. Throws `NoSuchElementException` if no element matches.

```php
random(): E
```
Returns a random element. Throws `NoSuchElementException` if empty.

```php
randomOrNull(): E|null
```
Returns a random element, or `null` if empty.

## Querying

```php
contains(mixed $element): bool
```
Whether the collection contains the value (strict comparison).

```php
containsAll(iterable $elements): bool
```
Whether the collection contains all provided values.

```php
all(Closure $predicate): bool
```
Returns `true` if all elements match the predicate. Predicate: `(E, int): bool`.

```php
any(Closure $predicate): bool
```
Returns `true` if any element matches. Predicate: `(E, int): bool`.

```php
none(Closure $predicate): bool
```
Returns `true` if no element matches. Predicate: `(E, int): bool`.

```php
count(): int
```
Returns the number of elements in the collection.

```php
countWhere(Closure $predicate): int
```
Returns the number of elements matching the predicate `(E, int): bool`.

```php
isEmpty(): bool
```
Whether the collection has no elements.

```php
isNotEmpty(): bool
```
Whether the collection has at least one element.

## Aggregation

```php
fold(mixed $initial, Closure $operation): R
```
Left fold. Accumulates a result starting from the initial value by applying `(R, E): R` to each element. Usage: `$list->fold(0, fn($acc, $v) => $acc + $v)`.

```php
reduce(Closure $operation): E
```
Reduce with a binary operation `(E, E): E`. Throws `UnsupportedOperationException` if empty.

```php
reduceOrNull(Closure $operation): E|null
```
Reduce, or `null` if empty.

```php
sum(?Closure $selector = null): int|float
```
Sum of all elements, or values returned by the selector `(E, int): int|float`.

```php
avg(?Closure $selector = null): float
```
Average. Optional selector `(E, int): int|float`. Throws if empty.

```php
avgOrNull(?Closure $selector = null): float|null
```
Average, or `null` if empty. Optional selector `(E, int): int|float`.

```php
min(?Closure $selector = null): E
```
Element with minimum value. With a selector `(E, int): mixed`, returns the element whose selector value is minimum. Throws if empty.

```php
minOrNull(?Closure $selector = null): E|null
```
Minimum element, or `null` if empty. Optional selector `(E, int): mixed`.

```php
max(?Closure $selector = null): E
```
Element with maximum value. Optional selector `(E, int): mixed`. Throws if empty.

```php
maxOrNull(?Closure $selector = null): E|null
```
Maximum element, or `null` if empty. Optional selector `(E, int): mixed`.

```php
minOf(Closure $selector): mixed
```
Returns the minimum value produced by the selector `(E, int): mixed`. Unlike `min()`, returns the **selector value** itself, not the element. Throws `NoSuchElementException` if empty.

```php
minOfOrNull(Closure $selector): mixed
```
Returns the minimum selector value, or `null` if empty.

```php
maxOf(Closure $selector): mixed
```
Returns the maximum value produced by the selector `(E, int): mixed`. Unlike `max()`, returns the **selector value** itself, not the element. Throws `NoSuchElementException` if empty.

```php
maxOfOrNull(Closure $selector): mixed
```
Returns the maximum selector value, or `null` if empty.

```php
joinToString(
    string $separator = ', ',
    string $prefix = '',
    string $postfix = '',
    int $limit = -1,
    string $truncated = '...',
    ?Closure $transform = null,
): string
```
Joins elements into a string. When `$transform` is provided, it is applied to each element `(E, int): string` before joining. Without a transform, elements are converted via `(string)` cast — scalars, `null`, and `Stringable` objects are supported. Throws `ConversionException` for non-stringable objects and arrays.

```php
countBy(Closure $keySelector): ImmutableMap<NK, int>
```
Groups elements by the key returned by the selector `(E, int): NK` and counts elements in each group. Returns an `ImmutableMap` where keys are group identifiers and values are counts.

## Transformation

All transformation methods are marked `#[NoDiscard]` and **always return immutable collections**, regardless of whether the source is mutable or immutable.

```php
filter(Closure $predicate): ImmutableList<E> | ImmutableSet<E>
```
Filter by predicate `(E, int): bool`. Returns `ImmutableList` for lists, `ImmutableSet` for sets.

```php
filterNotNull(): Collection<E>
```
Filter out `null` elements.

```php
filterInstanceOf(string $type): Collection<T>
```
Filter elements that are instances of the given class or interface. `$type` is a `class-string<T>`. Enables PHPStan to narrow the generic type — e.g. `Collection<Animal>` becomes `ImmutableList<Dog>`.

```php
map(Closure $transform): Collection<R>
```
Transform each element. Closure: `(E, int): R`.

```php
mapNotNull(Closure $transform): Collection<R>
```
Transform each element with `(E, int): R|null` and exclude `null` results. Combines `map` and `filterNotNull` in a single operation.

```php
flatMap(Closure $transform): Collection<R>
```
Transform and flatten. Closure: `(E, int): iterable<R>`.

```php
flatten(): Collection<mixed>
```
Flatten a collection of iterables.

```php
takeFirst(int $n = 1): Collection<E>
```
First N elements.

```php
takeLast(int $n = 1): Collection<E>
```
Last N elements.

```php
dropFirst(int $n = 1): Collection<E>
```
All elements except the first N.

```php
dropLast(int $n = 1): Collection<E>
```
All elements except the last N.

```php
takeWhile(Closure $predicate): Collection<E>
```
Take elements while predicate is true.

```php
dropWhile(Closure $predicate): Collection<E>
```
Drop elements while predicate is true, return the rest.

```php
takeLastWhile(Closure $predicate): Collection<E>
```
Take elements from the end while predicate `(E, int): bool` is true.

```php
dropLastWhile(Closure $predicate): Collection<E>
```
Drop elements from the end while predicate `(E, int): bool` is true, return the rest.

```php
distinct(): Collection<E>
```
Unique elements by identity.

```php
distinctBy(Closure $selector): Collection<E>
```
Unique elements by selector `(E, int): mixed` value.

```php
chunked(int $size): ListInterface<ListInterface<E>>
```
Split into chunks of the given size. The last chunk may be smaller.

```php
windowed(
    int $size,
    int $step = 1,
    bool $partialWindows = false,
): ListInterface<ListInterface<E>>
```
Returns a list of snapshots of a sliding window of the given size along this collection with the given step. When `$partialWindows` is `true`, includes smaller windows at the end.

```php
zip(iterable $other): ListInterface<array{E, U}>
```
Pair elements from two iterables at the same position. Result length equals the shorter input.

```php
zipWithNext(): ListInterface<array{E, E}>
```
Returns a list of pairs of each two adjacent elements. If the collection has fewer than two elements, returns an empty list.

```php
unzip(): array{ImmutableList<mixed>, ImmutableList<mixed>}
```
Splits a collection of pairs into two lists — one from the first component (`[0]`), one from the second (`[1]`). Inverse of `zip()`.

```php
partition(Closure $predicate): array{Collection<E>, Collection<E>}
```
Split into two collections — matching and non-matching.

```php
groupBy(Closure $keySelector, ?Closure $valueTransform = null): Map<K, Collection<E>>
```
Group elements by key selector `(E, int): K`. When a `$valueTransform` `(E, int): V` is provided, each element is transformed before being added to its group — the result is `Map<K, ImmutableList<V>>`.

```php
intersect(iterable $other): ImmutableSet<E>
```
Elements present in both this collection and the iterable. Returns a set (duplicates removed).

```php
union(iterable $other): ImmutableSet<E>
```
All elements from both this collection and the iterable. Returns a set (duplicates removed).

```php
subtract(iterable $other): ImmutableSet<E>
```
Elements present in this collection but not in the iterable. Returns a set (duplicates removed).

## Ordering

All ordering methods returning new collections are marked `#[NoDiscard]`.

```php
sorted(): Collection<E>
```
Natural ascending order.

```php
sortedDesc(): Collection<E>
```
Natural descending order.

```php
sortedBy(Closure $selector): Collection<E>
```
Sort by selector `(E): R`.

```php
sortedByDesc(Closure $selector): Collection<E>
```
Sort by selector, descending.

```php
sortedWith(Closure $comparator): Collection<E>
```
Sort by comparator `(E, E): int`.

```php
reversed(): Collection<E>
```
Reversed order.

```php
shuffled(): Collection<E>
```
Random order.

## Iteration

```php
forEach(Closure $action): Collection
```
Execute action `(E, int): void` for each element. Returns the collection for chaining.

## Conversion

```php
toMap(Closure $keySelector, ?Closure $valueTransform = null): ImmutableMap<K, V>
```
Convert to an immutable map using key selector `(E, int): K` and optional value transform `(E, int): V`.

```php
toList(): ImmutableList<E>
```
Convert to an immutable list preserving iteration order.

```php
toSet(): ImmutableSet<E>
```
Convert to an immutable set (duplicates removed).

```php
toArray(): array<int, E>
```
Convert to a PHP array.

```php
toMutable(): MutableCollection<E>
```
Creates and returns a new in-memory mutable copy of this collection. Even if this collection is already mutable, a new copy is created.

```php
toImmutable(): ImmutableCollection<E>
```
Returns an immutable collection. If this collection is already immutable, it may return itself.

## MutableCollection Methods

In addition to all `Collection` methods, `MutableCollection<E>` provides:

### Tracking

```php
tracked(): MutableTrackedCollection<E>
```
Returns a tracked wrapper that shares the same underlying store. Each mutation method on the tracked wrapper returns a result with a `$changed` property indicating whether that operation modified the data:

```php
$tracked = mutableSetOf([1, 2, 3])->tracked();
$tracked->add(4)->changed; // true
$tracked->add(4)->changed; // false - already present
```

Note: `$changed` is only available on the return value of mutation methods, not on the tracked wrapper itself.

### Mutation

| Method | Description |
|--------|-------------|
| `add(mixed $element): MutableCollection` | Add a single element |
| `addFirst(mixed $element): MutableCollection` | Add element at the beginning. For Sets: moves to front if already present. For Lists: always prepends |
| `addAll(iterable $elements): MutableCollection` | Add all elements from iterable |
| `removeElement(mixed $element): MutableCollection` | Remove first occurrence of element |
| `removeFirst(): MutableCollection` | Remove the first element (no-op if empty) |
| `removeLast(): MutableCollection` | Remove the last element (no-op if empty) |
| `removeIf(Closure $predicate): MutableCollection` | Remove elements matching predicate |
| `removeAll(iterable $elements): MutableCollection` | Remove all occurrences of given elements |
| `retainAll(iterable $elements): MutableCollection` | Keep only elements present in iterable |
| `clear(): MutableCollection` | Remove all elements |

### In-place ordering

| Method | Description |
|--------|-------------|
| `sort(): MutableCollection` | Natural ascending order |
| `sortDesc(): MutableCollection` | Natural descending order |
| `sortBy(Closure $selector): MutableCollection` | By selector |
| `sortByDesc(Closure $selector): MutableCollection` | By selector, descending |
| `sortWith(Closure $comparator): MutableCollection` | By comparator |
| `reverse(): MutableCollection` | Reverse in-place |
| `shuffle(): MutableCollection` | Shuffle in-place |

## ImmutableCollection Methods

In addition to all `Collection` methods, `ImmutableCollection<E>` provides (all `#[NoDiscard]`):

| Method | Description |
|--------|-------------|
| `add(mixed $element): ImmutableCollection` | New collection with element added |
| `addFirst(mixed $element): ImmutableCollection` | New collection with element at the beginning. For Sets: moves to front if already present. For Lists: always prepends |
| `addAll(iterable $elements): ImmutableCollection` | New collection with elements added |
| `removeElement(mixed $element): ImmutableCollection` | New collection without first occurrence of element |
| `removeFirst(): ImmutableCollection` | New collection without the first element (returns same collection if empty) |
| `removeLast(): ImmutableCollection` | New collection without the last element (returns same collection if empty) |
| `removeIf(Closure $predicate): ImmutableCollection` | New collection without matching elements |
| `removeAll(iterable $elements): ImmutableCollection` | New collection without given elements |
| `retainAll(iterable $elements): ImmutableCollection` | New collection with only given elements |
