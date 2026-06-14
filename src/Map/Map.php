<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

use ArrayAccess;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Noctud\Collection\Collection;
use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Set\Set;
use NoDiscard;

/**
 * Key-value associative collection with strict key handling.
 * Every implementation of this interface must preserve key types exactly as provided,
 * without any implicit casting (e.g., string "1" must not become int 1).
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @extends IteratorAggregate<K,V>
 * @extends ArrayAccess<K,V>
 */
interface Map extends IteratorAggregate, Countable, ArrayAccess, JsonSerializable
{
	// --- Properties ---

	/**
	 * Returns a read-only live view of the map's keys.
	 *
	 * @var Set<K>
	 */
	public Set $keys {
		get;
	}

	/**
	 * Returns a read-only live view of the map's values.
	 *
	 * @var Collection<V>
	 */
	public Collection $values {
		get;
	}

	/**
	 * Returns a read-only live view of the map's entries.
	 *
	 * In the default map implementation, MapEntry is a snapshot
	 * of the key/value at the time of retrieval.
	 *
	 * @var Set<MapEntry<K,V>>
	 */
	public Set $entries {
		get;
	}

	// --- Element Access ---

	/**
	 * Retrieves the value associated with the given key.
	 * Throws an exception if the key does not exist in the map.
	 * Array access syntax `$map['key']` is an alias for this method.
	 *
	 * @param K $key
	 * @return V
	 * @throws NoSuchElementException
	 */
	public function get(string|int|bool|float|object $key);

	/**
	 * Retrieves the value associated with the given key.
	 * Returns null if the key does not exist in the map.
	 * Use `$map['key'] ?? null` for the same behavior via array access.
	 *
	 * @param K $key
	 * @return V|null
	 */
	public function getOrNull(string|int|bool|float|object $key): mixed;

	/**
	 * Retrieves the value associated with the given key.
	 * Returns the provided default value if the key does not exist in the map.
	 *
	 * @template D
	 * @param K $key
	 * @param D $default
	 * @return V|D
	 */
	public function getOrDefault(string|int|bool|float|object $key, mixed $default): mixed;

	/**
	 * Retrieves the value associated with the given key.
	 * If the key does not exist, computes the value using the provided callback.
	 *
	 * @template D
	 * @param K $key
	 * @param Closure():D $compute
	 * @return V|D
	 */
	public function getOrCompute(string|int|bool|float|object $key, Closure $compute): mixed;

	// --- Querying ---

	/**
	 * Whether the map does not contain any entries.
	 */
	public function isEmpty(): bool;

	/**
	 * Whether the map contains at least one entry.
	 */
	public function isNotEmpty(): bool;

	/**
	 * Checks if the map contains the specified key.
	 */
	public function containsKey(string|int|bool|float|object $key): bool;

	/**
	 * Checks if the map contains the specified value using strict comparison.
	 */
	public function containsValue(mixed $value): bool;

	/**
	 * Tests if all entries satisfy the given predicate.
	 *
	 * @param Closure(V, K):bool $predicate
	 */
	public function all(Closure $predicate): bool;

	/**
	 * Tests if any entry satisfies the given predicate.
	 *
	 * @param Closure(V, K):bool $predicate
	 */
	public function any(Closure $predicate): bool;

	/**
	 * Tests if no entry satisfies the given predicate.
	 *
	 * @param Closure(V, K):bool $predicate
	 */
	public function none(Closure $predicate): bool;

	/**
	 * Returns the number of entries in the map.
	 */
	public function count(): int;

	/**
	 * Returns the number of entries matching the predicate.
	 *
	 * @param Closure(V, K):bool $predicate
	 */
	public function countWhere(Closure $predicate): int;

	// --- Transformation ---

	/**
	 * Creates a new map containing only entries that satisfy the predicate.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return Map<K,V> New map with filtered entries.
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): Map;

	/**
	 * Creates a new map containing only entries whose keys satisfy the predicate.
	 *
	 * @param Closure(K):bool $predicate
	 * @return Map<K,V> New map with filtered keys.
	 */
	#[NoDiscard]
	public function filterKeys(Closure $predicate): Map;

	/**
	 * Creates a new map containing only entries whose values satisfy the predicate.
	 *
	 * @param Closure(V):bool $predicate
	 * @return Map<K,V> New map with filtered values.
	 */
	#[NoDiscard]
	public function filterValues(Closure $predicate): Map;

	/**
	 * Creates a new map excluding entries with null values.
	 *
	 * @return Map<K,V> New map without null values.
	 */
	#[NoDiscard]
	public function filterValuesNotNull(): Map;

