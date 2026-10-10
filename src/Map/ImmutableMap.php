<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

use Closure;
use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\Exception\UnsupportedOperationException;
use NoDiscard;

/**
 * Immutable map where mutation methods return new instances.
 * Implementations must preserve key types exactly without implicit casting.
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @extends Map<K,V>
 * @method ImmutableMap<K,V> onEach(Closure(V, K):void $action) Executes the given action for each entry and returns the map for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 * @method ImmutableMap<K,V> onEachKey(Closure(K):void $action) Executes the given action for each key and returns the map for chaining. Use it instead of forEachKey() wherever the result is used - forEachKey() returns void since 0.2.
 * @method ImmutableMap<K,V> onEachValue(Closure(V):void $action) Executes the given action for each value and returns the map for chaining. Use it instead of forEachValue() wherever the result is used - forEachValue() returns void since 0.2.
 */
interface ImmutableMap extends Map
{
	// --- Mutation (returns new) ---

	/**
	 * Returns a new map with the given key/value added.
	 *
	 * Note: Immutable maps allow type widening - you can add entries with different
	 * types, and the returned map will have the union type.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 *
	 * @param NK $key
	 * @param NV $value
	 * @return ImmutableMap<K|NK,V|NV> The new map with the added entry.
	 */
	#[NoDiscard]
	public function put(string|int|bool|float|object $key, mixed $value): ImmutableMap;

	/**
	 * Returns a new map with the given key/value added only if the key is not already present.
	 * If the key already exists, returns the same map unchanged.
	 *
	 * Note: Immutable maps allow type widening - you can add entries with different
	 * types, and the returned map will have the union type.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 *
	 * @param NK $key
	 * @param NV $value
	 * @return ImmutableMap<K|NK,V|NV> The new map with the entry added, or the same map if key exists.
	 */
	#[NoDiscard]
	public function putIfAbsent(string|int|bool|float|object $key, mixed $value): ImmutableMap;

	/**
	 * Returns a new map with the given key/value added at the beginning.
	 * If the key already exists, it is moved to the first position and its value is updated.
	 *
	 * Note: Immutable maps allow type widening - you can add entries with different
	 * types, and the returned map will have the union type.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 *
	 * @param NK $key
	 * @param NV $value
	 * @return ImmutableMap<K|NK,V|NV> The new map with the entry first.
	 */
	#[NoDiscard]
	public function putFirst(string|int|bool|float|object $key, mixed $value): ImmutableMap;

	/**
	 * Returns a new map with the given entries added.
	 * Example of $data: ['key' => 'value', 'key2' => 'value2']
	 *
	 * Note: Immutable maps allow type widening - you can add entries with different
	 * types, and the returned map will have the union type.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 *
	 * @param iterable<NK,NV> $data
	 * @return ImmutableMap<K|NK,V|NV> The new map with the added entries.
	 */
	#[NoDiscard]
	public function putAll(iterable $data): ImmutableMap;

	/**
	 * Returns a new map with the given entries added.
	 * Example of $data: [['key', 'value'], ['key2', 'value2']]
	 *
	 * Note: Immutable maps allow type widening - you can add entries with different
	 * types, and the returned map will have the union type.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 *
	 * @param iterable<array{0:NK,1:NV}> $data
	 * @return ImmutableMap<K|NK,V|NV> The new map with the added entries.
	 */
	#[NoDiscard]
	public function putAllPairs(iterable $data): ImmutableMap;

	/**
	 * Removes the entry for the specified key if present.
	 *
	 * @return ImmutableMap<K,V> The new map without the specified entry.
	 */
	#[NoDiscard]
	public function remove(string|int|bool|float|object $key): ImmutableMap;

	/**
	 * Returns a new map without the first entry.
	 * Returns same map if empty.
	 *
	 * @return ImmutableMap<K,V> The new map without the first entry.
	 */
	#[NoDiscard]
	public function removeFirst(): ImmutableMap;

	/**
	 * Returns a new map without the last entry.
	 * Returns same map if empty.
	 *
	 * @return ImmutableMap<K,V> The new map without the last entry.
	 */
	#[NoDiscard]
	public function removeLast(): ImmutableMap;

	/**
	 * Removes all entries that match the given predicate function.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return ImmutableMap<K,V> The new map without the removed entries.
	 */
	#[NoDiscard]
	public function removeIf(Closure $predicate): ImmutableMap;

	/**
	 * Removes all entries whose keys match the given predicate function.
	 *
	 * @param Closure(K):bool $predicate
	 * @return ImmutableMap<K,V> The new map without the removed entries.
	 */
	#[NoDiscard]
	public function removeIfKey(Closure $predicate): ImmutableMap;

