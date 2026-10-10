# Lazy Initialization

Collections can defer initialization until first access. The backing data is computed from a callback only when an operation requires it.

::: tip Looking for lazy pipelines?
Lazy initialization defers **when** a collection is loaded — once loaded, it's a regular in-memory collection. A [Sequence](./sequence) changes **how** a chain runs — element by element, without storing intermediate results.
:::

## Creating

Pass a `Closure` to any factory function:

```php
use function Noctud\Collection\listOf;
use function Noctud\Collection\setOf;
use function Noctud\Collection\mapOf;

$list = listOf(fn() => fetchElementsFromApi());
$set = setOf(fn() => loadUniqueTokens());
$map = mapOf(fn() => loadConfiguration());
```

The closure can return an array, a `Generator`, or any `iterable`:

```php
$map = mapOf(fn() => ['key' => 'value']);

$map = mapOf(function () {
    yield 'a' => 1;
    yield 'b' => 2;
});
```

## When Materialization Happens

The callback executes on the first operation that reads the collection's contents:

```php
$lazy = mapOf(fn() => expensiveQuery());

$lazy->values; // still lazy - views are also lazy
$lazy->count(); // materializes here - needs to know the size
```

If the collection is never read, the callback never runs.

## Closures vs Generators

Always wrap generators in a closure. Passing a `Generator` object directly causes immediate materialization:

```php
$callable = function (): Generator { yield 'a' => 1; };

// Correct - closure, stays lazy
$map = mapOf($callable);
$map = mapOf(fn() => $callable());

// Wrong - generator object, materialized immediately
$generator = $callable();
$map = mapOf($generator);
```

::: warning
A `Generator` is an `iterable`, so `mapOf($generator)` works — but it materializes immediately, which is intended. Always pass a `Closure` for lazy behavior.
:::

::: tip Sequences are different
`sequenceOf($generator)` does not materialize — a sequence pulls from the generator as it goes. A generator can't be rewound, though, so that sequence can be iterated only once. See [Iterating More Than Once](./sequence#iterating-more-than-once).
:::

## Lazy Views

Views of a lazily initialized map stay uninitialized too. Accessing `->values` or `->keys` does not trigger materialization:

```php
$map = mapOf(fn() => expensiveQuery());

$keys = $map->keys; // no query yet
$vals = $map->values; // no query yet

$keys->count(); // query runs now
```

## Use Cases

### Deferred database queries

```php
class UserRepository
{
    /** @var ImmutableMap<int, User> */
    public ImmutableMap $indexedUsers {
        get => $this->indexedUsers ??= mapOf(fn() => $this->loadAll());
    }
}
```

### Property hooks with lazy init

```php
class Config
{
    /** @var MutableMap<string, mixed> */
    private MutableMap $cache {
        get => $this->cache ??= mutableMapOf();
    }
}
```

### Conditional initialization

```php
$fallback = listOf(fn() => computeExpensiveDefaults());

if ($primarySource->isNotEmpty()) {
    return $primarySource; // fallback is never materialized
}

return $fallback;
```

## Transparent Behavior

From the outside, lazily initialized and regular collections are indistinguishable. Switching between them requires no code changes:

```php
// Regular
$users = listOf($userArray);

// Lazily initialized - same interface, same behavior
$users = listOf(fn() => $userArray);
```

This makes lazy initialization useful for optimizing existing code without refactoring callers.

There is intentionally no `isInitialized()` method — exposing initialization state would break the abstraction and encourage patterns that depend on implementation details. If you need to track whether a collection was accessed, manage that state externally.

## How It Works

Lazy initialization leverages PHP 8.4's native [Lazy Objects](https://www.php.net/manual/en/language.oop5.lazy-objects.php) feature. When you pass a closure to a factory function, the internal store is wrapped in a **ghost proxy** via `ReflectionClass::newLazyProxy()`:

```php
// Simplified internal implementation
public function __construct(iterable|Closure $data)
{
    if ($data instanceof Closure) {
        $reflector = new ReflectionClass(ArrayIndexStore::class);
        $this->store = $reflector->newLazyProxy(function () use ($data) {
            return new ArrayIndexStore($data());
        });
    } else {
        $this->store = new ArrayIndexStore($data);
    }
}
```

The ghost proxy looks and behaves exactly like the real store object, but the actual instance is only created when a property or method is first accessed. This is a native language feature — once initialized, there is zero overhead compared to a regular object.
