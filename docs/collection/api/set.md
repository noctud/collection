---
pageClass: api-reference
---

# Set API

`Set<E>` extends `Collection<E>` for ordered collections of unique elements. Duplicate values are silently ignored on insertion. Sets do not support indexed access. See [Collection API](./collection) for inherited methods.

## Conversion

```php
toMutable(): MutableCollection<E>
```
Always creates a new mutable copy. The built-in sets return a `MutableSet`, but no set interface narrows the declared type, so PHPStan only sees `MutableCollection<E>`. Use `mutableSetOf($set)` when you need a `MutableSet<E>`.

```php
toImmutable(): ImmutableSet<E>
```
Returns an immutable set. May return itself if already immutable.

## MutableSet methods

`MutableSet` does not add methods beyond those inherited from `MutableCollection`. See [Collection API](./collection) for `add()`, `addFirst()`, `removeElement()`, `removeFirst()`, `removeLast()`, and other mutation methods.

::: tip addFirst on Sets
`addFirst()` uses **move-to-front** semantics on sets: if the element already exists, it is moved to the first position. If the element is already first, the call is a no-op. See the [Set guide](../set#addfirst-move-to-front) for examples.
:::

## ImmutableSet methods

`ImmutableSet` does not add methods beyond those inherited from `ImmutableCollection`. See [Collection API](./collection) for `add()`, `addFirst()`, `removeElement()`, `removeFirst()`, `removeLast()`, and other mutation methods.
