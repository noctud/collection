# Design Philosophy

Some API choices in this library might look unusual if you're used to other collection libraries. Here's why things are the way they are.

## Throw or Null

Every accessor that can fail has two variants — one throws, one returns `null`:

```php
$list = listOf([1, 2, 3]);

// Throwing — missing data is a bug
$list->first(); // 1
$list->single(); // throws — more than one element
$list[5]; // throws — index out of bounds
$list->get(5); // throws — index out of bounds

// Nullable — absence is expected
$list->firstOrNull(); // 1
$list->singleOrNull(); // null — more than one element
$list[5] ?? null; // null — index out of bounds
$list->getOrNull(5); // null — index out of bounds
```

The same pattern applies to predicate-based search — `find()` returns null, `expect()` throws:

```php
$list->find(fn($n) => $n > 10); // null — no match
$list->expect(fn($n) => $n > 10); // throws — no match
```

Array access `$map['a']` is strict — it throws on missing keys, just like `get()`. Use `??` for safe fallback:

```php
$map = mapOf(['a' => 1]);

$map['a']; // 1 — throws if missing
$map['a'] ?? null; // 1 — null if missing
$map->get('a'); // 1 — throws if missing
$map->getOrNull('a'); // 1 — null if missing
```

## Explicit Naming

Method names try to eliminate ambiguity, even if it means a few more characters.

**`removeElement` vs `remove`** — Collections (List, Set) use `removeElement($element)` to remove by value. Maps use `remove($key)` to remove by key. If both used `remove()`, switching between a Set and a Map during refactoring could silently change what gets removed — by value vs. by key — with no warning.

**No redundant suffixes** — `add()` always appends, so there's no `addLast()`. The `First`/`Last` suffix only shows up when there's actual ambiguity: `removeFirst()` vs `removeLast()` both remove without arguments, so the suffix clarifies which end.

**Consistent parameter names** — Collection, List, and Set methods use `$element`. Maps use `$key` and `$value`. You can tell at a glance whether you're working with a positional collection or a key-value structure.

**Value-first callbacks** — All callbacks receive `($value, $key)`, matching PHP's `array_filter` and `array_walk`. When you only care about the key, dedicated methods like `filterKeys()` and `removeIfKey()` exist so you don't end up writing `fn($v, $k) => condition($k)`.

::: tip Int index in every callback
On Collection, List, and Set, the second callback argument is always `int` — the iteration index. For Lists this is the actual index, for Sets it's just the iterator position created on the fly during traversal. This means `filter(fn($v, $i) => $i < 5)` works on any collection type without needing separate `filterIndexed()` methods. Maps use the actual key as the second argument instead.
:::

## Immutable by Default

Factory functions return immutable collections:

```php
$list = listOf([1, 2, 3]); // ImmutableList
$set = setOf(['a', 'b']); // ImmutableSet
$map = mapOf(['x' => 1]); // ImmutableMap
```

Transformations (`filter`, `map`, `sorted`, `reversed`, etc.) also return immutable, even when the source is mutable:

```php
$mutable = mutableListOf([3, 1, 2]);
$filtered = $mutable->filter(fn($n) => $n > 1); // ImmutableList
```

Mutating methods on immutable collections are marked `#[NoDiscard]`, so forgetting to use the return value is a warning:

```php
$list = listOf([1, 2, 3]);
$list->add(4); // warning — return value discarded
$list = $list->add(4); // correct
```

Mutable is opt-in — `mutableListOf()`, `mutableSetOf()`, `mutableMapOf()`, or `->toMutable()`.

## Method Chaining

Every mutating method returns the collection — mutable methods return `$this`, immutable methods return a new instance:

```php
$set = mutableSetOf([1, 2, 3]);
$set->add(4)->add(5)->removeElement(1)->sort();

$list = listOf([1, 2, 3]);
$list = $list->add(4)->add(5)->removeElement(1)->sorted();
```

In Kotlin and Java, mutable mutations return `void` or a boolean — `add()` returns `true`/`false`, `remove()` returns `true`/`false`. That makes chaining impossible, so every operation needs its own statement. Here, mutations always return the collection, so multi-step operations read as a single expression.

When you do need the result of a mutation, call `tracked()` first. The tracked wrapper still chains, but each mutation's return value exposes a `$changed` property:

```php
$tracked = mutableSetOf([1, 2, 3])->tracked();

$tracked->add(4)->changed; // true — element was added
$tracked->add(4)->changed; // false — already present

// Still chainable when you don't need the result
$tracked->removeElement(1)->removeElement(2)->add(10);
```

This gives you both — fluent chaining by default, and per-operation results when you ask for them.

## Gradual Mutability

Mutability isn't binary — it's a spectrum of four levels:

```
Collection (read-only)        → query, iterate, transform
├── ImmutableCollection       → mutation returns new instances
└── WritableCollection        → add, remove, clear
    └── MutableCollection     → addFirst, sort, reverse, shuffle
```

The split between `Writable` and `Mutable` is the interesting one. `WritableCollection` gives you basic add/remove — enough to build up and tear down a collection. `MutableCollection` adds positional control: prepending with `addFirst()`, in-place `sort()`, `reverse()`, `shuffle()`.

Why not put everything in one interface? Because not every writable collection can meaningfully support ordering operations. Consider a third-party library like Doctrine that wants to back a collection with a database query. It can reasonably implement `add()` and `remove()` — those map to `INSERT` and `DELETE`. But in-place `sort()` or `addFirst()` on a database-backed collection doesn't make sense. With the `Writable` layer, Doctrine can implement `WritableCollection` without being forced to provide `sort()` or `shuffle()` stubs that throw `UnsupportedOperationException`.