	/**
	 * Filter entries whose values are instances of the given class or interface.
	 *
	 * @template T
	 * @param class-string<T> $type
	 * @return Map<K,T>
	 */
	#[NoDiscard]
	public function filterValuesInstanceOf(string $type): Map;

	/**
	 * Creates a new map by transforming each key using the provided transform function.
	 *
	 * @template NK of string|int|bool|float|object
	 * @param Closure(V, K):NK $transform
	 * @return Map<NK,V>
	 */
	#[NoDiscard]
	public function mapKeys(Closure $transform): Map;

	/**
	 * Creates a new map by transforming each value using the provided transform function.
	 *
	 * @template NV
	 * @param Closure(V, K):NV $transform
	 * @return Map<K,NV>
	 */
	#[NoDiscard]
	public function mapValues(Closure $transform): Map;

	/**
	 * Transforms each value using the given function and excludes entries where the result is null.
	 * Combines mapValues and filterValuesNotNull in a single operation.
	 *
	 * @template NV
	 * @param Closure(V, K):(NV|null) $transform
	 * @return Map<K,NV>
	 */
	#[NoDiscard]
	public function mapValuesNotNull(Closure $transform): Map;

	/**
	 * Returns a new map with keys and values swapped.
	 * Each value in this map becomes a key in the result, and each key becomes a value.
	 *
	 * @param KeyCollisionStrategy $onCollision Strategy for handling duplicate values (which become duplicate keys).
	 * @return Map<V,K>
	 * @throws ConversionException When duplicate values exist and strategy is Throw.
	 * @throws InvalidKeyTypeException When a value cannot be used as a map key (e.g. arrays).
	 */
	#[NoDiscard] // @phpstan-ignore generics.notSubtype
	public function flip(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): Map;

	// --- Ordering ---

	/**
	 * Returns a new map with entries sorted by key using an optional selector.
	 * Example: mapOf($users)->sortedByKey(fn($k) => $k->id)
	 *
	 * @template R of mixed
	 * @param Closure(K):R|null $selector Selector to extract comparable from the key.
	 * @return Map<K,V> New map with entries sorted by key.
	 */
	#[NoDiscard]
	public function sortedByKey(?Closure $selector = null): Map;

	/**
	 * Returns a new map with entries sorted by key in descending order using an optional selector.
	 * Example: mapOf($users)->sortedByKeyDesc(fn($k) => $k->id)
	 *
	 * @template R of mixed
	 * @param Closure(K):R|null $selector
	 * @return Map<K,V> New map with entries sorted by key in descending order.
	 */
	#[NoDiscard]
	public function sortedByKeyDesc(?Closure $selector = null): Map;

	/**
	 * Returns a new map with entries sorted by value using an optional selector.
	 * Example: mapOf($users)->sortedByValue(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(V):R|null $selector Selector to extract comparable from the value.
	 * @return Map<K,V> New map with entries sorted by value.
	 */
	#[NoDiscard]
	public function sortedByValue(?Closure $selector = null): Map;

	/**
	 * Returns a new map with entries sorted by value in descending order using an optional selector.
	 * Example: mapOf($users)->sortedByValueDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(V):R|null $selector
	 * @return Map<K,V> New map with entries sorted by value in descending order.
	 */
	#[NoDiscard]
	public function sortedByValueDesc(?Closure $selector = null): Map;

	/**
	 * Returns a new map with entries sorted by a selector applied to each pair (V,K).
	 * Example: mapOf($users)->sortedBy(fn($v,$k) => $v->name)
	 *
	 * @template R of mixed
	 * @param Closure(V, K):R $selector Selector to extract comparable from the pair.
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): Map;

	/**
	 * Returns a new map with entries sorted by a selector applied to each pair (V,K) in descending order.
	 * Example: mapOf($users)->sortedByDesc(fn($v,$k) => $v->name)
	 *
	 * @template R of mixed
	 * @param Closure(V, K):R $selector Selector to extract comparable from the pair.
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): Map;

	/**
	 * Returns a new map with entries sorted by their keys using a comparator.
	 * Example: mapOf($users)->sortedWithKey(fn($k1,$k2) => strcmp($k1, $k2))
	 *
	 * @param Closure(K, K):int $comparator
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function sortedWithKey(Closure $comparator): Map;

	/**
	 * Returns a new map with entries sorted by their values using a comparator.
	 * Example: mapOf($users)->sortedWithValue(fn($u1,$u2) => strcmp($u1->name, $u2->name))
	 *
	 * @param Closure(V, V):int $comparator
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function sortedWithValue(Closure $comparator): Map;

	/**
	 * Returns a new map with entries sorted using a comparator on MapEntry objects.
	 *
	 * @param Closure(MapEntry<K,V>, MapEntry<K,V>): int $comparator
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): Map;

	/**
	 * Returns a new map with entries in reversed order.
	 *
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function reversed(): Map;

	/**
	 * Returns a new map with entries in random order.
	 *
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function shuffled(): Map;

	// --- Slicing ---

	/**
	 * Returns a new map with the first N entries.
	 *
	 * @param int $n Number of entries to take.
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): Map;

	/**
	 * Returns a new map with the last N entries.
	 *
	 * @param int $n Number of entries to take.
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): Map;

	/**
	 * Returns a new map without the first N entries.
	 *
	 * @param int $n Number of entries to drop.
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): Map;

	/**
	 * Returns a new map without the last N entries.
	 *
	 * @param int $n Number of entries to drop.
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): Map;

	/**
	 * Takes entries from the beginning while the predicate is true.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): Map;

	/**
	 * Drops entries from the beginning while the predicate is true, then returns the rest.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): Map;

	/**
	 * Takes entries from the end while the predicate is true.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): Map;

	/**
	 * Drops entries from the end while the predicate is true, then returns the rest.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return Map<K,V>
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): Map;

	// --- Iteration ---

	/**
	 * Executes the given action for each entry and returns the map for chaining.
	 *
	 * @param Closure(V, K):void $action
	 * @return Map<K,V>
	 */
	public function forEach(Closure $action): Map;

