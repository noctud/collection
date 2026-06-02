# Cheatsheet

A scannable, example-driven tour of the API. For exact signatures and edge cases, see the [API Reference](./api/collection).

## How to read this

- Examples use the [factory functions](./api/functions) — `listOf`, `setOf`, `mapOf` (immutable) and their `mutable*` variants.
- Emoji are just sample values; plain numbers appear where the operation is arithmetic.
- A trailing comment shows the **result's contents**: `[...]` is a list, `{...}` is a set or map.
- Unless noted, **transformation and ordering methods return a new immutable collection** — the source is never changed, even when it is mutable.
- Predicates/selectors on **List/Set** receive `(element, int $index)`; on **Map**, `(value, key)`.
- Some examples reuse `$isFruit = fn($e) => in_array($e, ['🍎','🍊','🍋','🍇'], true);`.

## Creating collections

```php
listOf(['🍎','🍊','🍋']); // ImmutableList
mutableListOf([1, 2, 3]); // MutableList
setOf(['🍎','🍎','🍊']); // ImmutableSet {🍎, 🍊} — duplicates dropped
mutableSetOf(['a','b']); // MutableSet
mapOf(['🍎' => 3, '🍌' => 5]); // ImmutableMap (fruit → count)
mutableMapOf(['x' => 10]); // MutableMap

mapOfPairs([['1','a'], [$user,'b']]); // preserves exact key types
stringMapOf(['name' => 'Amy']); // optimized for string keys
intMapOf([1 => 'a', 2 => 'b']); // optimized for int keys

listOf(fn() => loadRows()); // lazy — loaded on first access
```

## List & Set

Inherited from `Collection<E>`, so available on **both lists and sets**.

### Element access

```php
listOf(['🍎','🍊','🍇'])->first(); // 🍎
listOf([])->firstOrNull(); // null
listOf(['🍎','🍊','🍇'])->last(); // 🍇
listOf(['🍎'])->single(); // 🍎 — throws unless exactly one element
listOf(['🍎','🍊','🍇'])->find($isFruit); // 🍎 (first match)
listOf(['🍎','🍊','🍇'])->findLast($isFruit); // 🍇 — last match
listOf(['🥕','🥦'])->expect($isFruit); // throws NoSuchElementException
listOf(['🍎','🍊','🍇'])->random(); // e.g. 🍊
```

### Querying

```php
listOf(['🍎','🍊','🍋'])->contains('🍊'); // true
listOf(['🍎','🍊','🍋'])->containsAll(['🍎','🍋']); // true
listOf(['🍎','🍊'])->all($isFruit); // true
listOf(['🍎','🥕'])->any($isFruit); // true
listOf(['🥕','🥦'])->none($isFruit); // true
listOf(['🍎','🍊','🍋'])->count(); // 3
listOf(['🍎','🥕','🍊'])->countWhere($isFruit); // 2
listOf([])->isEmpty(); // true
```

### Aggregation

Arithmetic, so these use plain numbers. `fold` starts from an initial value, `reduce` from the first element.

```php
listOf([1, 2, 3])->fold(10, fn($acc, $n) => $acc + $n); // 16
listOf([1, 2, 3])->reduce(fn($acc, $n) => $acc + $n); // 6
listOf([])->reduceOrNull(fn($a, $b) => $a + $b); // null
listOf([10, 20, 30])->sum(); // 60
listOf([10, 20, 30])->avg(); // 20.0
listOf([3, 1, 2])->min(); // 1
listOf([3, 1, 2])->max(); // 3
```

`min`/`max` return the **element**; `minOf`/`maxOf` return the selector **value**.

```php
$people = listOf([
    ['name' => 'Amy', 'age' => 30],
    ['name' => 'Bob', 'age' => 25],
]);
$people->min(fn($p) => $p['age']); // ['name' => 'Bob', 'age' => 25]
$people->minOf(fn($p) => $p['age']); // 25
```

```php
listOf(['🍎','🍊','🍋'])->joinToString(', '); // "🍎, 🍊, 🍋"
listOf(['🍎','🍊'])->joinToString(', ', '[', ']'); // "[🍎, 🍊]"
listOf(['🍎','🍊','🍎'])->countBy(fn($e) => $e); // {🍎: 2, 🍊: 1}
```

### Transformation

```php
listOf(['🍎','🥕','🍊','🥦'])->filter($isFruit); // [🍎, 🍊]
listOf(['🍎', null, '🍊'])->filterNotNull(); // [🍎, 🍊]
listOf($animals)->filterInstanceOf(Dog::class); // ImmutableList<Dog>
listOf(['🐛','🐛','🐛'])->map(fn($e) => '🦋'); // [🦋, 🦋, 🦋]
listOf([1, 2, 3, 4])->mapNotNull(fn($n) => $n % 2 ? null : $n); // [2, 4]
listOf([['🍎','🍊'], ['🍋']])->flatMap(fn($g) => $g); // [🍎, 🍊, 🍋]
listOf([['🍎','🍊'], ['🍋']])->flatten(); // [🍎, 🍊, 🍋]
```

