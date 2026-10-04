# Noctud Collection

[![Docs](https://img.shields.io/badge/docs-noctud.dev-8A2BE2)](https://noctud.dev/collection/getting-started)
[![codecov](https://codecov.io/gh/noctud/collection/branch/0.1.x/graph/badge.svg)](https://codecov.io/gh/noctud/collection)
[![Latest Stable Version](https://img.shields.io/packagist/v/noctud/collection.svg)](https://packagist.org/packages/noctud/collection)
![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)
[![Discord](https://img.shields.io/badge/discord-join-5865F2?logo=discord&logoColor=white)](https://discord.gg/jS3fKe6vW9)

Type-safe, mutable/immutable, sortable and key-preserving List/Map/Set collections for **PHP 8.4+**.

```shell
composer require noctud/collection
```

## ✨ Features
- **Type-safe**: Full generics support. Static analyzers understand every element type through the chain.
- **Key-preserving**: Map keys like `float`, `bool` or `"1"` retain their original types. No silent type casting.
- **Object keys**: Use objects as map keys out of the box. Implement `Hashable` for custom identity semantics.
- **Mutable & Immutable**: Choose the right variant. Immutable methods are marked with `#[NoDiscard]`.
- **Lazy Init**: Construct collections from closures. Uses PHP 8.4 lazy objects — materialized only on first access.
- **Lazy Sequences**: Process large or infinite data element by element. Chains stop as soon as the result is known.
- **Interface-driven**: Every type is an interface. Factory functions return contracts, not concrete classes.
- **Expressive**: Rich set of higher-order functions — map, filter, sorted, flatMap, groupBy, partition, and more.
- **Chainable**: Mutating methods return a collection — read result like `$set->tracked()->add('a')->changed`.
- **Strict**: Choose between throwing and nullable methods (`get`/`getOrNull`, `first`/`firstOrNull`, etc.)
- **Inspired by Kotlin**: Factory functions, mutable/immutable split, `OrNull` conventions, and namings.

## 🗺️ Architecture
```bash
Collection<E>               → Ordered elements, read-only
├── List<E>                 → Indexed, array access
│   ├── MutableList<E>      → Mutating methods (write & sort)
│   └── ImmutableList<E>    → Mutation returns new with #[NoDiscard]
└── Set<E>                  → Unique values, no array access
    ├── MutableSet<E>       → Mutating methods (write & sort)
    └── ImmutableSet<E>     → Mutation returns new with #[NoDiscard]

Map<K,V>                    → Ordered key-value pairs, array access
├── MutableMap<K,V>         → Mutating methods (write & sort)
└── ImmutableMap<K,V>       → Mutation returns new with #[NoDiscard]

Sequence<E>                 → Lazy pipeline, not a collection
```
Full architecture is [shown in docs](https://noctud.dev/collection/getting-started#architecture), there are also Writable interfaces for easy third party implementations.

### 🏗️ Constructing
Use [factory functions](https://noctud.dev/collection/api/functions) from namespace `Noctud\Collection`.
```php
setOf(['a', 'b']); // ImmutableSet<string>
mutableSetOf(['a', 'b']); // MutableSet<string>

listOf(['a', 'b']); // ImmutableList<string>
mutableListOf(['a', 'b']); // MutableList<string>

mapOf(['a' => 1, 'b' => 2]); // ImmutableMap<string, int>
mutableMapOf(['a' => 1, 'b' => 2]); // MutableMap<string, int>
```
Use `stringMapOf`/`mutableStringMapOf` and `intMapOf`/`mutableIntMapOf` for better
performance and ~50% less memory — they use single-array storage and enforce key types at runtime.

### 📖 Accessing
Array access is strict by default — throws on missing keys/indices. Use `??` for safe fallback.
```php
$list[0]; // throws if missing, get()
$list[0] ?? null; // null if missing
$list->getOrNull(0); // null if missing
$list->firstOrNull(); // null if empty
```
```php
$map['key']; // throws if missing, get()
$map['key'] ?? null; // null if missing
$map->getOrNull('key'); // null if missing
$map->values->first(); // throws if empty
```
Sets support only the `contains` method, they have no array access by design.

### 🌪️ Filtering & transformations
All transformation methods (`filter`, `map`, `flatMap`, `zip`, `partition`, ...) always return a new **immutable** collection, regardless of whether the source is mutable or immutable. Unlike `array_filter`, Lists are always reindexed — no gaps, no need for `array_values()`.
```php
$set->filter(fn($el) => strlen($el->property) > 3); // new Set<E>
$map->filter(fn($v, $k) => strlen($k->property) > 3); // new Map<K,V>
$map->filterValuesNotNull(); // new Map<K,V> where V is not null
$map->values->filter(fn($v) => $v > 10); // new Collection<V>
```

### 📊 Sorting
Every Collection and Map is sequentially ordered, so [sorting](https://noctud.dev/collection/sorting) is supported everywhere.
- `sorted*` returns a new collection, `sort*` sorts in place (Mutable only).
- `*By` takes a selector, `*With` takes a comparator. Add `Desc` for descending.

```php
// Basic
$list->sort(); // also sortDesc()
$map->sortByKey(); // also sortByValue()

// Selector examples
$list->sortBy(fn ($v) => $v->score);
$map->sortByKeyDesc(fn ($k) => strlen($k));

// Comparator examples (advanced use cases)
$list->sortWith(fn ($a, $b) => $b->score <=> $a->score);
$map->sortWithKey(fn ($a, $b) => $a <=> $b); // also sortWithValue()
$map->sortWith(fn (MapEntry $a, MapEntry $b) => $a->value <=> $b->value);
```

### 👁️ Map views
Every Map exposes live read-only [`$keys`, `$values`, and `$entries`](https://noctud.dev/collection/map#views) views. These are real `Set` and `Collection` objects backed by the same underlying store — mutations to the map are immediately visible through views and vice versa.
```php
$map = mapOf(['alice' => 28, 'bob' => 35, 'carol' => 22]);

$map->values->min(); // 22
$map->keys->filter(fn($k) => strlen($k) > 3); // Set {'alice', 'carol'}
$map->entries->first(); // MapEntry { key: 'alice', value: 28 }
```

### ✔️ Quantifiers
Check if all/any or none of the elements match the predicate.
```php
$set->all(fn($v) => strlen($v->property) > 3); // true|false
$map->any(fn($v, $k) => strlen($k->property) > 3); // true|false
$map->values->none(fn($v) => $v->isActive); // true|false
```

### ➰ Iterating
All collections are traversable.
```php
$set->forEach(fn($v) => print("$v->property\n"));
$map->forEach(fn($v, $k) => print("$k = $v\n"));

// Keys for Sets are generated on the fly (0, 1, 2, ...)
foreach ($collection as $k => $v) {
    print("$k = $v\n");
}
```

### ⛓️ Chainable
Mutating methods return `$this` (Mutable) or a new instance (Immutable). Both share the same API, but immutable methods are marked with `#[NoDiscard]` to prevent accidental misuse.

```php
$new = $map->put('b', 2)
    ->remove('a')
    ->filter(fn($v, $k) => $v > 1)
    ->mapValues(fn($v, $k) => $v * 2)
    ->sortedByKey();

$mutableSet->clear()
    ->addAll(['a', 'b', 'c', null])
    ->removeIf(fn($v) => $v === null);
```

Method [`tracked()`](https://noctud.dev/collection/mutability#change-tracking) wraps a mutable collection in a proxy that tracks changes. The `$changed` flag is available on the return value of each mutation method, not on the wrapper itself.
```php
$map = mutableMapOf(['a' => 'b']);
if ($map->tracked()->remove('a')->changed) {
    // do something only if 'a' was actually removed
}
```

### 🛡️ [Type safety](https://noctud.dev/collection/mutability)
Mutable collections enforce strict typing — PHPStan warns if you try to add elements of incompatible types.
Immutable collections allow type widening since they return a new instance with potentially different types.
```php
// Mutable — strict, PHPStan warns on type mismatch
$map = mutableMapOf(['a' => 1]); // MutableMap<string, int>
$map->put('b', 'wrong'); // ❌ PHPStan error: string is not int

// Immutable — widening allowed, returns new instance
$map = mapOf(['a' => 1]); // ImmutableMap<string, int>
$new = $map->put('b', 'text'); // ✅ ImmutableMap<string, int|string>
```

### 🔑 [Preserving key types](https://noctud.dev/collection/map#preserving-key-types)
```php
$map = mutableMapOf(['1' => 'a']); // ❌ Key '1' will be cast to int(1) before the map is created
$map = mutableMapOfPairs([['1', 'a']]); // ✅ Key '1' will stay as a string
$map['2'] = 'b'; // ✅ Key '2' will stay as string

// Enforce string keys (int are only allowed at construction time)
$map = stringMapOf(['1' => 'a', 2 => 'b']); // ✅ Keys '1' and '2' will be strings

// Constructing from a generator
$map = mapOf((function() {
    yield '1' => 'a'; // ✅ Key '1' will stay as a string
})());
```
Map will always preserve original keys, you have to only worry about constructing the map.

### 💤 [Lazy Initialization](https://noctud.dev/collection/lazy-init)
Construct from a closure — the callback executes only on first access. Under the hood, lazy initialization uses PHP 8.4's [Lazy Objects](https://www.php.net/manual/en/language.oop5.lazy-objects.php) — the internal store is a ghost proxy materialized only when first accessed.
```php
// The query runs only if $users is actually read
$template->users = listOf(fn() => $repository->getAllUsers());

$lazyMap = mapOf(fn () => ['a' => 1]); // ✅ Good, callback returning an array
$lazyMap = mapOf(fn () => $generator); // ✅ Good, callback returning Generator

$lazyMap->values; // still lazy, no code executed yet
$lazyMap->count(); // first read - executes the callback, materializes the map
```
Lazily initialized collections behave identically to regular ones — there is no way to tell from outside. Always pass closures, not Generator objects directly.

### 🌊 [Sequences](https://noctud.dev/collection/sequence)
Collection transformations are eager — every step builds a new collection. A `Sequence` runs the chain element by element instead, and stops as soon as the result is known. Use it for large, streamed or infinite data.
```php
// profile() runs only once - for the first active user
$users->asSequence()
    ->filter(fn($u) => $u->isActive())
    ->map(fn($u) => $api->profile($u))
    ->first();

// readLines() is your own generator - stops after 10 errors
sequenceOf(fn() => readLines('app.log'))
    ->filter(fn($line) => str_contains($line, 'ERROR'))
    ->takeFirst(10)
    ->toList();

// Infinite, but only the first 10 elements are ever computed
generateSequence(1, fn($n) => $n * 2)->takeFirst(10)->toList();
```
A sequence stores nothing, so each terminal operation (`first()`, `toList()`, …) runs the chain from the source again: a closure source is called again, a `Generator` can be iterated only once.

### ㊙️ [Objects as keys](https://noctud.dev/collection/map#objects-as-keys)

Use objects as map keys out of the box. By default, objects are hashed using `spl_object_id`.

```php
$map = mapOfPairs([[$user, 'data']]);
isset($map[$user]); // ✅ True, same object instance
isset($map[clone $user]); // ❌ False, different instance
```

Implement `Hashable` for custom identity semantics:

```php
class User implements \Noctud\Collection\Hashable {
    public function identity(): string|int {
        return "user_$this->id";
    }
}

$map = mutableMapOf();
$map[$user] = 'cacheData';
isset($map[clone $user]); // ✅ True, same user ID
```

### 🧩 Extending
Every type you interact with is an interface — `ImmutableList`, `MutableMap`, `Set`, even `MapEntry`. Logic is encapsulated in traits, so you can turn any class into a collection.

For custom stores, database-backed collections, and more, see the [Extending guide](https://noctud.dev/collection/extending).

### 🚀 Performance

This library prioritizes type safety and correctness. Lists and Sets have minimal overhead compared to native arrays. The generic `mapOf()` uses dual-array storage to preserve any key type, which adds memory and performance overhead.

When keys are exclusively strings or integers, use the optimized variants for maximum performance:
```php
$users = stringMapOf(['alice' => 28, 'bob' => 35]); // or mutableStringMapOf()
$scores = intMapOf([1 => 100, 2 => 85, 3 => 92]); // or mutableIntMapOf()
```
These use single-array storage, skip key hashing entirely, and use ~50% less memory than `mapOf()`.

Converting between mutable and immutable via `toMutable()`/`toImmutable()` uses copy-on-write — the data is shared until either side is modified, making variant switching virtually free.

### 🔎 Static analysis

Generics are fully supported by PHPStan and Psalm. PhpStorm has known limitations with generics inference.

## 📚 Documentation

- [Getting started](https://noctud.dev/collection/getting-started) — Installation, architecture, basic usage
- [List](https://noctud.dev/collection/list) / [Set](https://noctud.dev/collection/set) / [Map](https://noctud.dev/collection/map) — Type guides with examples
- [Mutability](https://noctud.dev/collection/mutability) — Mutable vs immutable, change tracking, copy-on-write
- [Sorting](https://noctud.dev/collection/sorting) — Full sorting reference with quick-reference table
- [Lazy initialization](https://noctud.dev/collection/lazy-init) — Deferred initialization
- [Sequence](https://noctud.dev/collection/sequence) — Lazy, element-by-element pipelines
- [Extending](https://noctud.dev/collection/extending) — Custom implementations, stores, traits
- [API reference](https://noctud.dev/collection/api/collection) — All method signatures
