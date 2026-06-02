---
pageClass: api-reference
---

# Map API

`Map<K,V>` is an ordered key-value associative collection with strict key handling. Unlike PHP arrays, keys are never silently cast. Extends `IteratorAggregate<K,V>`, `Countable`, and `ArrayAccess<K,V>`.

## Properties

```php
public Set<K> $keys { get; }
```
Live read-only view of the map's keys.

```php
public Collection<V> $values { get; }
```
Live read-only view of the map's values.

```php
public Set<MapEntry<K,V>> $entries { get; }
```
Live read-only view of the map's entries. Each `MapEntry` is a snapshot. `MapEntry` implements `Hashable` (identity derived from both key and value), so set operations like `intersect`, `union`, and `subtract` compare entries by content.

## Element Access

```php
get(mixed $key): V
```
Get value by key. Throws `NoSuchElementException` if missing. Array access syntax `$map['key']` is an alias for `$map->get('key')`.

```php
getOrNull(mixed $key): V|null
```
Get value, or `null` if missing. Use `$map['key'] ?? null` for the same behavior via array access.

```php
getOrDefault(mixed $key, mixed $default): V|D
```
Get value, or the provided default.

```php
getOrCompute(mixed $key, Closure $compute): V|D
```
Get value, or compute with `(): D`.

```php
getOrPut(mixed $key, Closure $compute): V // MutableMap only
```
Get value if key exists. Otherwise, compute the default, store it in the map, and return it.

## Array Access

```php
$map['key']; // get - throws if missing
$map['key'] ?? 'default'; // safe access via ?? operator
isset($map['key']); // containsKey
```

On `MutableMap`:
```php
$map['key'] = 'value'; // put
unset($map['key']); // remove
```

On `ImmutableMap`, `offsetSet` and `offsetUnset` throw `UnsupportedOperationException`.

## Querying

```php
containsKey(mixed $key): bool
```
Whether the map contains the key.

```php
containsValue(mixed $value): bool
```
Whether the map contains the value (strict comparison).

```php
all(Closure $predicate): bool
```
All entries satisfy predicate `(V, K): bool`.

```php
any(Closure $predicate): bool
```
Any entry satisfies predicate.

```php
none(Closure $predicate): bool
```
No entry satisfies predicate.

```php
count(): int
```
Returns the number of entries in the map.

```php
countWhere(Closure $predicate): int
```
Returns the number of entries matching the predicate `(V, K): bool`.

```php
isEmpty(): bool
```
Whether the map has no entries.

```php
isNotEmpty(): bool
```
Whether the map has at least one entry.

## Transformation

All transformation methods are marked `#[NoDiscard]` and **always return immutable collections**, regardless of whether the source map is mutable or immutable.

```php
filter(Closure $predicate): ImmutableMap<K,V>
```
Filter entries by `(V, K): bool`.

```php
filterKeys(Closure $predicate): ImmutableMap<K,V>
```
Filter by key predicate `(K): bool`.

```php
filterValues(Closure $predicate): ImmutableMap<K,V>
```
Filter by value predicate `(V): bool`.

```php
filterValuesNotNull(): ImmutableMap<K,V>
```
Exclude entries with `null` values.

```php
filterValuesInstanceOf(string $type): ImmutableMap<K,T>
```
Filter entries whose values are instances of the given class or interface. `$type` is a `class-string<T>`. Enables PHPStan to narrow the value type — e.g. `Map<string, Animal>` becomes `ImmutableMap<string, Dog>`.

```php
mapKeys(Closure $transform): ImmutableMap<NK,V>
```
Transform keys `(V, K): NK`.

```php
mapValues(Closure $transform): ImmutableMap<K,NV>
```
Transform values `(V, K): NV`.

```php
mapValuesNotNull(Closure $transform): ImmutableMap<K,NV>
```
Transform values `(V, K): NV|null` and exclude entries where the result is `null`. Combines `mapValues` and `filterValuesNotNull` in a single operation.

