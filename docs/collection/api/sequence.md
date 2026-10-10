---
pageClass: api-reference
outline: [2, 3]
---

# Sequence API

`Sequence<E>` is a lazily evaluated pipeline of elements — see the [Sequence guide](../sequence) for how it works. It extends only `IteratorAggregate<int, E>`: it is neither a `Collection`, nor `Countable`, nor `JsonSerializable`.

Create one with [`asSequence()`](./collection#conversion) on any collection, or with the [`sequenceOf()` and `generateSequence()`](./functions#sequence) factory functions.

::: info Every terminal operation is a pass
Intermediate operations return a new `Sequence` and pull nothing. Each terminal operation — and each `foreach` — pulls from the source again. A source that can't produce another pass throws `NonReplayableSourceException`, and a closure returning something that is not iterable throws `InvalidSequenceSourceException`. Both implement `SourceException`, which every terminal operation declares. See [Iterating More Than Once](../sequence#iterating-more-than-once).
:::

## Intermediate Operations

All intermediate operations are marked `#[NoDiscard]`, return a new `Sequence`, and run nothing until a terminal operation pulls. Callbacks receive the element and its position `(E, int)` — the position in that step's input, the same one an eager chain would pass.

### Filtering

```php
filter(Closure $predicate): Sequence<E>
```
Keep elements matching the predicate `(E, int): bool`.

```php
filterNotNull(): Sequence<E>
```
Skip `null` elements. The element type narrows to exclude `null` — `Sequence<string|null>` becomes `Sequence<string>`.

```php
filterInstanceOf(string $type): Sequence<T>
```
Keep elements that are instances of the given class or interface. `$type` is a `class-string<T>`, so the element type narrows to `T`.

### Mapping

```php
map(Closure $transform): Sequence<R>
```
Transform each element. Closure: `(E, int): R`.

```php
mapNotNull(Closure $transform): Sequence<R>
```
Transform each element with `(E, int): R|null` and skip `null` results.

```php
flatMap(Closure $transform): Sequence<R>
```
Transform each element into an iterable with `(E, int): iterable<R>` and yield its elements. Each iterable is consumed lazily, element by element.

```php
flatten(): Sequence<V>
```
Flatten one level: iterable elements contribute their own elements, non-iterable elements are kept as-is. Nested iterables are consumed lazily.

### Slicing

```php
takeFirst(int $n = 1): Sequence<E>
```
The first N elements. Stops pulling once N elements have been yielded.

```php
takeWhile(Closure $predicate): Sequence<E>
```
Elements while the predicate `(E, int): bool` holds. Stops pulling at the first element that fails it.

```php
dropFirst(int $n = 1): Sequence<E>
```
Skip the first N elements, yield the rest.

```php
dropWhile(Closure $predicate): Sequence<E>
```
Skip elements while the predicate `(E, int): bool` holds, yield the rest — starting with the first element that failed it.

### Distinct

```php
distinct(): Sequence<E>
```
Unique elements by identity, first occurrence kept. Remembers every distinct element seen during the pass, so memory grows with the number of distinct elements.

```php
distinctBy(Closure $selector): Sequence<E>
```
Unique elements by selector `(E, int): mixed` value, first occurrence kept. Remembers every distinct selector value seen during the pass.

### Windowing

```php
chunked(int $size): Sequence<ImmutableList<E>>
```
Split into chunks of the given size, each yielded as soon as it fills up. The last chunk may be smaller. Holds only the chunk being filled. A non-positive size yields nothing.

```php
windowed(
    int $size,
    int $step = 1,
    bool $partialWindows = false,
): Sequence<ImmutableList<E>>
```
Yield a window of the given size sliding along the sequence with the given step. When `$partialWindows` is `true`, the shorter windows at the end are yielded too. Holds at most `$size` elements at a time. A non-positive size or step yields nothing.

### Zipping

```php
zip(iterable $other): Sequence<array{E, U}>
```
Pair elements at the same position with another iterable. The result has the length of the shorter input. The other side is pulled in lockstep, not copied — if it can be iterated only once, so can the result.

::: tip Zipping with a stream
A `Generator` is zipped from where it stands, not rewound — so you can read the head of a stream yourself and zip the rest. After a `foreach` that ended with `break`, call `next()` first: the generator still points at the element you stopped on. Any other iterator is rewound first, unless you wrap it in a `NoRewindIterator`.
:::

```php
zipWithNext(): Sequence<array{E, E}>
```
Pair each two adjacent elements. Yields nothing if the sequence has fewer than two elements.

### Peeking

```php
onEach(Closure $action): Sequence<E>
```
Run the action `(E, int): void` on each element as it passes through, then yield the element unchanged. The lazy counterpart of `forEach()` — nothing runs before a terminal operation pulls, and the action runs again on every pass.

## Terminal Operations

Each terminal operation runs one pass over the source. Some stop pulling as soon as they know the answer; the others drain the sequence, and never return on an infinite one.

### Element Access

```php
first(): E
```
Returns the first element. Throws `NoSuchElementException` if empty. Pulls at most one element.

```php
firstOrNull(): E|null
```
Returns the first element, or `null` if empty. Pulls at most one element.

```php
last(): E
```
Returns the last element. Throws `NoSuchElementException` if empty. Drains the sequence — the last element is only known at the end.

```php
lastOrNull(): E|null
```
Returns the last element, or `null` if empty. Drains the sequence.

```php
single(): E
```
Returns the single element. Throws `NoSuchElementException` if empty or more than one element. Pulls at most two elements — a second one is already an error.

```php
singleOrNull(): E|null
```
Returns the single element, or `null` if empty or more than one element. Pulls at most two elements.

```php
elementAt(int $index): E
```
Returns the element at the given position. Throws `IndexOutOfBoundsException` if the index is negative, or if the sequence runs out before reaching it. Pulls up to that position and no further.

```php
elementAtOrNull(int $index): E|null
```
Returns the element at the given position, or `null` when there is none — a negative index included. Pulls up to that position and no further.

```php
find(Closure $predicate): E|null
```
Returns the first element matching the predicate `(E, int): bool`, or `null` if no element matches. Stops at the first match.

```php
expect(Closure $predicate): E
```
Returns the first element matching the predicate `(E, int): bool`. Throws `NoSuchElementException` if no element matches. Stops at the first match.

```php
findLast(Closure $predicate): E|null
```
Returns the last element matching the predicate `(E, int): bool`, or `null` if no element matches. Drains the sequence.

```php
expectLast(Closure $predicate): E
```
Returns the last element matching the predicate `(E, int): bool`. Throws `NoSuchElementException` if no element matches. Drains the sequence.

### Querying

```php
isEmpty(): bool
```
Whether the sequence has no elements. Pulls at most one element.

```php
isNotEmpty(): bool
```
Whether the sequence has at least one element. Pulls at most one element.

```php
contains(mixed $element): bool
```
Whether the sequence contains the value (strict comparison). Stops at the first match.

```php
containsAll(iterable $elements): bool
```
Whether the sequence contains all provided values. Walks the sequence once, and stops as soon as every value has been found.

```php
all(Closure $predicate): bool
```
Returns `true` if all elements match the predicate `(E, int): bool`. Stops at the first element that doesn't.

```php
any(Closure $predicate): bool
```
Returns `true` if any element matches the predicate `(E, int): bool`. Stops at the first match.

```php
none(Closure $predicate): bool
```
Returns `true` if no element matches the predicate `(E, int): bool`. Stops at the first match.

```php
count(): int<0, max>
```
Returns the number of elements. O(n) — drains the sequence. A sequence is not `Countable`, so the native `count($sequence)` throws a `TypeError`; the cost has to be asked for explicitly.

```php
countWhere(Closure $predicate): int<0, max>
```
Returns the number of elements matching the predicate `(E, int): bool`. Drains the sequence.

### Aggregation

```php
fold(mixed $initial, Closure $operation): R
```
Left fold. Accumulates a result starting from the initial value by applying `(R, E): R` to each element. Drains the sequence.

```php
reduce(Closure $operation): E
```
Reduce with a binary operation `(E, E): E`. Throws `UnsupportedOperationException` if empty. Drains the sequence.

```php
reduceOrNull(Closure $operation): E|null
```
Reduce, or `null` if empty. Drains the sequence.

```php
sum(?Closure $selector = null): int|float
```
Sum of all elements, or of values returned by the selector `(E, int): int|float`. The return type narrows to `int` when every summed value is an `int`. Drains the sequence.

```php
avg(?Closure $selector = null): float
```
Average. Optional selector `(E, int): int|float`. Throws `UnsupportedOperationException` if empty. Drains the sequence.

```php
avgOrNull(?Closure $selector = null): float|null
```
Average, or `null` if empty. Optional selector `(E, int): int|float`. Drains the sequence.

```php
min(?Closure $selector = null): E
```
Element with minimum value. With a selector `(E, int): mixed`, returns the element whose selector value is minimum. Throws `NoSuchElementException` if empty. Drains the sequence.

```php
minOrNull(?Closure $selector = null): E|null
```
Minimum element, or `null` if empty. Optional selector `(E, int): mixed`. Drains the sequence.

```php
max(?Closure $selector = null): E
```
Element with maximum value. Optional selector `(E, int): mixed`. Throws `NoSuchElementException` if empty. Drains the sequence.

```php
maxOrNull(?Closure $selector = null): E|null
```
Maximum element, or `null` if empty. Optional selector `(E, int): mixed`. Drains the sequence.

```php
minOf(Closure $selector): R
```
Returns the minimum value produced by the selector `(E, int): R` — the **selector value** itself, not the element. Throws `NoSuchElementException` if empty. Drains the sequence.

```php
minOfOrNull(Closure $selector): R|null
```
Returns the minimum selector value, or `null` if empty. Drains the sequence.

```php
maxOf(Closure $selector): R
```
Returns the maximum value produced by the selector `(E, int): R` — the **selector value** itself, not the element. Throws `NoSuchElementException` if empty. Drains the sequence.

```php
maxOfOrNull(Closure $selector): R|null
```
Returns the maximum selector value, or `null` if empty. Drains the sequence.

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
Joins elements into a string. When `$transform` is provided, it is applied to each element `(E, int): string` before joining. Without a transform, elements are converted via `(string)` cast — scalars, `null`, and `Stringable` objects are supported. Throws `ConversionException` for non-stringable objects and arrays. With a non-negative `$limit`, it pulls only `$limit + 1` elements (the extra one decides whether `$truncated` is appended), so it is safe on infinite sequences. Without a limit, it drains the sequence.

### Grouping

These terminal operations end the pipeline and hand back immutable collections.

```php
groupBy(
    Closure $keySelector,
    ?Closure $valueTransform = null,
): ImmutableMap<K, ImmutableList<E>>
```
Group elements by the key returned by the selector `(E, int): K`. When a `$valueTransform` `(E, int): V` is provided, each element is transformed before being added to its group — the result is `ImmutableMap<K, ImmutableList<V>>`. Holds every element, since the last one may still belong to the first group. Throws `InvalidKeyTypeException` if the selector returns a value that can't be a map key.

```php
countBy(Closure $keySelector): ImmutableMap<NK, int>
```
Count elements per key returned by the selector `(E, int): NK`. Holds only one counter per key, never the elements. Throws `InvalidKeyTypeException` if the selector returns a value that can't be a map key.

```php
partition(
    Closure $predicate,
): array{ImmutableList<E>, ImmutableList<E>}
```
Split into two lists — the elements matching the predicate `(E, int): bool`, then the rest.

```php
unzip(): array{ImmutableList<mixed>, ImmutableList<mixed>}
```
Split a sequence of pairs into two lists — one from the first component (`[0]`), one from the second (`[1]`). Inverse of `zip()`. Throws `UnsupportedOperationException` if an element is not a pair.

### Iteration

```php
forEach(Closure $action): void
```
Run the action `(E, int): void` for each element. Returns nothing — use `onEach()` to act on elements without ending the chain. A plain `foreach` over the sequence is a pass too, with positional keys `0, 1, 2, …` whatever keys the source had.

### Conversion

```php
toList(): ImmutableList<E>
```
Convert to an immutable list preserving iteration order.

```php
toSet(): ImmutableSet<E>
```
Convert to an immutable set (duplicates removed).

```php
toArray(): list<E>
```
Convert to a PHP array.

```php
toMap(
    Closure $keySelector,
    ?Closure $valueTransform = null,
): ImmutableMap<K, V>
```
Convert to an immutable map using key selector `(E, int): K` and optional value transform `(E, int): V`.

## Not Available on Sequence

A sequence leaves these `Collection` methods out — most of them need the whole source before they can produce anything, the rest are clearer on a materialized collection. Materialize first, and the cost is visible in your code:

| Collection method | With a sequence |
|---|---|
| `sorted*()`, `reversed()` | `->toList()->sorted()` |
| `shuffled()`, `random*()` | `->toList()->random()` |
| `takeLast*()`, `dropLast*()` | `->toList()->takeLast(3)` |
| `intersect()`, `union()` | `->toSet()->intersect($other)` |
| `subtract()` | `->toSet()->subtract($other)` |
| `toMutable()` | `->toList()->toMutable()` |
| `count($collection)` | `$sequence->count()` |
| `json_encode($collection)` | `json_encode($sequence->toList())` |

See [No Hidden Materialization](../design#no-hidden-materialization) for the reasoning.
