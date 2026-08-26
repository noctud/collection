# Extending

The collection library is designed for extensibility. All collection behavior is provided through **traits**, not abstract classes. This means your custom classes are free to extend any class they need — a Doctrine entity, a framework base class, or nothing at all. You implement the interface, use the trait, and get the full collection API without being locked into an inheritance hierarchy.

## Adding Collection Behavior to Existing Classes

Turn any class into a collection by implementing the interface and using a logic trait. This is useful when you want domain objects to expose collection-like access without wrapping them:

```php
use Noctud\Collection\Map\Map;
use Noctud\Collection\Map\ImmutableMapLogic;
use Noctud\Collection\Map\StringMap\StringKeyValueStore;

final class JsonBody extends Body implements ImmutableMap
{
    use ImmutableMapLogic;

    public function __construct(public readonly string $json)
    {
        $this->store = StringKeyValueStore::fromAssoc(json_decode($json, true));
    }
}

$body = new JsonBody('{"name": "Rodney", "age": 38, "city": null}');
$body['name']; // "Rodney" - throws if missing
$body['name'] ?? null; // "Rodney" - null if missing
$body->count(); // 3
$body->values->last(); // null
```

The class is now a full `Map` — it can be passed to any function expecting a map, used in `foreach`, and has all the filtering/transformation methods.

::: tip Why Map instead of ImmutableMap?
`Map` is the read-only base interface — the widest type hint. Accepting `Map` in function signatures means the function works with mutable, immutable, and custom maps alike. Use `ImmutableMap` or `MutableMap` when you need their specific guarantees.
:::

That's it — one interface, one trait, and a store. The trait provides the entire collection API (`filter()`, `sorted()`, `forEach()`, `toArray()`, etc.), and the store handles raw data access.

## Self-Preserving Collections

By default, transformation methods return the **base** interface type. A custom `OrderItemCollection` that uses `ImmutableSetLogic` will have `filter()` typed as `ImmutableSet<OrderItem>`, not `OrderItemCollection`:

```php
class OrderItemCollection implements ImmutableSet
{
    use ImmutableSetLogic;

    public function onlySwapped(): self
    {
        // PHPStan error: filter() returns ImmutableSet<OrderItem>, not OrderItemCollection
        return $this->filter(static fn (OrderItem $i) => $i->isSwapped());
    }
}
```