	/**
	 * Removes all entries whose values match the given predicate function.
	 *
	 * @param Closure(V):bool $predicate
	 * @return ImmutableMap<K,V> The new map without the removed entries.
	 */
	#[NoDiscard]
	public function removeIfValue(Closure $predicate): ImmutableMap;

	/**
	 * Removes all entries with null values.
	 *
	 * @return ImmutableMap<K,V> The new map without the removed entries.
	 */
	#[NoDiscard]
	public function removeNullValues(): ImmutableMap;

	// --- Internal ---

	/**
	 * Based on this, IDEs and Static Analysis Tools can understand
	 * that the map is immutable and prevent modifications via ArrayAccess.
	 *
	 * @param K $offset
	 * @param V $value
	 * @throws UnsupportedOperationException
	 */
	public function offsetSet(mixed $offset, mixed $value): void;

	/**
	 * Based on this, IDEs and Static Analysis Tools can understand
	 * that the map is immutable and prevent modifications via ArrayAccess.
	 *
	 * @param K $offset
	 * @throws UnsupportedOperationException
	 */
	public function offsetUnset(mixed $offset): void;

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated You are using toImmutable() on an ImmutableMap!
	 * Same instance will be returned, this deprecation is intended
	 * as a hint that you were not in read-only/mutable context.
	 *
	 * @return ImmutableMap<K,V> The immutable copy of this map.
	 */
	#[NoDiscard]
	public function toImmutable(): ImmutableMap;

	// --- Narrowing Start (auto-generated) ---

	/**
	 * Creates a new map containing only entries that satisfy the predicate.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return ImmutableMap<K,V> New map with filtered entries.
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): ImmutableMap;

	/**
	 * Creates a new map containing only entries whose keys satisfy the predicate.
	 *
	 * @param Closure(K):bool $predicate
	 * @return ImmutableMap<K,V> New map with filtered keys.
	 */
	#[NoDiscard]
	public function filterKeys(Closure $predicate): ImmutableMap;

	/**
	 * Creates a new map containing only entries whose values satisfy the predicate.
	 *
	 * @param Closure(V):bool $predicate
	 * @return ImmutableMap<K,V> New map with filtered values.
	 */
	#[NoDiscard]
	public function filterValues(Closure $predicate): ImmutableMap;

	/**
	 * Creates a new map excluding entries with null values.
	 *
	 * @return ImmutableMap<K, (V is null ? never : V)> New map without null values.
	 */
	#[NoDiscard]
	public function filterValuesNotNull(): ImmutableMap;

	/**
	 * Filter entries whose values are instances of the given class or interface.
	 *
	 * @template T
	 * @param class-string<T> $type
	 * @return ImmutableMap<K,T>
	 */
	#[NoDiscard]
	public function filterValuesInstanceOf(string $type): ImmutableMap;

	/**
	 * Creates a new map by transforming each key using the provided transform function.
	 *
	 * @template NK of string|int|bool|float|object
	 * @param Closure(V, K):NK $transform
	 * @return ImmutableMap<NK,V>
	 */
	#[NoDiscard]
	public function mapKeys(Closure $transform): ImmutableMap;

	/**
	 * Creates a new map by transforming each value using the provided transform function.
	 *
	 * @template NV
	 * @param Closure(V, K):NV $transform
	 * @return ImmutableMap<K,NV>
	 */
	#[NoDiscard]
	public function mapValues(Closure $transform): ImmutableMap;

	/**
	 * Transforms each value using the given function and excludes entries where the result is null.
	 * Combines mapValues and filterValuesNotNull in a single operation.
	 *
	 * @template NV
	 * @param Closure(V, K):(NV|null) $transform
	 * @return ImmutableMap<K,NV>
	 */
	#[NoDiscard]
	public function mapValuesNotNull(Closure $transform): ImmutableMap;

	/**
	 * Returns a new map with keys and values swapped.
	 * Each value in this map becomes a key in the result, and each key becomes a value.
	 *
	 * @param KeyCollisionStrategy $onCollision Strategy for handling duplicate values (which become duplicate keys).
	 * @return ImmutableMap<V,K>
	 * @throws ConversionException When duplicate values exist and strategy is Throw.
	 * @throws InvalidKeyTypeException When a value cannot be used as a map key (e.g. arrays).
	 */
	#[NoDiscard] // @phpstan-ignore generics.notSubtype
	public function flip(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): ImmutableMap;

	/**
	 * Returns a new map with entries sorted by key using an optional selector.
	 * Example: mapOf($users)->sortedByKey(fn($k) => $k->id)
	 *
	 * @template R of mixed
	 * @param Closure(K):R|null $selector Selector to extract comparable from the key.
	 * @return ImmutableMap<K,V> New map with entries sorted by key.
	 */
	#[NoDiscard]
	public function sortedByKey(?Closure $selector = null): ImmutableMap;