```php
flip(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): ImmutableMap<V,K>
```
Swap keys and values. Throws on duplicate values by default.

## Ordering

All return new immutable maps, marked `#[NoDiscard]`.

| Method | Signature | Description |
|--------|-----------|-------------|
| `sortedByKey` | `(?Closure $selector): ImmutableMap` | By key, optional selector |
| `sortedByKeyDesc` | `(?Closure $selector): ImmutableMap` | By key desc |
| `sortedByValue` | `(?Closure $selector): ImmutableMap` | By value, optional selector |
| `sortedByValueDesc` | `(?Closure $selector): ImmutableMap` | By value desc |
| `sortedBy` | `(Closure $selector): ImmutableMap` | By `(V,K): R` selector |
| `sortedByDesc` | `(Closure $selector): ImmutableMap` | By selector, desc |
| `sortedWithKey` | `(Closure $comparator): ImmutableMap` | Key comparator `(K,K): int` |
| `sortedWithValue` | `(Closure $comparator): ImmutableMap` | Value comparator `(V,V): int` |
| `sortedWith` | `(Closure $comparator): ImmutableMap` | `(MapEntry, MapEntry): int` |
| `reversed` | `(): ImmutableMap` | Reversed order |
| `shuffled` | `(): ImmutableMap` | Random order |

## Slicing

All return new immutable maps, marked `#[NoDiscard]`.

| Method | Signature | Description |
|--------|-----------|-------------|
| `takeFirst` | `(int $n = 1): ImmutableMap` | First N entries |
| `takeLast` | `(int $n = 1): ImmutableMap` | Last N entries |
| `dropFirst` | `(int $n = 1): ImmutableMap` | All except first N entries |
| `dropLast` | `(int $n = 1): ImmutableMap` | All except last N entries |
| `takeWhile` | `(Closure $predicate): ImmutableMap` | Entries from start while `(V, K): bool` is true |
| `dropWhile` | `(Closure $predicate): ImmutableMap` | Drop from start while `(V, K): bool` is true, return rest |
| `takeLastWhile` | `(Closure $predicate): ImmutableMap` | Entries from end while `(V, K): bool` is true |
| `dropLastWhile` | `(Closure $predicate): ImmutableMap` | Drop from end while `(V, K): bool` is true, return rest |

## Iteration

```php
forEach(Closure $action): Map
```
Execute `(V, K): void` for each entry. Returns the map for chaining.

```php
forEachKey(Closure $action): Map
```
Execute `(K): void` for each key. Returns the map for chaining.

```php
forEachValue(Closure $action): Map
```
Execute `(V): void` for each value. Returns the map for chaining.

## Conversion

```php
jsonSerialize(): array
```
Returns `toArray()` for `json_encode()` compatibility. Produces `{"key": "value", ...}` JSON output. Throws `ConversionException` if the map contains object keys or keys that collide after PHP's native casting (e.g. string `"1"` and int `1`). For maps with non-scalar keys, use `toPairs()` or `mapKeys()` to prepare the data manually before encoding.

```php
map(Closure $transform): ImmutableList<R>
```
Transform each entry with `(V, K): R` and return results as a list.

```php
mapNotNull(Closure $transform): ImmutableList<R>
```
Transform each entry with `(V, K): R|null` and return non-null results as a list. Combines `map()` and `filterNotNull()` in a single operation.

```php
flatMap(Closure $transform): ImmutableList<R>
```
Transform each entry with `(V, K): iterable<R>` and flatten all results into a single list.

```php
toArray(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): array
```
Convert to PHP array. Only works with scalar keys. Throws `ConversionException` for object keys or key collisions.

```php
toPairs(): array{0:K, 1:V}[]
```
Convert to array of `[key, value]` pairs.

```php
toMutable(): MutableMap<K,V>
```
Always creates a new mutable copy.

```php
toImmutable(): ImmutableMap<K,V>
```
Returns an immutable map. May return itself if already immutable.

## MutableMap Methods

### Tracking