	/**
	 * Executes the given action for each key and returns the map for chaining.
	 *
	 * @param Closure(K):void $action
	 * @return Map<K,V>
	 */
	public function forEachKey(Closure $action): Map;

	/**
	 * Executes the given action for each value and returns the map for chaining.
	 *
	 * @param Closure(V):void $action
	 * @return Map<K,V>
	 */
	public function forEachValue(Closure $action): Map;

	// --- Conversion ---

	/**
	 * Converts the map to a native PHP array, only if all keys are of a scalar or null type.
	 *
	 * Object keys will throw ConversionException
	 * Duplicate keys (string "1" and int 1) will throw ConversionException
	 * Float keys with precision loss (e.g. 1.5) will throw ConversionException
	 *
	 * Collisions can only occur with mixed types or floats. A Map<string, V> with only
	 * string keys will never throw — PHP only casts strings that look exactly like integers.
	 *
	 * Conversion works exactly as the keys were written to an array.
	 * - null → '' (empty string)
	 * - true → 1, false → 0
	 * - float → int (only if no precision loss, e.g. 1.0 → 1; but 1.5 throws)
	 * - string digits will become int ('1' → 1)
	 * - Objects are not supported
	 *
	 * @param KeyCollisionStrategy $onCollision Strategy for handling key collisions during conversion.
	 * @return array<array-key,V>
	 * @throws ConversionException
	 */
	#[NoDiscard]
	public function toArray(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): array;

	/**
	 * Transforms each entry using the given function and returns the results as a list.
	 *
	 * @template R
	 * @param Closure(V, K):R $transform
	 * @return ImmutableList<R>
	 */
	#[NoDiscard]
	public function map(Closure $transform): ImmutableList;

	/**
	 * Transforms each entry using the given function and excludes null results.
	 * Combines map and filterNotNull in a single operation.
	 *
	 * @template R
	 * @param Closure(V, K):(R|null) $transform
	 * @return ImmutableList<R>
	 */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): ImmutableList;

	/**
	 * Transforms each entry into an iterable and flattens the results into a single list.
	 *
	 * @template R
	 * @param Closure(V, K):iterable<R> $transform
	 * @return ImmutableList<R>
	 */
	#[NoDiscard]
	public function flatMap(Closure $transform): ImmutableList;

	/**
	 * Converts the map to an array of pairs like [[key1, value1], [key2, value2], ...].
	 *
	 * @return list<array{0:K,1:V}>
	 */
	#[NoDiscard]
	public function toPairs(): array;

	/**
	 * Always creates and returns in-memory mutable copy of this map with the same entries.
	 * Even if this map is already mutable, a new copy is created.
	 *
	 * @return MutableMap<K,V> The mutable copy of this map.
	 */
	#[NoDiscard]
	public function toMutable(): MutableMap;

	/**
	 * Returns an immutable map.
	 * If this map is already immutable, it may return itself.
	 *
	 * @return ImmutableMap<K,V> The immutable copy of this map.
	 */
	#[NoDiscard]
	public function toImmutable(): ImmutableMap;

	// --- Internal ---

	/**
	 * @param K $offset
	 * @param V $value
	 */
	public function offsetSet(mixed $offset, mixed $value): void;

	/**
	 * @param K $offset
	 */
	public function offsetUnset(mixed $offset): void;
}