	/**
	 * Returns a new map with entries sorted by key in descending order using an optional selector.
	 * Example: mapOf($users)->sortedByKeyDesc(fn($k) => $k->id)
	 *
	 * @template R of mixed
	 * @param Closure(K):R|null $selector
	 * @return ImmutableMap<K,V> New map with entries sorted by key in descending order.
	 */
	#[NoDiscard]
	public function sortedByKeyDesc(?Closure $selector = null): ImmutableMap;

	/**
	 * Returns a new map with entries sorted by value using an optional selector.
	 * Example: mapOf($users)->sortedByValue(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(V):R|null $selector Selector to extract comparable from the value.
	 * @return ImmutableMap<K,V> New map with entries sorted by value.
	 */
	#[NoDiscard]
	public function sortedByValue(?Closure $selector = null): ImmutableMap;

	/**
	 * Returns a new map with entries sorted by value in descending order using an optional selector.
	 * Example: mapOf($users)->sortedByValueDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(V):R|null $selector
	 * @return ImmutableMap<K,V> New map with entries sorted by value in descending order.
	 */
	#[NoDiscard]
	public function sortedByValueDesc(?Closure $selector = null): ImmutableMap;

	/**
	 * Returns a new map with entries sorted by a selector applied to each pair (V,K).
	 * Example: mapOf($users)->sortedBy(fn($v,$k) => $v->name)
	 *
	 * @template R of mixed
	 * @param Closure(V, K):R $selector Selector to extract comparable from the pair.
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): ImmutableMap;

	/**
	 * Returns a new map with entries sorted by a selector applied to each pair (V,K) in descending order.
	 * Example: mapOf($users)->sortedByDesc(fn($v,$k) => $v->name)
	 *
	 * @template R of mixed
	 * @param Closure(V, K):R $selector Selector to extract comparable from the pair.
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): ImmutableMap;

	/**
	 * Returns a new map with entries sorted by their keys using a comparator.
	 * Example: mapOf($users)->sortedWithKey(fn($k1,$k2) => strcmp($k1, $k2))
	 *
	 * @param Closure(K, K):int $comparator
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedWithKey(Closure $comparator): ImmutableMap;

	/**
	 * Returns a new map with entries sorted by their values using a comparator.
	 * Example: mapOf($users)->sortedWithValue(fn($u1,$u2) => strcmp($u1->name, $u2->name))
	 *
	 * @param Closure(V, V):int $comparator
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedWithValue(Closure $comparator): ImmutableMap;

	/**
	 * Returns a new map with entries sorted using a comparator on MapEntry objects.
	 *
	 * @param Closure(MapEntry<K,V>, MapEntry<K,V>): int $comparator
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): ImmutableMap;

	/**
	 * Returns a new map with entries in reversed order.
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function reversed(): ImmutableMap;

	/**
	 * Returns a new map with entries in random order.
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function shuffled(): ImmutableMap;

	/**
	 * Returns a new map with the first N entries.
	 *
	 * @param int $n Number of entries to take.
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): ImmutableMap;

	/**
	 * Returns a new map with the last N entries.
	 *
	 * @param int $n Number of entries to take.
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): ImmutableMap;

	/**
	 * Returns a new map without the first N entries.
	 *
	 * @param int $n Number of entries to drop.
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): ImmutableMap;

	/**
	 * Returns a new map without the last N entries.
	 *
	 * @param int $n Number of entries to drop.
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): ImmutableMap;

	/**
	 * Takes entries from the beginning while the predicate is true.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): ImmutableMap;

	/**
	 * Drops entries from the beginning while the predicate is true, then returns the rest.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): ImmutableMap;

	/**
	 * Takes entries from the end while the predicate is true.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): ImmutableMap;

	/**
	 * Drops entries from the end while the predicate is true, then returns the rest.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): ImmutableMap;

	/**
	 * Executes the given action for each entry and returns the map for chaining.
	 *
	 * @param Closure(V, K):void $action
	 * @return ImmutableMap<K,V>
	 */
	public function forEach(Closure $action): ImmutableMap;

	/**
	 * Executes the given action for each key and returns the map for chaining.
	 *
	 * @param Closure(K):void $action
	 * @return ImmutableMap<K,V>
	 */
	public function forEachKey(Closure $action): ImmutableMap;

	/**
	 * Executes the given action for each value and returns the map for chaining.
	 *
	 * @param Closure(V):void $action
	 * @return ImmutableMap<K,V>
	 */
	public function forEachValue(Closure $action): ImmutableMap;

	// --- Narrowing End (auto-generated) ---
}
