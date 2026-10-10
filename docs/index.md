---
layout: home

hero:
  name: Noctud Collection
  tagline: Type-safe, clean, mutable/immutable, sortable and key-preserving collections for PHP 8.4+
  image:
    light: /hero-light.svg
    dark: /hero-dark.svg
    alt: List / Set / Map
  actions:
    - theme: brand
      text: Getting Started
      link: /collection/getting-started
    - theme: alt
      text: API Reference
      link: /collection/api/collection

features:
  - title: Type-safe
    details: Full generics support. Static analyzers understand every element type through the entire chain.
  - title: Key-preserving
    details: "Map keys like float, bool, objects, or \"1\" retain their original types. No silent type casting."
  - title: Mutable & Immutable
    details: Choose the right variant for every use case. Immutable methods are marked with NoDiscard to prevent accidental misuse.
  - title: Lazy Initialization
    details: Construct collections from closures. Uses PHP 8.4 lazy objects — the internal store is a ghost proxy materialized only on first access.
  - title: Lazy Sequences
    details: Process large or infinite data element by element, without intermediate collections. Chains stop once the result is known.
  - title: Object keys
    details: Use objects as map keys out of the box. Implement the Hashable interface for custom identity semantics.
---

<style>
.VPHero .image {
  margin-top: 40px;
}

@media (max-width: 768px) {
  .VPHero .image {
    display: none !important;
  }
}

.code-showcase {
  max-width: 960px;
  margin: 0 auto;
  padding: 0 24px 64px;
}

.code-showcase h2 {
  font-size: 28px;
  font-weight: 700;
  margin-top: 112px;
  margin-bottom: 8px;
  border-top: 1px solid var(--vp-c-divider);
  padding-top: 24px;
}

.code-showcase h3 {
  font-size: 20px;
  font-weight: 600;
  margin-top: 32px;
  margin-bottom: 4px;
}

.code-showcase p {
  color: var(--vp-c-text-2);
  margin-bottom: 16px;
  line-height: 1.7;
}

.side-by-side {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}

@media (max-width: 768px) {
  .side-by-side {
    grid-template-columns: 1fr;
  }
}

.side-by-side > div > p {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 6px;
  color: var(--vp-c-text-1);
}