```php
tracked(): MutableTrackedMap<K,V>
```
Returns a tracked wrapper that shares the same underlying store. Each mutation method on the tracked wrapper returns a result with a `$changed` property indicating whether that operation modified the data:

```php
$tracked = mutableMapOf(['a' => 1])->tracked();
$tracked->put('b', 2)->changed; // true
$tracked->put('b', 2)->changed; // false - same value
```

Note: `$changed` is only available on the return value of mutation methods, not on the tracked wrapper itself.

### Mutation: Add

| Method | Description |
|--------|-------------|
| `put(mixed $key, mixed $value): MutableMap` | Add or replace an entry |
| `putIfAbsent(mixed $key, mixed $value): MutableMap` | Add entry only if the key is not already present |
| `putFirst(mixed $key, mixed $value): MutableMap` | Add or replace at the beginning. Moves existing key to front |
| `putAll(iterable $data): MutableMap` | Add entries from key-value iterable |
| `putAllPairs(iterable $data): MutableMap` | Add entries from `[key, value]` pairs |

### Mutation: Remove

| Method | Description |
|--------|-------------|
| `remove(mixed $key): MutableMap` | Remove entry by key |
| `removeFirst(): MutableMap` | Remove the first entry (no-op if empty) |
| `removeLast(): MutableMap` | Remove the last entry (no-op if empty) |
| `removeIf(Closure $predicate): MutableMap` | Remove entries matching `(V,K): bool` |
| `removeIfKey(Closure $predicate): MutableMap` | Remove by key predicate `(K): bool` |
| `removeIfValue(Closure $predicate): MutableMap` | Remove by value predicate `(V): bool` |
| `removeNullValues(): MutableMap` | Remove entries with `null` values |
| `clear(): MutableMap` | Remove all entries |

### Mutation: Order

| Method | Description |
|--------|-------------|
| `sortByKey(?Closure): MutableMap` | In-place by key |
| `sortByKeyDesc(?Closure): MutableMap` | In-place by key desc |
| `sortByValue(?Closure): MutableMap` | In-place by value |
| `sortByValueDesc(?Closure): MutableMap` | In-place by value desc |
| `sortBy(Closure): MutableMap` | In-place by `(V,K): R` |
| `sortByDesc(Closure): MutableMap` | In-place by selector desc |
| `sortWithKey(Closure): MutableMap` | In-place `(K,K): int` |
| `sortWithValue(Closure): MutableMap` | In-place `(V,V): int` |
| `sortWith(Closure): MutableMap` | In-place `(MapEntry, MapEntry): int` |
| `reverse(): MutableMap` | In-place reverse |
| `shuffle(): MutableMap` | In-place shuffle |

## ImmutableMap Methods

All mutation methods are marked `#[NoDiscard]` and return new `ImmutableMap` instances:

### Mutation: Add

| Method | Description |
|--------|-------------|
| `put(mixed $key, mixed $value): ImmutableMap` | New map with entry added |
| `putIfAbsent(mixed $key, mixed $value): ImmutableMap` | New map with entry added only if key is not present. Returns same map if key exists |
| `putFirst(mixed $key, mixed $value): ImmutableMap` | New map with entry at the beginning. Moves existing key to front |
| `putAll(iterable $data): ImmutableMap` | New map with entries added |
| `putAllPairs(iterable $data): ImmutableMap` | New map with pair entries added |

### Mutation: Remove

| Method | Description |
|--------|-------------|
| `remove(mixed $key): ImmutableMap` | New map without the key |
| `removeFirst(): ImmutableMap` | New map without the first entry (returns same map if empty) |
| `removeLast(): ImmutableMap` | New map without the last entry (returns same map if empty) |
| `removeIf(Closure): ImmutableMap` | New map without matching entries |
| `removeIfKey(Closure): ImmutableMap` | New map without matching keys |
| `removeIfValue(Closure): ImmutableMap` | New map without matching values |
| `removeNullValues(): ImmutableMap` | New map without null values |