```php
listOf(['🍎','🍊','🍋','🍇'])->takeFirst(2); // [🍎, 🍊]
listOf(['🍎','🍊','🍋','🍇'])->takeLast(2); // [🍋, 🍇]
listOf(['🍎','🍊','🍋','🍇'])->dropFirst(2); // [🍋, 🍇]
listOf(['🍎','🍊','🍋','🍇'])->dropLast(2); // [🍎, 🍊]

listOf(['🍎','🍊','🥕','🍋'])->takeWhile($isFruit); // [🍎, 🍊]
listOf(['🍎','🍊','🥕','🍋'])->dropWhile($isFruit); // [🥕, 🍋]

listOf(['🍎','🍊','🍎','🍋'])->distinct(); // [🍎, 🍊, 🍋]
$people->distinctBy(fn($p) => $p['age']); // first of each distinct age
```

`chunked` splits into fixed-size pieces; `windowed` slides a window across.

```php
listOf([1, 2, 3, 4, 5])->chunked(2); // [[1,2], [3,4], [5]]
listOf([1, 2, 3, 4])->windowed(2); // [[1,2], [2,3], [3,4]]
listOf([1, 2, 3, 4, 5])->windowed(3, step: 2); // [[1,2,3], [3,4,5]]
```

`zip` pairs two lists (stops at the shorter); `zipWithNext` pairs adjacent elements; `unzip` is the inverse of `zip`.

```php
listOf(['🧑','👩'])->zip(['🎩','👒']); // [[🧑,🎩], [👩,👒]]
listOf(['🍎','🍊','🍋'])->zipWithNext(); // [[🍎,🍊], [🍊,🍋]]
listOf([['🧑','🎩'], ['👩','👒']])->unzip(); // [[🧑,👩], [🎩,👒]]
```

`partition` splits into `[matching, rest]`; `groupBy` returns a `Map` of lists.

```php
listOf(['🍎','🥕','🍊','🥦'])->partition($isFruit); // [[🍎,🍊], [🥕,🥦]]
listOf([1, 2, 3, 4])->groupBy(fn($n) => $n % 2 ? 'odd' : 'even');
// {odd: [1, 3], even: [2, 4]}
```

Set operations always return an `ImmutableSet`.

```php
listOf(['🍎','🍊','🍋'])->intersect(['🍊','🍋','🍇']); // {🍊, 🍋}
listOf(['🍎','🍊'])->union(['🍊','🍇']); // {🍎, 🍊, 🍇}
listOf(['🍎','🍊','🍋'])->subtract(['🍊','🍋']); // {🍎}
```

### Ordering

```php
listOf([3, 1, 2])->sorted(); // [1, 2, 3]
listOf([3, 1, 2])->sortedDesc(); // [3, 2, 1]
$people->sortedBy(fn($p) => $p['age']); // youngest → oldest
$people->sortedByDesc(fn($p) => $p['age']); // oldest → youngest
listOf([3, 1, 2])->sortedWith(fn($a, $b) => $a <=> $b); // [1, 2, 3]
listOf(['🍎','🍊','🍋'])->reversed(); // [🍋, 🍊, 🍎]
listOf(['🍎','🍊','🍋'])->shuffled(); // random order
```

### Iteration & conversion

```php
listOf(['🍎','🍊'])->forEach(fn($e) => send($e)); // returns the list

listOf(['🍎','🍊','🍎'])->toSet(); // {🍎, 🍊}
setOf(['🍎','🍊'])->toList(); // [🍎, 🍊]
listOf(['🍎','🍊'])->toArray(); // ['🍎', '🍊'] — native PHP array
listOf(['Amy','Bob'])->toMap(fn($n) => $n[0]); // {A: 'Amy', B: 'Bob'}
listOf([1, 2])->toMutable(); // MutableList — always a fresh copy
mutableListOf([1, 2])->toImmutable(); // ImmutableList
```

## List only

Positional access by integer index (`Set` has none). `slice(from, to)` is from-inclusive, to-exclusive.

```php
listOf(['🍎','🍊','🍋'])->get(1); // 🍊 — alias: $list[1]
listOf(['🍎','🍊','🍋'])->getOrNull(9); // null — $list[9] ?? null
listOf(['🍎','🍊'])->getOrDefault(9, '🚫'); // 🚫
listOf(['🍎','🍊','🍋'])->indexOf('🍊'); // 1 (-1 if absent)
listOf([10, 20, 30])->indexOfFirst(fn($n) => $n > 15); // 1
listOf(['🍎','🍊','🍋','🍇'])->slice(1, 3); // [🍊, 🍋]
```

Mutation adds `set(index, value)`, `removeAt(index)`, `removeEvery(value)`.

## Set only

