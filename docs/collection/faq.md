# FAQ

## What's different from Java/Kotlin collections?

- The base interfaces `Set`, `Map`, and `ListInterface` are strictly **read-only**. There are no "optional operations" or `UnsupportedOperationException` surprises.
- `Map->keys`, `Map->values`, and `Map->entries` return **read-only** live views.
- `MapEntry` is always a snapshot of a key-value pair, not a live reference to the map.
- Mutable and immutable collections have **separate interfaces** — `MutableList` for in-place mutation, `ImmutableList` for persistent mutation that returns new instances.
- Iterating over a collection operates on a snapshot of its state. Mutating the collection during iteration does not affect the current loop. There is no `ConcurrentModificationException`, same as PHP arrays.
- All mutating methods return the collection instance for chaining. Mutable collections provide a `tracked()` method that returns a wrapper whose mutation methods expose a `$changed` property indicating whether the operation actually modified the data.
- **Every collection is ordered.** There is no `HashSet` vs `LinkedHashSet` distinction — all collections preserve insertion order.
- **Callback parameter order follows PHP convention** — value first, key/index second (`$value, $key`), same as `array_filter`, `array_walk`, and every major PHP library. Specialized methods like `removeIfKey(fn($k) => ...)` and `filterKeys(fn($k) => ...)` exist so you never have to write `fn($v, $k) => condition($k)`.
- **`Sequence` only has intermediate operations that stay lazy.** Kotlin's `Sequence` also offers `sorted()` and a few others that read the whole source before yielding anything. Here you write `toList()->sorted()` instead — see [No Hidden Materialization](./design#no-hidden-materialization).

```
Collection<E>                → Ordered elements, read-only
├── ImmutableCollection<E>   → Mutation returns new (#[NoDiscard])
├── WritableCollection<E>    → Writable methods (add/remove)
│   └── MutableCollection<E> → Positional control (addFirst/sort)
├── Set<E>                   → Unique values, no array access
│   ├── ImmutableSet<E>
│   └── WritableSet<E>
│       └── MutableSet<E>
└── List<E>                  → Indexed, array access
    ├── ImmutableList<E>
    └── WritableList<E>
        └── MutableList<E>

Map<K,V>                     → Ordered key-value pairs, array access
├── ImmutableMap<K,V>
└── WritableMap<K,V>
    └── MutableMap<K,V>

Sequence<E>                  → Lazy pipeline, not a collection
```

## Are there lazy collections, like Laravel's `LazyCollection`?

Yes — [`Sequence`](./sequence). Don't confuse it with lazy initialization, which also takes a closure but does something else.

Lazy initialization defers **when** a collection is loaded. The closure runs once, on first read, and from then on it's a regular in-memory collection — `listOf(fn() => ...)` is still a `List`.

A `Sequence` changes **how** a chain runs. Elements flow through `filter()`, `map()` and the rest one at a time, intermediate results are never stored, and a closure source runs again on every pass.

| | `listOf(fn() => ...)` | `sequenceOf(fn() => ...)` |
|---|---|---|
| Closure runs | Once, on first read | On every pass |
| Keeps the data | Yes | Never |
| `filter()`, `map()` | Eager, new list | Lazy, new sequence |
| `count()` | O(1) | O(n), drains |

See [Lazy Initialization](./lazy-init) and [Sequence](./sequence).

## Why does my sequence run the query twice?

A sequence stores nothing, so every terminal operation — and every `foreach` — pulls from the source again. A closure source is called again, along with the query or request inside it. A `Generator` passed directly can't be rewound, so there the second pass throws `NonReplayableSourceException` instead.