.plugin-section img {
  border-radius: 8px;
  border: 1px solid var(--vp-c-divider);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.plugin-install {
  display: inline-flex;
  align-items: center;
  padding: 12px 24px;
  background: var(--vp-c-brand-1);
  color: var(--vp-c-white) !important;
  border-radius: 20px;
  font-weight: 600;
  font-size: 15px;
  text-decoration: none !important;
  transition: background 0.25s;
}

.plugin-install:hover {
  background: var(--vp-c-brand-2);
}
</style>

<div class="code-showcase">

## If you know PHP arrays

**Key preservation**

Native PHP arrays silently cast keys — `"1"` becomes `int(1)`, `true` becomes `1`, and objects cannot be used as keys at all. Even `array<string, mixed>` in PHPDoc is not an option — insert `"123"` and it becomes `int(123)`. Map preserves every key exactly as written.

<div class="side-by-side">
<div>

Noctud Collection

```php
$map = mutableMapOf();
$map[true] = 'a';
$map[false] = 'b';
$map[1.1] = 'c';
$map['1'] = 'd';
$map[new User()] = 'e';

$map->keys->first(); // true
$map->keys; // [true, false, 1.1, "1", User]
$map->values; // ['a', 'b', 'c', 'd', 'e']
```

</div>
<div>

Native PHP

```php
$array = [];
$array[true] = 'a'; // key cast to 1
$array[false] = 'b'; // key cast to 0
$array[1.1] = 'c'; // deprecated, cast to int 1
$array['1'] = 'd'; // cast to int 1, overwrites
$array[new User()] = 'e'; // error

array_key_first($array); // 1
array_keys($array); // [1, 0]
array_values($array); // ['d', 'b']
```

</div>
</div>

**Filtering**

Filtering a PHP array leaves gaps in integer keys. Forgetting `array_values()` leads to bugs where `$result[0]` is suddenly undefined. Collections always reindex automatically.

<div class="side-by-side">
<div>

Noctud Collection

```php
$list = listOf([10, 20, 30, 40]);
$filtered = $list->filter(fn($n) => $n > 15);
// [20, 30, 40] - indices 0, 1, 2
```

</div>
<div>

Native PHP

```php
$array = [10, 20, 30, 40];
$filtered = array_filter($array, fn($n) => $n > 15);
// $filtered[0] is undefined
```

</div>
</div>

**Type safety**

PHPStan doesn't warn when you assign incompatible types to a PHP array — the type silently widens. Mutable collections enforce strict typing, catching bugs at analysis time. Type is only widened in immutable collections because they produce new instances.

<div class="side-by-side">
<div>

Noctud Collection

```php
$mut = mutableMapOf(['a' => 1]);
$mut['b'] = 'wrong'; // PHPStan error

// Immutable — type widens for a new map
$new = mapOf(['a' => 1])->put('b', 'ok');
```

</div>
<div>

Native PHP

```php
/** @var array<string, int> $array */
$array = ['a' => 1];
$array['b'] = 'wrong'; // No warning, type widens
```

</div>
</div>

**Accessing elements**

Array access is strict — `$list[99]` throws when the index is out of bounds, just like `get()`. Use `??` for safe fallback, or `getOrNull()` for the nullable variant. Same pattern for `first`, `last`, `random`, and `single`.

<div class="side-by-side">
<div>

Noctud Collection

```php
$list[0]; // throws if missing
$list[0] ?? null; // null if missing
```

```php
$list->last(); // throws if empty
$list->lastOrNull(); // null if empty
```

</div>
<div>

Native PHP

```php
array_key_exists(0, $array) ? $array[0]
    : throw new Exception();
$array[0] ?? null;
```

```php
count($array) > 0 ? $array[array_key_last($array)]
    : throw new Exception();
count($array) > 0 ? $array[array_key_last($array)]
    : null;
```

</div>
</div>

**Sorting**

The `sorted*` methods are on all collections and Mutable has also `sort*` methods for in-place sorting.

<div class="side-by-side">
<div>

Noctud Collection

```php
// Creates a new collection
$byAge = $users->sortedBy(fn($u) => $u->age);
```
```php
// In-place DESC by name (Mutable only)
$users->sortByDesc(fn($u) => $u->name);
```

</div>
<div>

Native PHP

```php
// Creates a new array
$byAge = $users;
usort($byAge, fn($a, $b) => $a->age <=> $b->age);
```
```php
// In-place DESC by name
usort($users, fn($a, $b) => $b->name <=> $a->name);
```

</div>
</div>

[Learn more about Lists, Sets, and Maps →](/collection/getting-started)

## Mutable and Immutable

Both variants share the same API. The difference is how mutating methods behave. Immutable mutating methods are marked with `#[NoDiscard]` — PHP 8.5 will warn you if you forget to capture the return value.

```php
$set = setOf([1, 2, 3]); // ImmutableSet<int>
$set->add(4); // PHP Warning: return value is unused
$new = $set->add(4)->reversed(); // Ok

$list = mutableListOf([1, 2, 3]); // MutableList<int>
$list->add(4)->shuffle()->sort(); // Ok
```

Track state of Mutable collections, `tracked()` returns mutable collection wrapped in a proxy that tracks changes.

```php
if ($set->tracked()->add('a')->changed) // do something only if 'a' was not in set
```

Build with mutable, then freeze with `toImmutable()` — conversion is virtually free thanks to copy-on-write. The data is only duplicated when either side is modified.

[Read about mutability →](/collection/mutability)

## Map views

Every Map exposes live **read-only** `$keys`, `$values`, and `$entries` views. These are real collection objects, not plain arrays, and they share memory space with the Map. Modifying the map updates the views.

```php
$map = mapOf(['rodney' => 28, 'sheppard' => 35, 'teyla' => 22]);

$map->values->min(); // 22
$map->values->avg(); // 28.33
$map->keys->filter(fn($k) => strlen($k) > 5); // Set {'rodney', 'sheppard'}
$map->entries->first(); // MapEntry { key: 'rodney', value: 28 }
```

Thanks to views, the Map interface doesn't need to be polluted with `randomKey()`, `randomValue()`, `randomEntry()` (and first, last, single etc. and all their nullable variants), you can simply access it via the corresponding view.

[Read about maps →](/collection/map)

## Lazy Initialization

Construct from a closure — the callback executes only on first access.

```php
// The query runs only if $users is actually read
$template->users = listOf(fn() => $repository->getAllUsers());

// Lazy property that loads config on demand
private ImmutableMap $config {
    get => $this->config ??= mapOf(fn() => $this->loadConfig());
}
```

Lazily initialized collections behave identically to regular ones — there is no way to tell from outside.

::: info How it works
Under the hood, lazy initialization uses PHP 8.4's [Lazy Objects](https://www.php.net/manual/en/language.oop5.lazy-objects.php) feature. The internal store is wrapped in a ghost proxy via `ReflectionClass::newLazyProxy()` — the real store object is only created when first accessed. This is a native language feature with zero userland overhead once initialized.
:::

[Read about lazy initialization →](/collection/lazy-init)

## Lazy Sequences

Collection transformations are eager — each step builds a new collection. Call `asSequence()` and the same chain runs element by element instead, stopping as soon as the result is known.

<div class="side-by-side">
<div>

Collection — each step runs on everything

```php
$users
    ->filter(fn($u) => $u->isActive())
    ->map(fn($u) => $api->profile($u))
    ->first();
// profile() runs for every active user
```

</div>
<div>

Sequence — stops at the first result

```php
$users->asSequence()
    ->filter(fn($u) => $u->isActive())
    ->map(fn($u) => $api->profile($u))
    ->first();
// profile() runs once
```

</div>
</div>

Sequences stream from any source — a file, a database cursor, a paginated API — or from nowhere at all:

```php
// readLines() is your own generator yielding one line at a time
// Stops after 10 errors - the whole file is never loaded
$errors = sequenceOf(fn() => readLines('app.log'))
    ->filter(fn($line) => str_contains($line, 'ERROR'))
    ->takeFirst(10)
    ->toList();

// Infinite, but only the first 10 elements are ever computed
$powers = generateSequence(1, fn($n) => $n * 2)->takeFirst(10)->toList();
```

[Read about sequences →](/collection/sequence)

## Interface-driven design

Every type you interact with is an interface — `ImmutableList`, `MutableMap`, `Set`, even `MapEntry`. Factory functions return interfaces, never concrete classes:

```php
$list = listOf([1, 2, 3]); // ImmutableList<int> - not ImmutableArrayList
$map = mutableMapOf(['a' => 1]); // MutableMap<string, int> - not MutableHashMap
$entry = $map->entries->first(); // MapEntry<string, int> - not SimpleMapEntry
```

This is possible thanks to PHP 8.4 interface properties. Map views like `$keys`, `$values`, and `$entries` are declared directly on the `Map` interface — no abstract class needed.

**Why this matters:** your code depends only on contracts, not on the internal storage or implementation details. You can swap `mapOf()` for `stringMapOf()` without changing a single type-hint. And if you need a custom collection backed by a database cursor or a Redis sorted set, you can implement the interface directly — the rest of your codebase doesn't need to know.

[Read about extending collections →](/collection/extending)

## Performance

This library prioritizes type safety and correctness. For hot loops over millions of elements, use plain arrays. For domain logic, business rules, and API boundaries, this gives you guarantees arrays can't. Lists and Sets have minimal overhead compared to native arrays. The generic `mapOf()` uses dual-array storage (keys + values) to preserve any key type (objects, float, bool), which adds memory and performance overhead compared to native arrays.

::: tip Optimized maps for string/int keys
When keys are exclusively strings or integers, the type-specific variants offer maximum performance:

```php
$users = stringMapOf(['rodney' => 28, 'sheppard' => 35]); // or mutableStringMapOf()
$scores = intMapOf([1 => 100, 2 => 85, 3 => 92]); // or mutableIntMapOf()
```

These use single-array storage and skip key hashing entirely, but still preserve key types.
- **~50% less memory** than `mapOf()` (single array vs dual array)
- **Zero-copy initialization** — when created from an existing array, data is shared via copy-on-write until modified
- **Faster** iteration, lookups, and writes
:::

### Copy-on-write everywhere

Converting between mutable and immutable variants via `toMutable()` / `toImmutable()` uses copy-on-write — the underlying data is shared until either side is modified, making variant switching virtually free.

The same applies when constructing a new collection from an existing one — even across mutable/immutable boundaries.

Data is only duplicated when either (mutable) side is actually modified — collections can be freely passed between layers or converted between mutable and immutable without any memory or performance penalty.

<div class="plugin-section">

## PhpStorm / IntelliJ Plugin

PhpStorm doesn't fully understand PHP generics — type inference breaks in callbacks and there's no autocomplete through view properties. The [**Noctud**](https://plugins.jetbrains.com/plugin/30173-noctud) plugin fixes these IDE limitations so collections work seamlessly in the editor.

::: tabs
== View Autocomplete

Type `$map->random` and the plugin suggests methods from `values`, `keys`, and `entries` views directly — no need to type the full property path.

![View autocomplete in PhpStorm](/intellij_autocomplete_help.png)

== Generic Type Inference

Callback parameters like `fn ($v) =>` inside `filter()`, `map()`, or any higher-order method correctly resolve the generic type — the IDE knows exactly what `$v` is.

![Generic type inference fixed](/intellij_fixed_generics.png)
:::

<a href="https://plugins.jetbrains.com/plugin/30173-noctud" target="_blank" class="plugin-install">Install from JetBrains Marketplace →</a>

</div>

## Inspiration

The API is heavily inspired by [Kotlin Collections](https://kotlinlang.org/docs/collections-overview.html) — factory functions, the mutable/immutable split, `OrNull` conventions, and method naming all follow Kotlin patterns. The type hierarchy draws from the Java Collections Framework with clear separation of mutability/immutability.

[Getting started →](/collection/getting-started)

</div>