Sets hold **unique** elements (duplicates dropped on insert) and add no methods of their own. One twist: `addFirst()` uses **move-to-front** semantics.

```php
setOf(['🍎','🍎','🍊']); // {🍎, 🍊}
mutableSetOf(['🍎','🍊','🍋'])->addFirst('🍋'); // {🍋, 🍎, 🍊}
```

## Map

A key-value collection with **strict** key handling — keys are never silently cast. Predicates receive `(value, key)`.

### Keys, values & entries

A map exposes three **live, read-only views** as properties: `$keys` is a `Set`, `$values` is a `Collection`, and `$entries` is a `Set` of `MapEntry`. Keys are unique (so a `Set`); values may repeat (so a `Collection`).

```php
$colors = mapOf(['🍎' => 'red', '🍌' => 'yellow', '🍇' => 'purple']);

$colors->keys; // Set<string> {🍎, 🍌, 🍇}
$colors->values; // Collection<string> [red, yellow, purple]
$colors->entries; // Set<MapEntry> {🍎→red, 🍌→yellow, 🍇→purple}
```

Split apart, the same map decomposes like this:

| `$colors` entry | `->keys` (Set) | `->values` (Collection) |
| --- | --- | --- |
| 🍎 → red | 🍎 | red |
| 🍌 → yellow | 🍌 | yellow |
| 🍇 → purple | 🍇 | purple |

### Access & querying

```php
mapOf(['🍎' => 3])->get('🍎'); // 3 — alias: $map['🍎']
mapOf(['🍎' => 3])->getOrNull('🍌'); // null
mapOf(['🍎' => 3])->getOrDefault('🍌', 0); // 0
mutableMapOf(['🍎' => 3])->getOrPut('🍌', fn() => 5); // 5 (stored)

mapOf(['🍎' => 3])->containsKey('🍎'); // true
mapOf(['🍎' => 3])->containsValue(3); // true
mapOf(['🍎' => 3, '🍌' => 5])->count(); // 2
mapOf(['🍎' => 3, '🍌' => 5])->all(fn($v, $k) => $v > 0); // true
```

### Transformation

```php
mapOf(['🍎' => 3, '🍌' => 5])->filter(fn($v, $k) => $v > 4); // {🍌: 5}
mapOf(['🍎' => 3, '🍌' => 5])->filterValues(fn($v) => $v > 4); // {🍌: 5}
mapOf(['🍎' => 3])->mapValues(fn($v, $k) => $v * 10); // {🍎: 30}
mapOf(['🍎' => 3])->mapKeys(fn($v, $k) => "fruit:$k"); // {fruit:🍎: 3}
mapOf(['🍎' => 3, '🍌' => 5])->flip(); // {3: 🍎, 5: 🍌}

mapOf(['🍎' => 3, '🍌' => 5])->map(fn($v, $k) => "$k=$v");
// ['🍎=3', '🍌=5'] — a list
```

### Ordering & conversion

```php
mapOf(['🍌' => 5, '🍎' => 3])->sortedByValue(); // {🍎: 3, 🍌: 5}
mapOf(['🍌' => 5, '🍎' => 3])->reversed(); // {🍎: 3, 🍌: 5}
mapOf(['🍎' => 3])->toArray(); // ['🍎' => 3]
mapOf(['🍎' => 3])->toPairs(); // [['🍎', 3]]
```

### Mutation

```php
mutableMapOf(['🍎' => 3])->put('🍌', 5); // adds/replaces
mutableMapOf(['🍎' => 3])->putIfAbsent('🍎', 9); // no-op — key exists
mutableMapOf(['🍎' => 3, '🍌' => 5])->remove('🍎'); // {🍌: 5}
```

Also: `putFirst`, `putAll`, `putAllPairs`, `removeIf`, `removeIfKey`, `removeIfValue`, `removeNullValues`, `clear`, plus the in-place `sortByKey` / `sortByValue` / … family.

## Mutable vs. immutable

Same method names, different behavior. On a **mutable** collection they change it in place and return it (handy for chaining); on an **immutable** collection they return a **new** collection and leave the original untouched.

```php
$m = mutableListOf(['🍎','🍊']);
$m->add('🍋'); // $m is now [🍎, 🍊, 🍋]

$i = listOf(['🍎','🍊']);
$new = $i->add('🍋'); // $i stays [🍎, 🍊]; $new is [🍎, 🍊, 🍋]
```

Shared mutators (List & Set): `add`, `addFirst`, `addAll`, `removeElement`, `removeFirst`, `removeLast`, `removeIf`, `removeAll`, `retainAll`, `clear`, and the in-place ordering `sort` / `sortBy` / `sortByDesc` / `sortWith` / `reverse` / `shuffle`.

### Tracking changes

`tracked()` wraps a mutable collection so each mutation reports whether it actually changed anything:

```php
$t = mutableSetOf(['🍎','🍊'])->tracked();
$t->add('🍋')->changed; // true
$t->add('🍋')->changed; // false — already present
```