This is intentional — the base type is the safe default for classes with structural invariants (the `JsonBody` example above can't be rebuilt from an arbitrary filtered subset). When your class **is** a thin typed wrapper that should return itself, opt in to self-preservation. There are two ways.

### Option 1: the SelfPreserving\*Logic trait (recommended)

Swap the logic trait for its self-preserving variant. Every shape-preserving operation now returns your own type — no annotations, no factory override. The only requirement is a constructor that accepts an `iterable`:

```php
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\SelfPreservingImmutableSetLogic;
use Noctud\Collection\Set\HashSet\HashElementStore;

/**
 * @implements ImmutableSet<OrderItem>
 * @phpstan-consistent-constructor
 */
class OrderItemCollection implements ImmutableSet
{
    /** @use SelfPreservingImmutableSetLogic<OrderItem> */
    use SelfPreservingImmutableSetLogic;

    /** @param iterable<OrderItem> $data */
    public function __construct(iterable $data = [])
    {
        $this->store = new HashElementStore($data);
    }

    public function onlySwapped(): self // ✅ filter() now returns OrderItemCollection
    {
        return $this->filter(static fn (OrderItem $i) => $i->isSwapped());
    }
}
```

The variants are `SelfPreservingImmutableSetLogic`, `SelfPreservingImmutableListLogic`, and `SelfPreservingImmutableMapLogic`. The map variant builds derived maps with `new static(...)`, so its constructor should populate the store from the iterable (e.g. `HashKeyValueStore::fromAssoc($data)`).

::: tip Use `self` or `static` in your own methods
The trait narrows to `static` (late static binding) so subclasses stay precise. In your own domain methods you can return `self` (your concrete class, as above) or `static` (if you expect subclasses to keep their type). Returning the trait's `static` into a `: self` declaration is always safe.
:::

### Option 2: class-level `@method` overrides

If you'd rather keep the base trait, declare `@method` overrides on the class and override the factory. Use your **concrete** element type in the annotations so element and closure types stay checked:

```php
/**
 * @implements ImmutableSet<OrderItem>
 * @phpstan-consistent-constructor
 * @method self filter(Closure(OrderItem, int): bool $predicate)
 * @method self sorted()
 * @method array{self, self} partition(Closure(OrderItem, int): bool $predicate)
 */
class OrderItemCollection implements ImmutableSet
{
    use ImmutableSetLogic;

    /** @param iterable<OrderItem> $data */
    protected function newCollectionOf(iterable $data): ImmutableSet
    {
        return new self($data);
    }

    // constructor ...
}
```

Add `@method` lines only for methods that genuinely keep the same shape. This is more verbose but useful when you only need a few methods narrowed, or when you can't add a constructor accepting an `iterable`.

### What is and isn't narrowed

Self-preservation applies only where the result is still a collection of the same element type:

| Narrowed to your type | Left as the base type |
|-----------------------|------------------------|
| `filter`, `filterNotNull`, `filterKeys`/`filterValues` (Map) | `map`, `mapNotNull`, `flatMap`, `flatten`, `mapKeys`/`mapValues` (Map) |
| `sorted*`, `reversed`, `shuffled` | `filterInstanceOf`, `filterValuesInstanceOf` (Map), `flip` (Map) |
| `take*`/`drop*`, `distinct`/`distinctBy`, `slice` (List) | `groupBy` (`ImmutableMap`'s value type is invariant) |
| `partition` → `array{static, static}` | `keys`/`values`/`entries` views, `toList`/`toSet`/`toMutable`/… conversions |
| set operations `intersect`/`union`/`subtract` (Set only) | `intersect`/`union`/`subtract` on a **List** (they produce a Set) |
| immutable mutations (`add`, `put`, `remove*`, …) | |

::: warning Mutations become strict
The base immutable mutations widen their type — `add(NE): ImmutableSet<E|NE>`. The self-preserving variant is strict instead — `add(E): static` — because a fixed-type collection cannot widen its element type while still being itself.
:::

::: tip Constructor invariants are safe
The right column is not just a static-type matter: transforms that produce new element values (`map`, `mapNotNull`, `flatMap`, `flatten`, and the Map key/value transforms) build a plain base collection at runtime as well. Your constructor is only ever re-entered with elements of your own type, so it can safely validate an invariant (e.g. "every element is an `OrderItem`").
:::

::: tip Why immutable only?
There is no `SelfPreserving` variant for mutable collections. Their mutation methods (`add`, `remove`, …) already return `static`, and their transformation methods (`filter`, `map`, …) always return **immutable** results — so "return my own mutable type" never applies. Self-preservation is meaningful only for immutable collections.
:::

## Logic Traits

The library provides these logic traits:

| Trait | Purpose |
|-------|---------|
| `CollectionLogic` | Read-only collection behavior |
| `ListLogic` | Read-only list behavior |
| `SetLogic` | Read-only set behavior |
| `MapLogic` | Read-only map behavior |
| `MutableListLogic` | Full mutable list behavior |
| `MutableSetLogic` | Full mutable set behavior |
| `MutableMapLogic` | Full mutable map behavior |
| `ImmutableListLogic` | Full immutable list behavior |
| `ImmutableSetLogic` | Full immutable set behavior |
| `ImmutableMapLogic` | Full immutable map behavior |
| `SelfPreservingImmutableListLogic` | Immutable list behavior narrowed to your own subtype (see [Self-Preserving Collections](#self-preserving-collections)) |
| `SelfPreservingImmutableSetLogic` | Immutable set behavior narrowed to your own subtype |
| `SelfPreservingImmutableMapLogic` | Immutable map behavior narrowed to your own subtype |

These traits contain all the methods defined on the interfaces — `filter()`, `sorted()`, `forEach()`, and so on. The store provides the raw data access, and the trait provides the high-level operations.

## Custom Store (When Using Traits)

If you're using the library's logic traits, you need to provide a store that handles low-level data access. The store is where actual data lives — the trait delegates all reads and writes to it.

| Interface | Used by |
|-----------|---------|
| `KeyValueStore` | Maps |
| `ReadWriteIndexedStore` | Lists |
| `ReadWriteElementStore` | Sets |

::: warning Stores are internal to traits
Store interfaces exist **only** to support the built-in logic traits. They are not part of the public collection API. If you implement `Map`, `List`, or `Set` directly without using traits, you don't need stores at all — use whatever internal data structure suits your needs.
:::

### Built-in Stores

The library provides these store implementations that you can reuse directly or wrap in your own store:

| Store | Purpose |
|-------|---------|
| `HashKeyValueStore` | Map storage with support for any key type (strings, objects, etc.) |
| `StringKeyValueStore` | Map storage optimized for string keys |
| `IntKeyValueStore` | Map storage optimized for integer keys |
| `ArrayIndexStore` | List storage backed by a PHP array |
| `HashElementStore` | Set storage with hash-based uniqueness |

### Example: Custom Store with Caching

```php
use Noctud\Collection\Map\HashMap\HashKeyValueStore;
use Noctud\Collection\Store\KeyValueStore;

final class CachedDatabaseStore implements KeyValueStore
{
    private HashKeyValueStore $cache;

    public function __construct(private Connection $db)
    {
        $this->cache = HashKeyValueStore::fromAssoc([]);
    }

    public function get(mixed $key): mixed
    {
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $value = $this->db->fetch($key);
        $this->cache->put($key, $value);

        return $value;
    }

    // Delegate other methods to cache or database...
}
```

### Example: Custom Map with Custom Store

```php
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Map\MutableMapLogic;

final class MyDatabaseMap implements MutableMap
{
    use MutableMapLogic;

    public function __construct(array $source, private Connection $connection)
    {
        $this->store = new CachedDatabaseStore($source, $connection);
    }
}
```

Optionally override `newMapOf` (and `newCollectionOf` for collections) so that derived maps (from `filter()`, `mapKeys()`, etc.) are also of the custom type. The method must return an `ImmutableMap` (default implementation returns `ImmutableHashMap`).

## Creating Live Views

You can create read-only views that stay synchronized with an underlying collection. The library uses this pattern for `$map->keys`, `$map->values`, and `$map->entries`.

To create a live view, implement the read-only interface and use the appropriate base Logic trait:

```php
use Noctud\Collection\Set\Set;
use Noctud\Collection\Set\SetLogic;
use Noctud\Collection\Store\KeyValueStore;

final class MapKeySet implements Set
{
    use SetLogic;

    public function __construct(KeyValueStore $store)
    {
        $this->store = new KeysAsElementStore($store);
    }
}
```

This view:
- Implements `Set` (the read-only interface)
- Uses `SetLogic` which provides all transformation methods
- Delegates to the map's store, so changes to the map are immediately visible through the view
- All transformation methods (`filter()`, `toList()`, etc.) return immutable collections

The same view class works for both mutable and immutable sources — if the underlying map is mutable, changes are reflected in the view; if immutable, the view never changes.

## Implementing Without Traits

You can implement `Map`, `List`, or `Set` directly without using the library's traits or stores. This gives you complete control over the implementation:

```php
use Noctud\Collection\Map\ImmutableMap;

final class MyCustomMap implements ImmutableMap
{
    private array $data = [];

    public function get(mixed $key): mixed
    {
        return $this->data[$key] ?? throw new KeyNotFoundException($key);
    }

    public function getOrNull(mixed $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    // Implement all other Map methods...
    // No stores, no traits — just your own logic
}
```

This approach is useful when:
- You need behavior that doesn't fit the trait's assumptions
- You're wrapping an external data source with its own query API
- You want maximum control over performance characteristics

The trade-off is that you must implement all interface methods yourself, including `filter()`, `map()`, `sorted()`, and the rest of the collection API.

## Store Interfaces (Reference)

For completeness, here's the store interface hierarchy used internally by the logic traits:

```
ReadOnlyElementStore         → count, contains, iterate
├── ReadWriteElementStore    → + add, remove, clear

ReadOnlyIndexedStore      → count, get, iterate
├── ReadWriteIndexedStore → + set, add, remove, clear

KeyValueStore             → count, get, has, put, remove, clear, iterate
```

These are **internal implementation details**. Most users will never need to interact with them directly — they exist to support the logic traits and allow swapping storage backends while keeping the same collection behavior.
