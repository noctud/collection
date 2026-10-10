---
searchTitle: Sequence — Lazy Collections
---

# Sequence

A `Sequence` is a lazy pipeline — what other PHP libraries call a lazy collection. You chain `filter()`, `map()` and the rest just like on a list, but nothing runs until you ask for a result. Then elements flow through the chain one at a time, and the work stops as soon as the result is known:

```php
$profile = $users->asSequence()
    ->filter(fn($user) => $user->isActive())
    ->map(fn($user) => $api->fetchProfile($user)) // an HTTP request
    ->first(); // fetchProfile() runs once - for the first active user
```

Under the hood, a sequence is a chain of generators with the collection API on top. If you know Laravel's `LazyCollection` or Kotlin's `Sequence`, it's the same idea.

A sequence stores nothing, so every result you ask for runs the chain from the source again. One such run is a **pass** — see [Iterating More Than Once](#iterating-more-than-once).

::: tip Not the same as lazy initialization
[Lazy initialization](./lazy-init) defers **when** a collection is loaded — once loaded, it's a regular in-memory collection. A sequence changes **how** a chain runs — element by element, without storing intermediate results.
:::

## Creating

### From a collection

Every list and set has `asSequence()`. Nothing is copied — each pass reads the collection as it is at that moment, so a sequence over a mutable collection sees its later changes:

```php
$list = mutableListOf([1, 2]);
$sequence = $list->asSequence();

$list->add(3);
$sequence->toArray(); // [1, 2, 3]
```

Maps have no `asSequence()` — go through one of their views:

```php
$map->entries->asSequence(); // Sequence<MapEntry<K, V>>
$map->values->asSequence(); // Sequence<V>
$map->keys->asSequence(); // Sequence<K>
```

### From an iterable or a closure

```php
use function Noctud\Collection\sequenceOf;

sequenceOf([1, 2, 3]); // Sequence<int>
sequenceOf(fn() => $repository->streamAll()); // called every pass
sequenceOf($generator); // single pass only
sequenceOf(); // empty Sequence
```

A closure is a **producer**: it is called at the start of every pass and must return a fresh iterable each time — usually a generator. Unlike `listOf(fn() => ...)`, the result is never kept. A `Generator` object passed directly can't be rewound, so the sequence can be iterated only once. See [Iterating More Than Once](#iterating-more-than-once).

::: info Keys are positional
A sequence is a stream of values. Keys from the source are dropped, and every pass yields `0, 1, 2, …` — `sequenceOf(['a' => 1])` yields `0 => 1`. The index passed to callbacks is that position in the step's input, the same one the eager chain would pass. To keep map keys, go through `$map->entries` and build a map at the end with `toMap()`.
:::

### Generating

`generateSequence()` starts with a seed and computes each next element from the previous one. The sequence ends when the function returns `null`:

```php
use function Noctud\Collection\generateSequence;

// $category, its parent, its grandparent, … up to the root
generateSequence($category, fn($c) => $c->parent)
    ->any(fn($c) => $c->isHidden());
```

When the function never returns `null`, the sequence is infinite. That's fine — pull only what you need:

```php
generateSequence(1, fn($n) => $n * 2)
    ->takeFirst(5)
    ->toList(); // [1, 2, 4, 8, 16]
```

Every pass starts again from the seed. A `null` seed creates an empty sequence.

## Eager vs Lazy

Collection methods are eager: each step processes every element and builds a new collection before the next step starts. A sequence runs all steps for one element before it moves on to the next one. Callbacks that report when they run make the difference visible:

```php
$words = ['The', 'quick', 'brown', 'fox', 'jumps', 'over'];

$isLong = function (string $word): bool {
    echo "filter($word) ";
    return strlen($word) > 3;
};

$length = function (string $word): int {
    echo "map($word) ";
    return strlen($word);
};
```

A list runs each step over all of its elements:

```php
listOf($words)
    ->filter($isLong)
    ->map($length)
    ->takeFirst(2);
// filter(The) filter(quick) filter(brown) filter(fox)
// filter(jumps) filter(over) map(quick) map(brown)
// map(jumps) map(over)
```

A sequence sends each word through the whole chain before it pulls the next one:

```php
sequenceOf($words)
    ->filter($isLong)
    ->map($length)
    ->takeFirst(2)
    ->toList();
// filter(The) filter(quick) map(quick) filter(brown) map(brown)
```

The list filtered all six words and mapped four, only to keep two. The sequence pulled three words and stopped — `fox`, `jumps` and `over` were never touched.

## When to Use a Sequence

Collections are the right default. Reach for a sequence when:

- **The source is large or unbounded** — files, database cursors, paginated APIs. Elements are pulled one at a time, so memory stays flat.
- **You only need part of the result** — `first()`, `find()`, `any()` or `takeFirst()` stop pulling as soon as they have the answer, so an expensive `map()` runs only for the elements that matter.
- **A long chain runs over a big collection** — every eager step allocates a new collection; a sequence allocates no intermediate collections at all.
- **The data is infinite** — [`generateSequence()`](#generating) keeps producing elements until you stop asking.

Stay with collections when:

- **The data is small and you use all of it** — a sequence is no faster there, while a collection can be counted, sorted and read again for free.
- **You need sorting, reversing or random access** — these need every element up front, see [Missing sorted()?](#missing-sorted).
- **You read the result more than once** — every terminal operation runs the whole pipeline again, see [Iterating More Than Once](#iterating-more-than-once).

| | Collection | Sequence |
|---|---|---|
| Evaluation | Eager, step by step | Lazy, element by element |
| Intermediate results | New collection per step | None |
| Iterating again | Free | Runs the pipeline again |
| `count()` | O(1), `Countable` | O(n), method only |
| Sorting, reversing | `sorted()`, `reversed()` | `toList()` first |
| `json_encode()` | Supported | `toList()` first |

## Intermediate vs Terminal

Every method on `Sequence` is one of two kinds:

- **Intermediate** operations — `filter()`, `map()`, `takeFirst()`, `distinct()`, `chunked()`, `zip()`, `onEach()` and others — return a new `Sequence` and run nothing.
- **Terminal** operations — `first()`, `find()`, `count()`, `sum()`, `toList()`, `forEach()` and others — run the pipeline and return a result.

The [API reference](./api/sequence) lists every operation and how much of the source it pulls.

### Stopping early

Some terminal operations stop pulling as soon as they know the answer; the others drain the whole sequence:

| Stop early | Drain the sequence |
|---|---|
| `first`, `elementAt` | `last`, `findLast`, `expectLast` |
| `find`, `expect` | `count`, `countWhere` |
| `any`, `all`, `none` | `fold`, `reduce`, `sum`, `avg` |
| `contains`, `containsAll` | `min`, `max`, `minOf`, `maxOf` |
| `isEmpty`, `isNotEmpty` | `toList`, `toSet`, `toArray`, `toMap` |
| `single` — at the 2nd element | `groupBy`, `countBy`, `partition` |
| `joinToString` with `$limit` | `unzip`, `forEach` |

The `OrNull` variants stop at the same point as their throwing counterparts. On an infinite sequence, only the left column can return — put a `takeFirst()` or `takeWhile()` in front of the others.

### `onEach()` vs `forEach()`

`onEach()` is intermediate: it runs an action on each element as it passes through and keeps the chain going. `forEach()` is terminal: it runs the pipeline and returns nothing.

```php
$overdue = $orders->asSequence()
    ->onEach(fn($order) => $logger->debug($order->id))
    ->find(fn($order) => $order->isOverdue());
// logs only the orders checked until the first overdue one
```

Like every intermediate operation, `onEach()` does nothing until a terminal operation runs the chain. Intermediate operations are marked `#[NoDiscard]`, so PHP 8.5 warns when a chain is built but never run:

```php
// Warning - nothing is sent
$orders->asSequence()->onEach(fn($order) => $mailer->send($order));

// Sends every order
$orders->asSequence()->forEach(fn($order) => $mailer->send($order));
```

### Memory

Most intermediate operations hold a single element at a time. A few need more, but never the whole source:

- `chunked()` and `windowed()` hold one chunk or window, and yield it as an `ImmutableList`.
- `distinct()` and `distinctBy()` remember every distinct element (or selector value) seen during the pass — memory grows with the number of distinct values.
- `zip()` pulls the other iterable in lockstep, without copying it.

Terminal operations that return collections — `toList()`, `groupBy()`, `partition()` — hold everything they return. `countBy()` holds only one counter per key.

## Iterating More Than Once

Each terminal operation — and each `foreach` — is a new pass that pulls from the source from the start. Whether a sequence supports another pass depends on its source:

| Source | Another pass |
|---|---|
| `array` | Replays the array |
| `Closure` | Calls the closure again |
| Collection, `asSequence()` | Reads the current contents |
| Other `IteratorAggregate` | Calls `getIterator()` again |
| `Iterator`, `Generator` | Throws |
| Any, with `constrainOnce: true` | Throws |

A pass that stopped early, like `first()`, still counts as a pass.

::: warning Every terminal operation runs the pipeline again
A sequence stores nothing, so two terminal operations mean two passes — and a producer runs twice:

```php
$rows = sequenceOf(fn() => $db->cursor('SELECT * FROM orders'));

$rows->count(); // runs the query
$rows->first(); // runs the query again
```

When you need several answers from the same data, materialize it once with `toList()` and continue on the list — see [Best Practices](./best-practices#materialize-sequences-you-read-twice).
:::

### Single-pass sources

A generator can't be rewound. Rather than silently yielding nothing the second time, the sequence throws:

```php
$sequence = sequenceOf($repository->streamAll()); // a Generator

$sequence->first(); // fine
$sequence->first(); // throws NonReplayableSourceException
```

Wrap the call in a closure — `sequenceOf(fn() => $repository->streamAll())` — when the sequence needs to replay.

A producer — a closure or an `IteratorAggregate` — must return a **fresh** iterable on every call. One that keeps returning the same generator, like `fn() => $generator`, is detected and throws on the second pass too.

### Forcing a single pass

When running the source again would repeat a side effect — a request, a query, a write — set `constrainOnce: true`. The second pass then throws instead of calling the closure again:

```php
$events = sequenceOf(
    fn() => $api->fetchEvents(),
    constrainOnce: true,
);
```

::: tip Catching source errors
`NonReplayableSourceException` and `InvalidSequenceSourceException` (thrown when a closure returns something that is not iterable) both implement `SourceException` — the one type every terminal operation declares. All exceptions of the library implement `NoctudCollectionException`.
:::

## Missing `sorted()`?

A sequence has no `sorted()`, `reversed()`, `shuffled()` or `takeLast()`. Each of them has to read the whole source before it can yield its first element — exactly what a sequence is meant to avoid. `dropLast()`, `random()` and the set operations are left to collections too. When you need one, materialize with `toList()` and continue on the list, so the cost shows in your code:

```php
$top = $scores->asSequence()
    ->filter(fn($score) => $score->isValid())
    ->toList() // everything left is loaded here
    ->sortedByDesc(fn($score) => $score->points)
    ->takeFirst(10);
```

The same goes for `count()` and `json_encode()`. A sequence is neither `Countable` nor `JsonSerializable`:

- `count($sequence)` throws a `TypeError` — call `$sequence->count()` to drain it explicitly.
- `json_encode($sequence)` produces `{}` — encode `$sequence->toList()` instead.

See [No Hidden Materialization](./design#no-hidden-materialization) for the reasoning.

## Use Cases

### Reading a large file

```php
function readLines(string $path): Generator
{
    $handle = fopen($path, 'r');

    try {
        while (($line = fgets($handle)) !== false) {
            yield rtrim($line, "\n");
        }
    } finally {
        fclose($handle);
    }
}

$errors = sequenceOf(fn() => readLines('/var/log/app.log'))
    ->filter(fn($line) => str_contains($line, 'ERROR'))
    ->takeFirst(20)
    ->toList();
```

Only one line is in memory at a time. Once 20 errors are found, the file is not read any further — and `finally` closes it when the generator is released.

### Paginated APIs

```php
$customer = generateSequence(1, fn($page) => $page + 1)
    ->map(fn($page) => $api->customers(page: $page))
    ->takeWhile(fn($customers) => $customers !== [])
    ->flatten()
    ->find(fn($customer) => $customer->email === $email);
```

Page numbers are infinite, but pages are fetched one by one — only until the customer is found, or an empty page ends the sequence.

### Database cursors

With Doctrine, `toIterable()` streams the results one entity at a time:

```php
$query = $em->createQuery('SELECT u FROM User u');

// Replayable - every pass runs the query again
$users = sequenceOf(fn() => $query->toIterable());

// Single pass - another pass throws instead of querying again
$users = sequenceOf($query->toIterable());
```

### Processing in batches

`chunked()` groups a stream into batches without loading it — handy for bulk writes and indexing:

```php
sequenceOf(fn() => $query->toIterable())
    ->map(fn(User $user) => $user->toSearchDocument())
    ->chunked(500)
    ->forEach(function ($documents) use ($searchIndex, $em) {
        $searchIndex->addDocuments($documents->toArray());
        $em->clear(); // Doctrine keeps every loaded entity until cleared
    });
```

Each batch is an `ImmutableList` of up to 500 documents, and only the batch being filled is held in memory.

## Custom Sequences

`GeneratorSequence`, the class `sequenceOf()` returns, is final — but its behavior lives in the `SequenceLogic` trait. To build your own sequence on top of any class, see [Extending](./extending#custom-sequences).
