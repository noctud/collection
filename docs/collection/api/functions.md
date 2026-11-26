---
pageClass: api-reference
---

# Factory functions

All factory functions are in the `Noctud\Collection` namespace. Import them with `use function`:

```php
use function Noctud\Collection\listOf;
use function Noctud\Collection\mutableListOf;
use function Noctud\Collection\setOf;
use function Noctud\Collection\mutableSetOf;
use function Noctud\Collection\mapOf;
use function Noctud\Collection\mutableMapOf;
use function Noctud\Collection\mapOfPairs;
use function Noctud\Collection\mutableMapOfPairs;
```

For type-specific maps with optimized storage:

```php
use function Noctud\Collection\stringMapOf;
use function Noctud\Collection\mutableStringMapOf;
use function Noctud\Collection\intMapOf;
use function Noctud\Collection\mutableIntMapOf;
```

For lazy sequences:

```php
use function Noctud\Collection\sequenceOf;
use function Noctud\Collection\generateSequence;
```

## List

### listOf

```php
function listOf(iterable|Closure $data = []): ImmutableList
```

Creates an immutable list. If the given data is a `Closure`, the list is lazily initialized on first access.

```php
listOf([1, 2, 3]); // ImmutableList<int>
listOf(); // empty ImmutableList
listOf(fn() => loadElements()); // lazy-init ImmutableList
```

### mutableListOf

```php
function mutableListOf(iterable|Closure|null $data = null): MutableList
```

Creates a mutable list. Supports lazy initialization via `Closure`.

```php
mutableListOf([1, 2, 3]); // MutableList<int>
mutableListOf(); // empty MutableList
mutableListOf(fn() => loadElements()); // lazy-init MutableList
```

## Set

### setOf

```php
function setOf(iterable|Closure $data = []): ImmutableSet
```

Creates an immutable set. Duplicate values are discarded (first occurrence kept). Supports lazy initialization via `Closure`.

```php
setOf(['a', 'b', 'c']); // ImmutableSet<string>
setOf([1, 2, 2, 3]); // ImmutableSet {1, 2, 3}
setOf(); // empty ImmutableSet
setOf(fn() => loadUniqueIds()); // lazy-init ImmutableSet
```

### mutableSetOf

```php
function mutableSetOf(iterable|Closure|null $data = null): MutableSet
```

Creates a mutable set. Duplicates discarded. Supports lazy initialization.

```php
mutableSetOf(['a', 'b']); // MutableSet<string>
mutableSetOf(); // empty MutableSet
```

## Map

### mapOf

```php
function mapOf(iterable|Closure $data = []): ImmutableMap
```

Creates an immutable map. Supports lazy initialization via `Closure`.

```php
mapOf(['a' => 1, 'b' => 2]); // ImmutableMap<string, int>
mapOf(); // empty ImmutableMap
mapOf(fn() => loadConfig()); // lazy-init ImmutableMap
```

::: warning
When passing a PHP array, standard PHP key casting rules apply *before* the map receives the data. Use `mapOfPairs` to preserve exact key types.
:::

### mutableMapOf

```php
function mutableMapOf(iterable|Closure|null $data = null): MutableMap
```

Creates a mutable map. Supports lazy initialization.

```php
mutableMapOf(['x' => 10]); // MutableMap<string, int>
mutableMapOf(); // empty MutableMap
```

### mapOfPairs

```php
function mapOfPairs(iterable|Closure $data): ImmutableMap
```

Creates an immutable map from `[key, value]` pairs. This avoids PHP's array key casting, preserving exact key types.

```php
mapOfPairs([['1', 'a'], ['2', 'b']]); // ImmutableMap<string, string>
mapOfPairs([[$user, 'data']]); // ImmutableMap<User, string>
```

### mutableMapOfPairs

```php
function mutableMapOfPairs(iterable|Closure $data): MutableMap
```

Creates a mutable map from `[key, value]` pairs.

```php
mutableMapOfPairs([['1', 'a']]); // MutableMap<string, string>
```

## Type-specific maps

Use `stringMapOf` / `intMapOf` when you know all keys will be strings or integers. They offer ~50% less memory usage and faster operations compared to `mapOf`. Use `mapOf` when you need mixed key types (objects, float, bool). The IntMap constructors reject non-int keys, while StringMap accepts PHP's natural key casting during construction. After that, the mutable variants throw `InvalidKeyTypeException` for a key of another type, and the immutable ones return an `ImmutableHashMap` from `put()` and the other additions.

### stringMapOf

```php
function stringMapOf(iterable|Closure $data = []): ImmutableMap
```

