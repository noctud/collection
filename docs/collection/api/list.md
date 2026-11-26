---
pageClass: api-reference
---

# List API

`ListInterface<E>` extends `Collection<E>` and `ArrayAccess<int, E>` with positional access by integer index. See [Collection API](./collection) for inherited methods.

## Element access

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
Copies the elements from index `$from` (inclusive) to `$to` (exclusive) into a new list. It is a copy, not a view. `ImmutableList`, `MutableList` and `WritableList` declare it as `ImmutableList<E>`, and later changes to a mutable source don't reach it. Throws `IndexOutOfBoundsException` if `$to` is greater than the list size.

## Array access

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
toMutable(): MutableCollection<E>
```
Always creates a new mutable copy. The built-in lists return a `MutableList`, but no list interface narrows the declared type, so PHPStan only sees `MutableCollection<E>` and rejects calls like `set()` on it. Use `mutableListOf($list)` when you need a `MutableList<E>`.

```php
toImmutable(): ImmutableList<E>
```
Returns an immutable list. May return itself if already immutable.

## MutableList methods

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
Remove element at index. Throws `IndexOutOfBoundsException` if out of bounds. Returns `$this`.

## ImmutableList methods

In addition to `ImmutableCollection` methods (all `#[NoDiscard]`):

```php
set(int $index, mixed $element): ImmutableList<E|NE>
```
New list with element at index replaced. Throws `IndexOutOfBoundsException` if out of bounds. The element type widens to `E|NE`, like `add()`.

```php
removeEvery(mixed $element): ImmutableList<E>
```
New list without any occurrences of the element.

```php
removeAt(int $index): ImmutableList<E>
```
New list without the element at the given index. Throws `IndexOutOfBoundsException` if out of bounds.
