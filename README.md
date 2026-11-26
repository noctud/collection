# Noctud Collection

[![Docs](https://img.shields.io/badge/docs-noctud.dev-8A2BE2)](https://noctud.dev/collection/getting-started)
[![codecov](https://codecov.io/gh/noctud/collection/branch/0.2.x/graph/badge.svg)](https://codecov.io/gh/noctud/collection)
[![Latest Stable Version](https://img.shields.io/packagist/v/noctud/collection.svg?color=blue)](https://packagist.org/packages/noctud/collection)
![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)
[![Discord](https://img.shields.io/badge/discord-join-5865F2?logo=discord&logoColor=white)](https://discord.gg/jS3fKe6vW9)

Type-safe List, Set and Map collections for PHP 8.4+. Each comes in a mutable and an immutable variant, maps keep their keys exactly as given (`'1'` stays a string, objects work as keys), and static analyzers follow the element types through a whole chain of calls.

The API follows Kotlin's collections: factory functions, the mutable/immutable split, the `OrNull` methods and most of the method names.

```shell
composer require noctud/collection
```

The 0.x doesn't mean unstable. Each `0.MINOR` series is a stable major version, so `0.2.x` is what other projects would call `2.x`. With the default `^0.2` constraint, updates never break your code: `0.1.x` had nine releases with no breaking changes, and `0.2.x` follows the same rules. See [Versioning](https://noctud.dev/collection/versioning).

## Types

```text
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

Every type is an interface, and the [factory functions](https://noctud.dev/collection/api/functions) in the `Noctud\Collection` namespace return these interfaces, not concrete classes. The [full hierarchy](https://noctud.dev/collection/getting-started#architecture) also has Writable interfaces for third-party implementations.

```php
setOf(['a', 'b']); // ImmutableSet<string>
mutableSetOf(['a', 'b']); // MutableSet<string>

listOf(['a', 'b']); // ImmutableList<string>
mutableListOf(['a', 'b']); // MutableList<string>

mapOf(['a' => 1, 'b' => 2]); // ImmutableMap<string, int>
mutableMapOf(['a' => 1, 'b' => 2]); // MutableMap<string, int>
```

## Accessing elements

Array access is strict, so a missing key or index throws, the same as `get()`. Use `??` or an `OrNull` method when absence is expected.

```php
$list[0]; // throws if missing, get()
$list[0] ?? null; // null if missing
$list->getOrNull(0); // null if missing
$list->firstOrNull(); // null if empty

$map['key']; // throws if missing, get()
$map['key'] ?? null; // null if missing
$map->getOrNull('key'); // null if missing
$map->values->first(); // throws if empty
```

Sets have no array access by design, only `contains()`.

Every map exposes live, read-only [`$keys`, `$values` and `$entries`](https://noctud.dev/collection/map#views) views. They are real `Set` and `Collection` objects backed by the map's own store, so a change to the map shows up in them right away.

```php
$map = mapOf(['alice' => 28, 'bob' => 35, 'carol' => 22]);

$map->values->min(); // 22
$map->keys->filter(fn($k) => strlen($k) > 3); // Set {'alice', 'carol'}
$map->entries->first(); // MapEntry { key: 'alice', value: 28 }
```

## Transforming

`filter()`, `map()`, `flatMap()`, `zip()`, `partition()` and the other transformations always return a new immutable collection, whether the source is mutable or not. Unlike `array_filter()`, a filtered List is reindexed, so there are no gaps to close with `array_values()`.

```php
$set->filter(fn($el) => strlen($el->property) > 3); // new Set<E>
$map->filter(fn($v, $k) => strlen($k->property) > 3); // new Map<K,V>
$map->filterValuesNotNull(); // new Map<K,V> where V is not null
$map->values->filter(fn($v) => $v > 10); // new Collection<V>
```

## Sorting

Every Collection and Map keeps its order, so [sorting](https://noctud.dev/collection/sorting) works everywhere. `sorted*` returns a new collection and `sort*` sorts in place (mutable only). `*By` takes a selector, `*With` takes a comparator, and `Desc` reverses the order.

```php
// Basic
$list->sort(); // also sortDesc()
$map->sortByKey(); // also sortByValue()

// Selector
$list->sortBy(fn ($v) => $v->score);
$map->sortByKeyDesc(fn ($k) => strlen($k));

// Comparator, for the cases a selector can't express
$list->sortWith(fn ($a, $b) => $b->score <=> $a->score);
$map->sortWithKey(fn ($a, $b) => $a <=> $b); // also sortWithValue()
$map->sortWith(fn (MapEntry $a, MapEntry $b) => $a->value <=> $b->value);
```

## Mutable and immutable

Mutating methods return `$this` on a mutable collection and a new instance on an immutable one. Both variants share the same API, and the immutable methods are marked `#[NoDiscard]`, so PHP 8.5 warns when a result is thrown away.

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

A mutable collection keeps its element type, so PHPStan reports an element of the wrong type. An immutable one may widen it, since it returns a new instance anyway. See [Mutability](https://noctud.dev/collection/mutability).

```php
$map = mutableMapOf(['a' => 1]); // MutableMap<string, int>
$map->put('b', 'wrong'); // PHPStan error: string is not int

$map = mapOf(['a' => 1]); // ImmutableMap<string, int>
$new = $map->put('b', 'text'); // ImmutableMap<string, int|string>
```

[`tracked()`](https://noctud.dev/collection/mutability#change-tracking) wraps a mutable collection to report whether a mutation changed anything. The `$changed` flag is on the value each mutation returns, not on the wrapper itself.

```php
$map = mutableMapOf(['a' => 'b']);
if ($map->tracked()->remove('a')->changed) {
    // runs only if 'a' was actually removed
}
```

## Keys keep their type

A map stores every key as given, so the only place to be careful is construction, because PHP casts an array key like `'1'` to `int(1)` before the map ever sees it. Pairs, `stringMapOf()` and generators avoid that. See [Preserving key types](https://noctud.dev/collection/map#preserving-key-types).

```php
$map = mutableMapOf(['1' => 'a']); // key '1' is cast to int(1) before the map is created
$map = mutableMapOfPairs([['1', 'a']]); // key '1' stays a string
$map['2'] = 'b'; // key '2' stays a string

// Only string keys (int keys are accepted at construction and become strings)
$map = stringMapOf(['1' => 'a', 2 => 'b']); // keys '1' and '2' are strings

// From a generator
$map = mapOf((function() {
    yield '1' => 'a'; // key '1' stays a string
})());
```

Objects work as keys too. By default they are hashed with `spl_object_id()`, so only the same instance matches. Implement `Hashable` to define identity yourself. See [Objects as keys](https://noctud.dev/collection/map#objects-as-keys).

```php
$map = mapOfPairs([[$user, 'data']]);
isset($map[$user]); // true, same instance
isset($map[clone $user]); // false, different instance

class User implements \Noctud\Collection\Hashable {
    public function identity(): string|int {
        return "user_$this->id";
    }
}

$map = mutableMapOf();
$map[$user] = 'cacheData';
isset($map[clone $user]); // true, same user ID
```

## Lazy initialization

Pass a closure instead of data and it runs on first access. The internal store is a PHP 8.4 [lazy object](https://www.php.net/manual/en/language.oop5.lazy-objects.php), a ghost that is materialized only when something reads it. See [Lazy initialization](https://noctud.dev/collection/lazy-init).

```php
// The query runs only if $users is actually read
$template->users = listOf(fn() => $repository->getAllUsers());

$lazyMap = mapOf(fn () => ['a' => 1]); // callback returning an array
$lazyMap = mapOf(fn () => $generator); // callback returning a Generator

$lazyMap->values; // still lazy, no code executed yet
$lazyMap->count(); // first read, executes the callback and materializes the map
```

Lazily initialized collections behave identically to regular ones; there is no way to tell from outside. Always pass closures, not Generator objects directly.

## Sequences

Collection transformations are eager, so every step builds a new collection. A `Sequence` runs the chain element by element instead and stops as soon as the result is known, which suits large, streamed or infinite data. See [Sequence](https://noctud.dev/collection/sequence).

```php
// profile() runs only once, for the first active user
$users->asSequence()
    ->filter(fn($u) => $u->isActive())
    ->map(fn($u) => $api->profile($u))
    ->first();

// readLines() is your own generator, reading stops after 10 errors
sequenceOf(fn() => readLines('app.log'))
    ->filter(fn($line) => str_contains($line, 'ERROR'))
    ->takeFirst(10)
    ->toList();

// Infinite, but only the first 10 elements are ever computed
generateSequence(1, fn($n) => $n * 2)->takeFirst(10)->toList();
```

A sequence stores nothing, so each terminal operation (`first()`, `toList()`, ...) runs the chain from the source again. A closure source is called again, but a `Generator` can be iterated only once.

## Extending

Every type you work with is an interface, `MapEntry` included, and the logic lives in traits, so any class can become a collection. Custom stores and database-backed collections are covered in the [Extending guide](https://noctud.dev/collection/extending).

## Performance

Type safety and correct keys come first. Lists and Sets add little over native arrays, but the generic `mapOf()` keeps two arrays so that any key type survives, which costs memory and time. When keys are only strings or only integers, use the specialized maps, which keep a single array, skip key hashing, check key types at runtime and use about 50% less memory.

```php
$users = stringMapOf(['alice' => 28, 'bob' => 35]); // or mutableStringMapOf()
$scores = intMapOf([1 => 100, 2 => 85, 3 => 92]); // or mutableIntMapOf()
```

`toMutable()` and `toImmutable()` share the data until either side is modified, so switching between the variants is nearly free.

## Static analysis

The generics are written for PHPStan, and `tests/Type` asserts the types it infers. Psalm reads the same annotations but isn't tested here. PhpStorm has known limitations with generics inference.

## Documentation

- [Getting started](https://noctud.dev/collection/getting-started): installation, architecture, basic usage
- [List](https://noctud.dev/collection/list), [Set](https://noctud.dev/collection/set) and [Map](https://noctud.dev/collection/map): type guides with examples
- [Mutability](https://noctud.dev/collection/mutability): mutable vs immutable, change tracking, copy-on-write
- [Sorting](https://noctud.dev/collection/sorting): full sorting reference with a quick-reference table
- [Lazy initialization](https://noctud.dev/collection/lazy-init): deferred initialization
- [Sequence](https://noctud.dev/collection/sequence): lazy, element-by-element pipelines
- [Extending](https://noctud.dev/collection/extending): custom implementations, stores, traits
- [API reference](https://noctud.dev/collection/api/collection): all method signatures
- [Versioning](https://noctud.dev/collection/versioning): release rules, backward compatibility, supported versions
