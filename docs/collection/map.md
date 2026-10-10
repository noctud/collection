# Map

`Map<K,V>` is an ordered key-value collection with strict key handling. Unlike PHP arrays, Map preserves the original type of every key — `"1"` stays a string, `true` stays bool, and objects can be used as keys.

## Creating

```php
use function Noctud\Collection\{mapOf,mutableMapOf};
use function Noctud\Collection\{mapOfPairs,mutableMapOfPairs};

$immutable = mapOf(['a' => 1, 'b' => 2]); // ImmutableMap
$mutable = mutableMapOf(['x' => 10]); // MutableMap
$empty = mapOf();
$lazy = mapOf(fn() => loadConfig());
```

Performance and memory optimized variants (both are in **mutable**/**immutable** flavors):

```php
use function Noctud\Collection\stringMapOf;
use function Noctud\Collection\mutableIntMapOf;

$immutable = stringMapOf(['1' => 'one']); // ImmutableMap
$mutable = mutableIntMapOf([1 => 'one']); // MutableMap
```

::: tip StringMap / IntMap
They share the same interface and both come in mutable/immutable variants. Keys are enforced to be specific type. See [Type-Specific Maps](#type-specific-maps-stringmap-and-intmap) for more info.
:::

### Preserving Key Types

When constructing a map from a PHP array, PHP's array key casting rules apply **before** the map receives the data:

```php
$map = mapOf(['1' => 'a']); // ⚠️ key becomes int(1) due to PHP casting
```

Use `stringMapOf` or `mapOfPairs` to preserve or enforce the type you want:

```php
$map = stringMapOf(['1' => 'a']); // ✓ key is cast back to string("1")
$map = mapOfPairs([['1', 'a']]); // ✓ key stays string("1")
```

After construction, Map always preserves keys. Array-access writes on a mutable map keep the original type:

```php
$map = mutableMapOf();
$map['2'] = 'b'; // key is string("2"), not int
```

## Accessing Elements

```php
$map = mapOf(['name' => 'Rodney', 'age' => 38]);

$map['name']; // 'Rodney' - throws if missing
$map['name'] ?? null; // 'Rodney' - null if missing (via ?? operator)
$map->get('name'); // 'Rodney' - throws if missing
$map->getOrNull('name'); // 'Rodney' - null if missing
$map->getOrDefault('role', 'guest'); // 'guest'
$map->getOrCompute('role', fn() => fetchRole()); // computed value

isset($map['name']); // true - same as containsKey('name')
isset($map['role']); // false

// MutableMap only - get or compute + store
$mutableMap = mutableMapOf(['name' => 'Rodney']);
$mutableMap->getOrPut('role', fn() => 'guest'); // 'guest' - stored in map
$mutableMap->getOrPut('role', fn() => 'admin'); // 'guest' - already exists
```

## Type-Specific Maps: StringMap and IntMap

For string-only or int-only keys, memory-optimized variants are available:

```php
use function Noctud\Collection\stringMapOf;
use function Noctud\Collection\mutableStringMapOf;
use function Noctud\Collection\intMapOf;
use function Noctud\Collection\mutableIntMapOf;

$users = stringMapOf(['rodney' => 38, 'sheppard' => 40]);
$scores = intMapOf([1 => 100, 2 => 85, 3 => 92]);
```

::: tip Performance benefits
StringMap and IntMap use ~50% less memory than HashMap (single-array storage vs dual-array). They also benefit from zero-copy initialization via PHP's copy-on-write and have no key hashing overhead. The API is identical to HashMap — you can swap between them without code changes.
:::

These maps enforce key types at runtime. The mutable IntMap throws `InvalidKeyTypeException` for non-int keys. The mutable StringMap enforces string keys on `put()` but accepts PHP's existing array during construction (since PHP already casts numeric strings to int). The immutable variants widen instead, returning an `ImmutableHashMap` for a key of another type:

```php
$intMap = mutableIntMapOf();
$intMap->put('a', 'fail'); // throws InvalidKeyTypeException

$stringMap = mutableStringMapOf();
$stringMap->put(123, 'fail'); // throws InvalidKeyTypeException

$scores = intMapOf([1 => 100]);
$scores->put('a', 50); // ImmutableHashMap [1 => 100, 'a' => 50]
```

Transformation methods return immutable collections. Methods that preserve key types return the corresponding immutable type-specific map:

```php
$stringMap = mutableStringMapOf(['a' => 1, 'b' => 2]);

$stringMap->filter(fn($v) => $v > 1); // ImmutableMap<string, int> - keys unchanged, still string-optimized
$stringMap->mapKeys(fn($v, $k) => strlen($k)); // ImmutableMap<int, int> - keys are now int
```

Use `mapOf()` / `mutableMapOf()` when you need mixed key types (objects, float, bool) or when the key type is unknown. For most cases, HashMap works fine — StringMap/IntMap are there when you want to optimize.

## Views: Keys, Values, Entries

Every map exposes three live views — `$keys`, `$values`, and `$entries`. These are full collection objects with all methods from `Set<K>`, `Collection<V>`, and `Set<MapEntry<K,V>>` respectively.

```php
$map = mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

$map->keys; // Set<string> {'a', 'b', 'c'}
$map->values; // Collection<int> [1, 2, 3]
$map->entries; // Set<MapEntry<string, int>>
```

### Why views matter

`Map` itself does not have methods like `first()`, `random()`, or `min()` — because those operations are ambiguous on a key-value structure. Do you want the first key? The first value? The first entry? Views make this explicit:

```php
$map = mapOf(['x' => 10, 'y' => 20, 'z' => 30]);

$map->values->first(); // 10
$map->values->last(); // 30
$map->keys->random(); // random key, e.g. 'y'
$map->entries->first(); // MapEntry { key: 'x', value: 10 }

$map->values->min(); // 10
$map->values->max(); // 30
$map->values->sum(); // 60
$map->values->avg(); // 20.0

$map->keys->contains('x'); // true
$map->keys->sorted(); // ImmutableSet {'x', 'y', 'z'}
$map->values->filter(fn($v) => $v > 15); // ImmutableList [20, 30]
```

::: tip PhpStorm / IntelliJ users
Install the [Noctud plugin](https://plugins.jetbrains.com/plugin/30173-noctud) to get autocomplete for view methods directly on `$map->` — the plugin suggests `$map->values->first()`, `$map->keys->random()`, etc. without typing the full path.
:::

### Entries as MapEntry objects

Each entry in `$entries` is a `MapEntry<K,V>` with `->key` and `->value` properties:

```php
$map = mapOf(['name' => 'Rodney', 'role' => 'scientist']);

$entry = $map->entries->first(); // MapEntry
$entry->key; // 'name'
$entry->value; // 'Rodney'

// Find entry by condition
$map->entries->find(fn($e) => $e->value === 'scientist'); // MapEntry { key: 'role', value: 'scientist' }

// Transform entries
$map->entries->map(fn($e) => "$e->key=$e->value"); // Collection ['name=Rodney', 'role=scientist']
```

`MapEntry` implements `Hashable`, so set operations on entries work by comparing both key and value:

```php
$map1 = mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
$map2 = mapOf(['b' => 2, 'c' => 99, 'd' => 4]);

$map1->entries->intersect($map2->entries); // Set [MapEntry('b', 2)] — only exact matches
$map1->entries->union($map2->entries); // Set [MapEntry('a', 1), MapEntry('b', 2), MapEntry('c', 3), MapEntry('c', 99), MapEntry('d', 4)]
$map1->entries->subtract($map2->entries); // Set [MapEntry('a', 1), MapEntry('c', 3)]
```

### Live reference on mutable maps

Views are **live** — they reflect the current state of the map, not a snapshot. On a mutable map, mutations are visible through the views immediately:

```php
$map = mutableMapOf(['a' => 1, 'b' => 2]);
$keys = $map->keys;
$values = $map->values;

$keys->count(); // 2
$values->sum(); // 3

$map->put('c', 3);

$keys->count(); // 3 - reflects the new entry
$values->sum(); // 6 - includes the new value
$keys->contains('c'); // true

$map->remove('a');

$keys->count(); // 2
$values->toArray(); // [2, 3]
```

No need to re-access `$map->keys` after mutation — the existing reference stays in sync.

### Converting views to standalone collections

Use `toList()`, `toMutable()`, or `toArray()` to detach a view into an independent collection:

```php
$map = mutableMapOf(['a' => 1, 'b' => 2]);
$snapshot = $map->values->toArray(); // [1, 2] - plain array, not live

$map->put('c', 3);
// $snapshot is still [1, 2]
```

## Querying

```php
$map = mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

$map->containsKey('a'); // true
$map->containsValue(2); // true
$map->count(); // 3
$map->isEmpty(); // false
$map->isNotEmpty(); // true
```

## Quantifiers

```php
$map = mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

$map->all(fn($v, $k) => $v > 0); // true
$map->any(fn($v, $k) => $v === 3); // true
$map->none(fn($v, $k) => $v < 0); // true
```

::: tip Callback argument order
Map callbacks always receive `($value, $key)` — value first, key second. This matches the convention used throughout the library and the PHP ecosystem (`array_map`, `array_filter`, `array_walk`).
:::

## Transforming

All transformation methods return **immutable collections** — the original is never modified, and the result is always immutable regardless of whether the source is mutable or immutable.

```php
$map = mutableMapOf(['a' => 1, 'b' => 2, 'c' => 3]);

// Filter - returns ImmutableMap
$map->filter(fn($v, $k) => $v > 1); // ImmutableMap {'b' => 2, 'c' => 3}
$map->filterKeys(fn($k) => $k !== 'a'); // ImmutableMap {'b' => 2, 'c' => 3}
$map->filterValues(fn($v) => $v % 2 === 0); // ImmutableMap {'b' => 2}
$map->filterValuesNotNull(); // ImmutableMap without null values
$map->filterValuesInstanceOf(Dog::class); // ImmutableMap<string, Dog> - narrows the value type

// Map - returns ImmutableMap
$map->mapKeys(fn($v, $k) => strtoupper($k)); // ImmutableMap {'A' => 1, 'B' => 2, 'C' => 3}
$map->mapValues(fn($v, $k) => $v * 10); // ImmutableMap {'a' => 10, 'b' => 20, 'c' => 30}
$map->mapValuesNotNull(fn($v) => $v > 1 ? $v * 10 : null); // ImmutableMap {'b' => 20, 'c' => 30}

// Map entries to list
$map->map(fn($v, $k) => "$k=$v"); // ImmutableList ['a=1', 'b=2', 'c=3']

// MapNotNull - transform entries, drop nulls
$map->mapNotNull(fn($v, $k) => $v > 1 ? "$k=$v" : null); // ImmutableList ['b=2', 'c=3']

// FlatMap - transform each entry into an iterable, flatten into a list
$map->flatMap(fn($v, $k) => [$k, $v]); // ImmutableList ['a', 1, 'b', 2, 'c', 3]

// Flip - returns ImmutableMap
$map->flip(); // ImmutableMap {1 => 'a', 2 => 'b', 3 => 'c'}

```

::: tip flip() and duplicate values
`flip()` throws `ConversionException` when multiple keys share the same value, since the flipped map would lose entries. Use `KeyCollisionStrategy` to control this:

```php
$map = mapOf(['a' => 1, 'b' => 1, 'c' => 2]);

$map->flip(); // throws ConversionException
$map->flip(KeyCollisionStrategy::KeepFirst); // Map {1 => 'a', 2 => 'c'}
$map->flip(KeyCollisionStrategy::KeepLast); // Map {1 => 'b', 2 => 'c'}
```
:::

## Slicing

Slice a map by position or predicate. All methods return **immutable maps**.

```php
$map = mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

// By position
$map->takeFirst(3); // ImmutableMap {'a' => 1, 'b' => 2, 'c' => 3}
$map->takeLast(2); // ImmutableMap {'d' => 4, 'e' => 5}
$map->dropFirst(2); // ImmutableMap {'c' => 3, 'd' => 4, 'e' => 5}
$map->dropLast(3); // ImmutableMap {'a' => 1, 'b' => 2}

// By predicate - stops at first mismatch from start
$map->takeWhile(fn($v) => $v < 4); // ImmutableMap {'a' => 1, 'b' => 2, 'c' => 3}
$map->dropWhile(fn($v) => $v < 4); // ImmutableMap {'d' => 4, 'e' => 5}

// By predicate - stops at first mismatch from end
$map->takeLastWhile(fn($v) => $v > 3); // ImmutableMap {'d' => 4, 'e' => 5}
$map->dropLastWhile(fn($v) => $v > 3); // ImmutableMap {'a' => 1, 'b' => 2, 'c' => 3}
```

Defaults to `$n = 1`, so `takeFirst()` / `dropFirst()` operate on a single entry:

```php
$map->takeFirst(); // ImmutableMap {'a' => 1}
$map->dropFirst(); // ImmutableMap {'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5}
```

## Object Keys

Objects are hashed using `spl_object_id` by default. Only the same instance matches:

```php
$user = new User(id: 1, name: 'Ronon');

$map = mapOfPairs([[$user, 'data']]);
isset($map[$user]); // true - same instance
isset($map[clone $user]); // false - different instance
```

Implement `Hashable` for identity based on custom logic:

```php
class User implements \Noctud\Collection\Hashable
{
    public function identity(): string|int
    {
        return "user_$this->id";
    }
}

$map = mutableMapOf();
$map[$user] = 'data';
isset($map[clone $user]); // true - same identity
```

## Mutating (MutableMap)

```php
$map = mutableMapOf(['a' => 1]);

$map->put('b', 2); // {'a' => 1, 'b' => 2}
$map->putIfAbsent('b', 99); // no-op — 'b' already exists
$map->putIfAbsent('c', 3); // {'a' => 1, 'b' => 2, 'c' => 3}
$map->putFirst('z', 0); // {'z' => 0, 'a' => 1, 'b' => 2, 'c' => 3}
$map->putFirst('b', 20); // {'b' => 20, 'z' => 0, 'a' => 1, 'c' => 3} - moved to front, value updated
$map->putAll(['c' => 3, 'd' => 4]); // adds entries 'c' and 'd'
$map->putAllPairs([['e', 5]]); // adds entry 'e' => 5
$map->remove('a'); // removes key 'a'
$map->removeFirst(); // removes the first entry
$map->removeLast(); // removes the last entry
$map->removeIf(fn($v, $k) => $v > 3); // removes entries where value > 3
$map->removeIfKey(fn($k) => $k === 'b'); // removes entry 'b'
$map->removeIfValue(fn($v) => $v === 2); // removes entries with value 2
$map->removeNullValues(); // removes null-valued entries
$map->clear(); // empties the map
```

Array-access writes work on mutable maps:

```php
$map['key'] = 'value'; // same as put('key', 'value')
unset($map['key']); // same as remove('key')
```

Track changes with `tracked()`:

```php
$tracked = mutableMapOf(['a' => 1])->tracked();
$tracked->remove('missing')->changed; // false
$tracked->remove('a')->changed; // true
```

## Mutating (ImmutableMap)

Immutable methods return a new map:

```php
$map = mapOf(['a' => 1, 'b' => 2]);

$new = $map->put('c', 3); // Map {'a' => 1, 'b' => 2, 'c' => 3}
$new = $map->putIfAbsent('a', 99); // same map — 'a' already exists
$new = $map->putIfAbsent('c', 3); // Map {'a' => 1, 'b' => 2, 'c' => 3}
$new = $map->putFirst('b', 20); // Map {'b' => 20, 'a' => 1} - moved to front
$new = $map->remove('a'); // Map {'b' => 2}
$new = $map->removeFirst(); // Map {'b' => 2}
$new = $map->removeLast(); // Map {'a' => 1}
// $map is unchanged
```

Array-access writes (`$map['x'] = 1`) throw `UnsupportedOperationException` on immutable maps.

## Iteration

```php
$map->forEach(fn($v, $k) => print("$k: $v\n"));
$map->forEachKey(fn($k) => print("$k\n"));
$map->forEachValue(fn($v) => print("$v\n"));

foreach ($map as $key => $value) {
    echo "$key = $value\n";
}
```

All three return nothing. Their `onEach()`, `onEachKey()` and `onEachValue()` counterparts return the map, so you can chain them mid-pipeline:

```php
$result = $map
    ->filter(fn($v) => $v > 0)
    ->onEach(fn($v, $k) => logger()->info("$k: $v"))
    ->mapValues(fn($v) => $v * 2);
```

## Conversion

```php
$map = mapOf(['a' => 1, 'b' => 2]);

$map->toArray(); // ['a' => 1, 'b' => 2]
$map->toPairs(); // [['a', 1], ['b', 2]]
$map->toMutable(); // MutableMap (always a new copy)
$map->toImmutable(); // ImmutableMap
```

::: tip toArray() and key collisions
`toArray()` throws `ConversionException` when keys are objects or when PHP's type casting would cause collisions (e.g., string `"1"` and int `1`). This is intentional — silent data loss is a problem this library prevents.

```php
$map = mapOfPairs([['1', 'a'], [1, 'b']]); // string "1" and int 1 as separate keys

$map->toArray(); // throws ConversionException
$map->toArray(KeyCollisionStrategy::KeepFirst); // [1 => 'a']
$map->toArray(KeyCollisionStrategy::KeepLast); // [1 => 'b']
```

Collisions can only occur with **mixed types or floats**. A `Map<string, V>` with only string keys will never throw — PHP only casts strings that look exactly like integers (`"1"`, `"42"`) to int keys, and two distinct strings (e.g., `"1"` and `"01"`) always remain distinct. Same for `Map<int, V>`. However, `Map<float, V>` can collide because floats are truncated to int (e.g., `1.1` and `1.9` both become `1`). PHP 8.1+ emits a deprecation notice when a float key loses precision, and PHP 9 will throw a `TypeError`.
:::

Map implements `JsonSerializable`, so `json_encode()` works directly:

```php
json_encode($map); // {"a":1,"b":2}
```

::: warning json_encode() and non-scalar keys
`json_encode($map)` internally calls `toArray()`, so it follows the same rules. Maps with string or int keys serialize naturally. Maps with object keys or mixed scalar types that collide after PHP casting will throw `ConversionException`. For those cases, prepare the data manually:

```php
json_encode($map->toPairs()); // always works: [["key", "value"], ...]
json_encode($map->mapKeys(fn($v, $k) => $k->getId())->toArray()); // transform keys first
```

Maps created via `stringMapOf()` / `intMapOf()` are always safe to `json_encode()` — thanks to PHP 8.0's [saner numeric strings](https://wiki.php.net/rfc/saner-numeric-strings), string and int keys are natively JSON-compatible without collisions.
:::
