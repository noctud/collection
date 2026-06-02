---
pageClass: api-reference
---

# List API

`ListInterface<E>` extends `Collection<E>` and `ArrayAccess<int, E>` with positional access by integer index. See [Collection API](./collection) for inherited methods.

## Element Access

```php
get(int $index): E
```
Returns the element at the given index. Throws `IndexOutOfBoundsException` if out of bounds. Array access syntax `$list[0]` is an alias for `$list->get(0)`.

```php
getOrNull(int $index): E|null
```
Returns the element at the index, or `null` if out of bounds. Use `$list[0] ?? null` for the same behavior via array access.

```php
getOrDefault(int $index, mixed $default): E|D
```
Returns the element at the index, or the provided default.

```php
getOrCompute(int $index, Closure $compute): E|D
```
Returns the element at the index, or computes a value with the given closure.

```php
indexOf(mixed $element): int
```
Index of the first occurrence (strict comparison), or `-1` if not found.

```php
lastIndexOf(mixed $element): int
```
Index of the last occurrence, or `-1`.

```php
indexOfFirst(Closure $predicate): int
```
Index of the first element matching the predicate `(E, int): bool`, or `-1`.

```php
indexOfLast(Closure $predicate): int
```
Index of the last matching element, or `-1`.

```php
slice(int $from, int $to): ListInterface<E>
```
View from index `$from` (inclusive) to `$to` (exclusive). Throws `IndexOutOfBoundsException` if out of bounds.

## Array Access

Lists implement `ArrayAccess<int, E>`:

```php
$list[0]; // get(0) - throws if out of bounds
$list[0] ?? 'default'; // safe access via ?? operator
isset($list[0]); // offsetExists - true if index is valid
```

**MutableList** supports mutation via array syntax:

```php
$list[0] = 'x'; // set(0, 'x')
$list[] = 'y'; // add('y') - append
unset($list[0]); // removeAt(0)
```

**ImmutableList** throws `UnsupportedOperationException` for mutation operations (`$list[0] = ...`, `$list[] = ...`, `unset($list[0])`).

## Conversion

```php
toIndexedMap(?Closure $valueTransform = null): ImmutableMap<int, V>
```
Convert to an immutable map using index as key, with optional value transform `(E, int): V`.

```php
toMutable(): MutableList<E>
```
Always creates a new mutable copy.

```php
toImmutable(): ImmutableList<E>
```
Returns an immutable list. May return itself if already immutable.

## MutableList Methods

In addition to `MutableCollection` methods:

```php
set(int $index, mixed $element): MutableList<E>
```
Replace element at index. Throws `IndexOutOfBoundsException` if out of bounds. Returns `$this`.

```php
removeEvery(mixed $element): MutableList<E>
```
Remove all occurrences of the element. Returns `$this`.

```php
removeAt(int $index): MutableList<E>
```
Remove element at index. Throws `OutOfBoundsException`. Returns `$this`.

## ImmutableList Methods

In addition to `ImmutableCollection` methods (all `#[NoDiscard]`):

```php
set(int $index, mixed $element): ImmutableList<E>
```
New list with element at index replaced.

```php
removeEvery(mixed $element): ImmutableList<E>
```
New list without any occurrences of the element.

```php
removeAt(int $index): ImmutableList<E>
```
New list without the element at the given index.
