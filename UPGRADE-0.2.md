# Upgrading from 0.1 to 0.2

Most code upgrades without changes. The one break that ordinary calling code is likely to hit is the
`forEach()` return type; the rest only concern code that extends or implements the library's types.

**Prepare on 0.1.8 first.** 0.1.8 already ships `onEach()`, `onEachKey()` and `onEachValue()` and flags
`mapOfPairs()` called without arguments, so you can fix everything below while still on 0.1, then
bump the constraint to `^0.2` with no further changes.

## `forEach()` returns `void`

`forEach()` on every collection, and `forEach()`, `forEachKey()` and `forEachValue()` on every map,
no longer return the receiver. Chaining moved to the new `onEach()`, `onEachKey()` and `onEachValue()`,
which behave exactly like the 0.1 `forEach*()` did.

```php
// 0.1
$active = $users->forEach(fn (User $u) => $u->touch())->filter(fn (User $u) => $u->isActive());
$map = $map->forEachValue(fn (int $v) => log($v));

// 0.1.8 and 0.2
$active = $users->onEach(fn (User $u) => $u->touch())->filter(fn (User $u) => $u->isActive());
$map = $map->onEachValue(fn (int $v) => log($v));
```

Only calls whose result is used need changing; a `forEach()` used as a plain statement works as before.
To find them, run PHPStan after upgrading: every affected call is reported as
`Result of method ...::forEach() (void) is used`. Without static analysis they fail at runtime with
`Call to a member function ... on null`.

If you extend a library class and override `forEach()`, `forEachKey()` or `forEachValue()`, the
override must now declare `: void`. Override `onEach*()` instead if you need to intercept chaining.

## `mapOfPairs()` requires its argument

`mapOfPairs()` with no arguments is gone, matching `mutableMapOfPairs()`. Use `mapOf()` for an empty map.
0.1.8 triggers an `E_USER_DEPRECATED` for the no-argument call.

```php
// 0.1
$map = mapOfPairs();

// 0.1.8 and 0.2
$map = mapOf();
```

## `MapLogic` declares `$store`

`MapLogic` now declares `protected KeyValueStore $store` once for every map variant. In 0.1,
`ImmutableMapLogic` declared it `protected`, `MutableMapLogic` and `MutableTrackedMapLogic` declared
it `private`, and `MapLogic` alone did not declare it at all.

Custom maps that only assign the store, as the extending guide shows, are unaffected:

```php
final class Settings implements ImmutableMap
{
    use ImmutableMapLogic;

    public function __construct(array $data)
    {
        $this->store = StringKeyValueStore::fromAssoc($data); // any KeyValueStore implementation
    }
}
```

A declaration of `$store` in your own class now fails with an incompatible-property error unless it
is exactly `protected KeyValueStore $store;`. That hits:

- a read-only `implements Map` + `use MapLogic` class, which had to declare `$store` itself in 0.1,
  if it declared it `private` or with a narrower type such as `StringKeyValueStore`;
- a class using `MutableMapLogic` or `MutableTrackedMapLogic` that redeclared the `private` property;
- a subclass of a mutable library map, e.g. `MutableHashMap`, that declares a `$store` of its own.

Remove the declaration and inherit `$store` from the trait. Classes using `ImmutableMapLogic` see no
change, since it already declared the property exactly this way.

## New interface methods

Classes that implement the interfaces directly, without the library's logic traits
(`CollectionLogic`, `ListLogic`, `SetLogic`, `MapLogic` and their variants), must add:

| Interface | Methods |
|---|---|
| `Collection` and every sub-interface | `elementAt()`, `elementAtOrNull()`, `asSequence()`, `onEach()`, and `forEach()` as `: void` |
| `Map` and every sub-interface | `onEach()`, `onEachKey()`, `onEachValue()`, and `forEach()`, `forEachKey()`, `forEachValue()` as `: void` |

Each `onEach*()` is narrowed on the sub-interfaces the same way the 0.1 `forEach*()` was, e.g.
`ImmutableList::onEach(): ImmutableList`, `MutableTrackedMap::onEach(): MutableTrackedMap&TrackedResult`.
Classes using the traits get all of them for free.

## Behaviour changes

These do not break code written against the 0.1 documentation, but can be observed:

- `zip()` walks the other iterable in lockstep instead of copying it upfront. A `Generator` passed in is
  consumed only up to the shorter length, and one that was already advanced now resumes from where it
  is instead of throwing. An iterator that cannot be rewound surfaces as `NonReplayableSourceException`
  (a subclass of `UnsupportedOperationException`) with the original exception as its previous one.
- Every exception the library throws implements the new `Noctud\Collection\Exception\NoctudCollectionException`,
  so a single `catch` covers them all. Existing `catch` blocks keep working.