When you need the data more than once, materialize it with `toList()`. When a second pass would be a bug, add `constrainOnce: true` to `sequenceOf()` so it fails loudly. See [Iterating More Than Once](./sequence#iterating-more-than-once).

## Why are there no runtime type checks?

For element values, the library relies on **static analysis** (PHPStan, Psalm) rather than runtime validation. There is no check that verifies a value is `User` before it enters a `ListInterface<User>`. The generic annotations (`@template`, `@param`, `@return`) propagate type information through the call chain, and static analyzers flag violations before the code runs.

**Mutable vs Immutable typing:**

- **Mutable collections enforce strict typing.** You cannot add a `string` to a `MutableList<int>` — static analysis will catch this. The collection's type is fixed at creation.
- **Immutable collections allow type widening.** Adding a `string` to an `ImmutableList<int>` returns a new `ImmutableList<int|string>`. This is safe because a new instance is created — the original keeps its type.

**Exception: StringMap and IntMap**

`StringMap` and `IntMap` perform runtime key type checks, throwing `InvalidKeyTypeException` on wrong key types. This enforces the Map contract: what you put in stays exactly as you put it.

Without this check, putting a `string` into `IntMap` would silently cast it to `0` — corrupting your data. Putting an `int` into `StringMap` would technically work, but allowing it would break the contract that what you put in stays in exactly as-is.

```php
$map = mutableIntMapOf([1 => 'a']);
$map->put('foo', 'b'); // Throws InvalidKeyTypeException (would become key 0)
```

::: tip
Run PHPStan at level 6+ on your project. Type violations will surface as analysis errors — not production exceptions.
:::

## Why `removeElement()` instead of `remove()` on Collection?

Maps use `remove($key)` to remove an entry by key. Collections (List, Set) use `removeElement($element)` to remove by value. The different name eliminates ambiguity when refactoring between collection types — it's always clear whether you're removing by key or by value.

If both used `remove()`, switching between `MutableSet` and `MutableMap` could silently change the semantics of your code without any warning. A few extra characters is a small price for avoiding that kind of bug.

## Why are the base interfaces read-only?

In Java, the `List` interface exposes methods like `add()` even for read-only lists, leading to runtime exceptions (`UnsupportedOperationException`). The type system effectively hides whether a collection is actually modifiable.

This library solves this by making base interfaces strictly **read-only** contracts:

- `MutableSet` extends `Set` and adds in-place mutation.
- `ImmutableSet` extends `Set` and adds persistent mutation (returning new instances).

If a function accepts `ImmutableSet`, it is guaranteed that the state will never change. There are no optional operations or runtime surprises.

## Why do both Mutable and Immutable have the same mutating method names?

Having `$set->add($v)` on both `MutableSet` and `ImmutableSet` may seem confusing at first. However, using different method names for mutable and immutable variants would be more confusing in practice — the operations are conceptually identical, only the semantics differ (in-place vs. new instance).

With `#[NoDiscard]`, calling a method that returns a new instance without using the result triggers a warning. This eliminates the most common mistake when working with immutable collections.

## Why is `MapEntry` a snapshot, not a live view?

A live view would introduce "action at a distance" — code holding an `$entry` variable has no way to know the map was modified elsewhere, and accessing a property could suddenly fail:

```php
$entry = $map->entries->first();
$map->remove('key');
echo $entry->value; // Would this throw? With snapshots, it doesn't.
```

Snapshots are more predictable and safer to use. The usability cost of live views is too high compared to the memory savings.

## Why are `$map->keys` and `$map->values` read-only views?

In Java, `keySet()` and `values()` on mutable maps return mutable views. Modifying these views modifies the underlying map — but only partially. Adding through the view throws an exception, while removing propagates to the map. If multiple keys share the same value, removing from the `values()` view has ambiguous behavior.

To avoid this confusion, keys and values views are always read-only. Since the base `Collection` interface is already read-only, this fits naturally into the type hierarchy.

## Why does `$map->containsKey()` exist when `$map->keys->contains()` works?

The library avoids duplicating methods. For most operations, use the views — there's no overhead since views are connected directly to the map's internal store:

```php
$map->entries->first();   // Not $map->first()
$map->entries->random();  // Not $map->random()
$map->values->sum();      // Not $map->sum()
```

The exceptions are `containsKey()` and `containsValue()` — checking if a key or value exists is so common that writing `$map->keys->contains()` every time would be tedious.

## Why does `$map->toArray()` throw on duplicate or incompatible keys?

When converting a Map to a native PHP array, silent key casting or deduplication would cause data loss — the exact problem this library tries to prevent. Strict checks during conversion ensure the resulting array faithfully represents the map's contents. If the keys are incompatible with PHP's array key restrictions, the error message explains how to resolve it with `mapKeys()`.

If you use `stringMapOf()` or `intMapOf()`, `toArray()` is always safe — keys are guaranteed to be the same type and cannot collide, so no runtime checks are performed.

::: tip When is toArray() safe?
`Map<string, V>` and `Map<int, V>` will never throw — two distinct strings (or ints) always remain distinct, even numeric-looking strings like `"1"` and `"01"`. Exceptions are thrown for:
- **Object keys** — PHP arrays don't support these
- **Mixed scalar types** — e.g., string `"1"` and int `1` collide
- **Floats with precision loss** — e.g., `1.5` cannot become an array key without losing `.5`
:::

::: tip Handling collisions
Pass a `KeyCollisionStrategy` to control behavior when keys collide after PHP's native casting:

```php
$map = mapOfPairs([['1', 'a'], [1, 'b']]); // string "1" and int 1 as separate keys

$map->toArray(); // throws ConversionException
$map->toArray(KeyCollisionStrategy::KeepFirst); // [1 => 'a']
$map->toArray(KeyCollisionStrategy::KeepLast); // [1 => 'b']
```
:::

## Why don't mutable collections allow type widening?

When you declare a `MutableMap<string, int>`, you're making a contract: this map holds string keys and integer values. If you could silently add a string value, the type would widen to `MutableMap<string, int|string>` without any warning.

The problem is that bugs get caught far from their source. Imagine a third-party library gives you a `MutableMap<string, string|bool>` to populate with configuration for an API call. You accidentally put an integer value instead of a string or bool. With widening, no warning — the type silently becomes `MutableMap<string, string|bool|int>`. Later, the API call fails because the library expected only strings and bools, and you have to trace back through your code to find where the integer sneaked in.

With strict typing, PHPStan catches the mistake immediately when you try to put the wrong type. The error appears right where the bug is, not somewhere downstream when the API fails.

Immutable collections allow widening because they return a **new** instance. You're not corrupting an existing contract — you're creating a new value with a new type. The original collection keeps its declared type.