Creates an immutable map optimized for string keys. Uses single-array storage with zero-copy initialization from arrays. Integer keys are accepted during construction and automatically converted to strings.

```php
stringMapOf(['rodney' => 38, 'sheppard' => 40]); // ImmutableMap<string, int>
stringMapOf(); // empty ImmutableMap
stringMapOf(fn() => loadUsers()); // lazy-init ImmutableMap
```

### mutableStringMapOf

```php
function mutableStringMapOf(iterable|Closure|null $data = null): MutableMap
```

Creates a mutable map optimized for string keys.

```php
mutableStringMapOf(['name' => 'Rodney']); // MutableMap<string, string>
mutableStringMapOf(); // empty MutableMap
```

### intMapOf

```php
function intMapOf(iterable|Closure $data = []): ImmutableMap
```

Creates an immutable map optimized for integer keys. Uses single-array storage with zero-copy initialization from arrays.

```php
intMapOf([1 => 'a', 2 => 'b']); // ImmutableMap<int, string>
intMapOf(); // empty ImmutableMap
intMapOf(fn() => loadScores()); // lazy-init ImmutableMap
```

### mutableIntMapOf

```php
function mutableIntMapOf(iterable|Closure|null $data = null): MutableMap
```

Creates a mutable map optimized for integer keys.

```php
mutableIntMapOf([1 => 100, 2 => 200]); // MutableMap<int, int>
mutableIntMapOf(); // empty MutableMap
```

## Sequence

### sequenceOf

```php
function sequenceOf(
    iterable|Closure $source = [],
    bool $constrainOnce = false,
): Sequence
```

Creates a lazy [Sequence](../sequence). Nothing is pulled from the source until a terminal operation runs. Whether the sequence can be iterated more than once depends on the source:

```php
sequenceOf([1, 2, 3]); // Sequence<int>, replayable
sequenceOf(fn() => readLines($path)); // called on every pass
sequenceOf($list); // reads the list again on every pass
sequenceOf($generator); // single pass only
sequenceOf(); // empty Sequence
```

::: warning A closure is a producer here
Unlike `listOf(fn() => ...)`, which calls the closure once and keeps the result, `sequenceOf(fn() => ...)` keeps nothing. The closure runs at the start of every pass and must return a fresh iterable each time. Set `constrainOnce: true` to limit any source to a single pass:

```php
sequenceOf(fn() => $api->fetchEvents(), constrainOnce: true);
```
:::

### generateSequence

```php
function generateSequence(mixed $seed, Closure $next): Sequence
```

Creates a sequence that starts with `$seed` and computes each next element from the previous one with `$next` `(E): E|null`. It ends at the first `null`. When `$next` never returns `null`, the sequence is infinite, so end it with `takeFirst()`, `takeWhile()` or a terminal operation that stops early. Every pass starts again from the seed.

```php
generateSequence(1, fn($n) => $n * 2)
    ->takeFirst(4)
    ->toList(); // [1, 2, 4, 8]

generateSequence($category, fn($c) => $c->parent); // up to the root
generateSequence(null, fn($n) => $n); // empty Sequence
```

## Summary

| Function | Returns | Key handling |
|----------|---------|-------------|
| `listOf` | `ImmutableList<E>` | N/A |
| `mutableListOf` | `MutableList<E>` | N/A |
| `setOf` | `ImmutableSet<E>` | N/A |
| `mutableSetOf` | `MutableSet<E>` | N/A |
| `mapOf` | `ImmutableMap<K,V>` | Subject to PHP array casting |
| `mutableMapOf` | `MutableMap<K,V>` | Subject to PHP array casting |
| `mapOfPairs` | `ImmutableMap<K,V>` | Preserves exact types |
| `mutableMapOfPairs` | `MutableMap<K,V>` | Preserves exact types |
| `stringMapOf` | `ImmutableMap<string,V>` | String keys only, optimized |
| `mutableStringMapOf` | `MutableMap<string,V>` | String keys only, optimized |
| `intMapOf` | `ImmutableMap<int,V>` | Int keys only, optimized |
| `mutableIntMapOf` | `MutableMap<int,V>` | Int keys only, optimized |
| `sequenceOf` | `Sequence<E>` | N/A |
| `generateSequence` | `Sequence<E>` | N/A |

All collection functions except `mapOfPairs` and `mutableMapOfPairs` accept an empty argument for creating empty collections, and all of them accept a `Closure` for lazy initialization. `sequenceOf()` accepts both too, but calls the `Closure` on every pass.
