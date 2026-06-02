---
pageClass: api-reference
---

# Factory Functions

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

## List

### listOf

```php
function listOf(iterable|Closure $data = []): ImmutableList
```

Creates an immutable list. If the given data is a `Closure`, the list is lazily initialized on first access.

```php
listOf([1, 2, 3]); // ImmutableList<int>
listOf(); // empty ImmutableList
listOf(fn() => loadElements()); // lazy ImmutableList
```

### mutableListOf

```php
function mutableListOf(iterable|Closure $data = []): MutableList
```

Creates a mutable list. Supports lazy initialization via `Closure`.

```php
mutableListOf([1, 2, 3]); // MutableList<int>
mutableListOf(); // empty MutableList
mutableListOf(fn() => loadElements()); // lazy MutableList
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
setOf(fn() => loadUniqueIds()); // lazy ImmutableSet
```

### mutableSetOf

```php
function mutableSetOf(iterable|Closure $data = []): MutableSet
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
mapOf(fn() => loadConfig()); // lazy ImmutableMap
```

::: warning
When passing a PHP array, standard PHP key casting rules apply *before* the map receives the data. Use `mapOfPairs` to preserve exact key types.
:::

### mutableMapOf

```php
function mutableMapOf(iterable|Closure $data = []): MutableMap
```

Creates a mutable map. Supports lazy initialization.

```php
mutableMapOf(['x' => 10]); // MutableMap<string, int>
mutableMapOf(); // empty MutableMap
```

### mapOfPairs

```php
function mapOfPairs(iterable|Closure $data = []): ImmutableMap
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

## Type-Specific Maps

### stringMapOf

```php
function stringMapOf(iterable|Closure $data = []): ImmutableMap
```

Creates an immutable map optimized for string keys. Uses single-array storage with zero-copy initialization from arrays. Integer keys are accepted during construction and automatically converted to strings.

```php
stringMapOf(['rodney' => 38, 'sheppard' => 40]); // ImmutableMap<string, int>
stringMapOf(); // empty ImmutableMap
stringMapOf(fn() => loadUsers()); // lazy ImmutableMap
```

### mutableStringMapOf

```php
function mutableStringMapOf(iterable|Closure $data = []): MutableMap
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
intMapOf(fn() => loadScores()); // lazy ImmutableMap
```

### mutableIntMapOf

```php
function mutableIntMapOf(iterable|Closure $data = []): MutableMap
```

Creates a mutable map optimized for integer keys.

```php
mutableIntMapOf([1 => 100, 2 => 200]); // MutableMap<int, int>
mutableIntMapOf(); // empty MutableMap
```

::: tip When to use type-specific maps
Use `stringMapOf` / `intMapOf` when you know all keys will be strings or integers. They offer ~50% less memory usage and faster operations compared to `mapOf`. Use `mapOf` when you need mixed key types (objects, float, bool). IntMap strictly enforces int keys; StringMap enforces string keys on `put()` but accepts PHP's natural key casting during construction.
:::

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

All functions accept an empty argument for creating empty collections, and a `Closure` for lazy initialization.