```php
// Doctrine can implement this — just add/remove
function syncTags(WritableSet $tags, Tag $freshTag): void {
    $tags->removeIf(fn($tag) => $tag->isExpired());
    $tags->add($freshTag);
}

// Accepts any WritableSet — library's MutableSet, Doctrine's DB-backed set, etc.
```

The same applies to maps — `WritableMap` has `put()` and `remove()`, while `MutableMap` adds `putFirst()`, `sortByKey()`, `reverse()`, and `shuffle()`.

This way, type hints tell you exactly what operations a function needs. If it takes `WritableCollection`, it won't try to sort your data. If it takes `MutableCollection`, it might.

## Interfaces, Not Classes

Every public type is an interface — `Collection`, `ListInterface`, `Set`, `Map`, and all mutable/immutable variants. Factory functions return interfaces, not concrete classes:

```php
function getUsers(): ImmutableList { ... }
function getConfig(): ImmutableMap { ... }
```

The library ships default implementations backed by internal stores and logic traits, but any code can provide its own implementation. You depend on the contract, not on a specific class.

## Views, Not Copies

Map's `$keys`, `$values`, and `$entries` are live read-only views that share memory with the map — not methods that return copies:

```php
$map = mutableMapOf(['a' => 1, 'b' => 2, 'c' => 3]);

$map->keys; // Set<string> — live view
$map->values; // Collection<int> — live view
$map->entries; // Set<MapEntry<string, int>> — live view
```

Views are full collection interfaces, so you can query and transform them directly:

```php
$map->keys->filter(fn($k) => strlen($k) > 1);
$map->values->sum();
$map->entries->find(fn($e) => $e->value > 2);
```

This removes the need for duplicated methods on Map. There's no `randomKey()`, `firstValue()`, `sumValues()` — just access the view and call the method. The only exceptions are `containsKey()` and `containsValue()`, which exist on Map directly because `$map->keys->contains()` gets old fast.

## Minimal Method Overloading

Methods do one thing. There's no "pass a predicate to change behavior" pattern.

```php
$list = listOf([1, 2, 3, 4, 5]);

$list->first(); // first element — 1
$list->find(fn($n) => $n > 3); // first matching element — 4

$list->count(); // total count — 5
$list->countWhere(fn($n) => $n > 3); // matching count — 2
```

In many libraries, `first()` doubles as a search method when you pass a callback. Here, `first()` always returns the first element and `find()` always searches. No guessing whether a method accepts a predicate — if you want to search, there's a method named for that.

The one exception is optional selectors — methods like `sum(?Closure $selector)`, `min(?Closure $selector)`, or `sortedByKey(?Closure $selector)`. Without the selector they operate on the raw value, with it they extract a comparable value first. The method's purpose doesn't change (sum still sums, sort still sorts), the selector just controls *what* gets summed or sorted. Splitting these into `sum()` / `sumBy()` would double the method count for little clarity.

## No Hidden Materialization

A [Sequence](./sequence) promises to process elements one at a time, without loading the source into memory. Every intermediate operation keeps that promise — it belongs on `Sequence` only if it can yield its first element before it has read the whole source.

That rules out `sorted()`, `reversed()`, `shuffled()` and `takeLast()`. Each of them has to read the whole source before it can yield anything, so on a sequence they would be lazy in signature only. Kotlin ships some of them anyway; here, the materialization is spelled out in your code:

```php
$sequence->toList()->sorted(); // the cost is right there
```

A few more collection methods — `dropLast()`, `random()` and the set operations — are left out as well. They are clearer on a materialized collection, and `toList()` or `toSet()` is one call away.

The same rule decides which interfaces `Sequence` does **not** implement:

- **No `Countable`** — `count($sequence)` reads like a cheap property lookup, but a sequence has to drain the source to answer. The explicit `$sequence->count()` method exists, while the native `count()` stays a `TypeError`.
- **No `JsonSerializable`** — `json_encode()` would quietly load everything into memory. Call `toList()` or `toArray()` first.

Terminal operations that do need everything — `toList()`, `groupBy()`, `partition()` — are fine: they end the pipeline and hand back an immutable collection, so nothing pretends to be lazy.

This is also why `forEach()` returns `void`. On a sequence, it is the terminal operation that runs the pipeline — returning the sequence would invite a second pass, which a single-pass source can't give. `onEach()` is the chainable variant, on sequences and collections alike.

## Under the Hood

Each collection delegates to an internal store (`ArrayIndexStore`, `HashElementStore`, `HashKeyValueStore`, etc.) that handles data storage. This is how `StringMap` and `IntMap` can use optimized stores while exposing the same `Map` interface.

Transformation logic lives in reusable operation classes (`FilterOperation`, `MapKeyValueOperation`, `ChunkOperation`, etc.) shared across all collection types. Logic traits (`CollectionLogic`, `MapLogic`, etc.) wire operations to stores, so adding a new collection variant doesn't require much code.

Most operations yield their results through generators. Collections collect each step into a new store, while `SequenceLogic` chains the same generators — so sequences reuse the operations without materializing anything.

`toMutable()`, `toImmutable()`, and factory conversions use copy-on-write — memory is only duplicated when either side is actually modified. Lazy initialization uses PHP 8.4 lazy objects, so materialization is deferred until first access with no wrapper overhead.
