<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

use Closure;
use NoDiscard;

/**
 * Mutable map with in-place mutation methods.
 * Implementations must preserve key types exactly without implicit casting.
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @extends WritableMap<K,V>
 * @method MutableMap<K,V> onEach(Closure(V, K):void $action) Executes the given action for each entry and returns the current map for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 * @method MutableMap<K,V> onEachKey(Closure(K):void $action) Executes the given action for each key and returns the current map for chaining. Use it instead of forEachKey() wherever the result is used - forEachKey() returns void since 0.2.
 * @method MutableMap<K,V> onEachValue(Closure(V):void $action) Executes the given action for each value and returns the current map for chaining. Use it instead of forEachValue() wherever the result is used - forEachValue() returns void since 0.2.
 */
interface MutableMap extends WritableMap
{
	// --- Tracking ---

	/**
	 * Returns a tracked version of this map that tracks whether mutations change the map.
	 * The tracked map shares the same underlying store.
	 *
	 * @return MutableTrackedMap<K,V>
	 */
	#[NoDiscard]
	public function tracked(): MutableTrackedMap;

	// --- Mutation: Add ---

	/**
	 * Returns the current map with the given key/value added at the beginning.
	 * If the key already exists, it is moved to the first position and its value is updated.
	 * If the key is already first with the same value, this is a no-op.
	 *
	 * Note: Mutable maps enforce strict typing - you can only add entries
	 * matching the declared types K and V. Use ImmutableMap if you need type widening.
	 *
	 * @param K $key
	 * @param V $value
	 * @return MutableMap<K,V> The current map.
	 */
	public function putFirst(string|int|bool|float|object $key, mixed $value): MutableMap;

	// --- Mutation: Order ---

	/**
	 * Sorts the map entries based on their keys using an optional selector (Kotlin-like sortedBy).
	 * Example: mapOf($users)->toMutable()->sortByKey(fn($k) => $k->id)
	 *
	 * @template R of mixed
	 * @param Closure(K):R|null $selector Selector to extract comparable from key.
	 * @return MutableMap<K,V> The current map.
	 */
	public function sortByKey(?Closure $selector = null): MutableMap;

	/**
	 * Sorts the map entries based on their keys in descending order using an optional selector.
	 * Example: mapOf($users)->toMutable()->sortByKeyDesc(fn($k) => $k->id)
	 *
	 * @template R of mixed
	 * @param Closure(K):R|null $selector
	 * @return MutableMap<K,V> The current map.
	 */
	public function sortByKeyDesc(?Closure $selector = null): MutableMap;

	/**
	 * Sorts the map entries based on their values using an optional selector.
	 * Example: mapOf($users)->toMutable()->sortByValue(fn($v) => $v->age)
	 *
	 * @template R of mixed
	 * @param Closure(V):R|null $selector Selector to extract comparable from value.
	 * @return MutableMap<K,V> The current map.
	 */
	public function sortByValue(?Closure $selector = null): MutableMap;

	/**
	 * Sorts the map entries based on their values in descending order using an optional selector.
	 * Example: mapOf($users)->toMutable()->sortByValueDesc(fn($v) => $v->age)
	 *
	 * @template R of mixed
	 * @param Closure(V):R|null $selector
	 * @return MutableMap<K,V> The current map.
	 */
	public function sortByValueDesc(?Closure $selector = null): MutableMap;

	/**
	 * Sorts the map entries by a selector applied to each pair (V,K).
	 * Example: mapOf($users)->toMutable()->sortBy(fn($v,$k)=>$v->name)
	 *
	 * @template R of mixed
	 * @param Closure(V, K):R $selector Selector to extract comparable from the pair.
	 * @return MutableMap<K,V> The current map.
	 */
	public function sortBy(Closure $selector): MutableMap;

	/**
	 * Sorts the map entries by a selector applied to each pair (V,K) in descending order.
	 * Example: mapOf($users)->toMutable()->sortByDesc(fn($v,$k)=>$v->name)
	 *
	 * @template R of mixed
	 * @param Closure(V, K):R $selector Selector to extract comparable from the pair.
	 * @return MutableMap<K,V> The current map.
	 */
	public function sortByDesc(Closure $selector): MutableMap;

	/**
	 * Sorts the map entries based on their keys using a comparator.
	 * Example: mapOf($users)->toMutable()->sortWithKey(fn($a,$b)=>$a->id <=> $b->id)
	 *
	 * @param Closure(K, K):int $comparator
	 * @return MutableMap<K,V> The current map.
	 */
	public function sortWithKey(Closure $comparator): MutableMap;

	/**
	 * Sorts the map entries based on their values using a comparator.
	 * Example: mapOf($users)->toMutable()->sortWithValue(fn($a,$b)=>$a->age <=> $b->age)
	 *
	 * @param Closure(V, V):int $comparator
	 * @return MutableMap<K,V> The current map.
	 */
	public function sortWithValue(Closure $comparator): MutableMap;

	/**
	 * Sorts the map entries using a comparator on MapEntry objects.
	 *
	 * @param Closure(MapEntry<K,V>, MapEntry<K,V>): int $comparator
	 * @return MutableMap<K,V> The current map.
	 */
	public function sortWith(Closure $comparator): MutableMap;

	/**
	 * Reverses the order of entries in the map in-place.
	 *
	 * @return MutableMap<K,V> The current map.
	 */
	public function reverse(): MutableMap;

	/**
	 * Shuffles the map entries in-place.
	 *
	 * @return MutableMap<K,V> The current map.
	 */
	public function shuffle(): MutableMap;

	// --- Narrowing Start (auto-generated) ---

	/**
	 * Returns the current map with the given key/value added.
	 *
	 * Note: Mutable maps enforce strict typing - you can only add entries
	 * matching the declared types K and V. Use ImmutableMap if you need type widening.
	 *
	 * @param K $key
	 * @param V $value
	 * @return MutableMap<K,V> The current map.
	 */
	public function put(string|int|bool|float|object $key, mixed $value): MutableMap;

	/**
	 * Puts the given key/value only if the key is not already present.
	 * If the key already exists, this is a no-op.
	 *
	 * @param K $key
	 * @param V $value
	 * @return MutableMap<K,V> The current map.
	 */
	public function putIfAbsent(string|int|bool|float|object $key, mixed $value): MutableMap;

	/**
	 * Adds all entries from the given iterable to the current map.
	 * Example of $data: ['key' => 'value', 'key2' => 'value2']
	 *
	 * Note: Mutable maps enforce strict typing - you can only add entries
	 * matching the declared types K and V. Use ImmutableMap if you need type widening.
	 *
	 * @param iterable<K,V> $data
	 * @return MutableMap<K,V> The current map.
	 */
	public function putAll(iterable $data): MutableMap;

	/**
	 * Adds all entries from the given iterable to the current map.
	 * Example of $data: [['key','value'], ['key2','value2']]
	 *
	 * Note: Mutable maps enforce strict typing - you can only add entries
	 * matching the declared types K and V. Use ImmutableMap if you need type widening.
	 *
	 * @param iterable<array{0:K,1:V}> $data
	 * @return MutableMap<K,V> The current map.
	 */
	public function putAllPairs(iterable $data): MutableMap;

	/**
	 * Removes the entry for the specified key if present.
	 *
	 * @return MutableMap<K,V> The current map.
	 */
	public function remove(string|int|bool|float|object $key): MutableMap;

	/**
	 * Removes all entries that match the given predicate function.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return MutableMap<K,V> The current map.
	 */
	public function removeIf(Closure $predicate): MutableMap;

	/**
	 * Removes all entries whose keys match the given predicate function.
	 *
	 * @param Closure(K):bool $predicate
	 * @return MutableMap<K,V> The current map.
	 */
	public function removeIfKey(Closure $predicate): MutableMap;

	/**
	 * Removes all entries whose values match the given predicate function.
	 *
	 * @param Closure(V):bool $predicate
	 * @return MutableMap<K,V> The current map.
	 */
	public function removeIfValue(Closure $predicate): MutableMap;

	/**
	 * Removes all entries with null values.
	 *
	 * @return MutableMap<K,V> The current map.
	 */
	public function removeNullValues(): MutableMap;

	/**
	 * Removes the first entry from the map.
	 * No-op if empty.
	 *
	 * @return MutableMap<K,V> The current map.
	 */
	public function removeFirst(): MutableMap;

	/**
	 * Removes the last entry from the map.
	 * No-op if empty.
	 *
	 * @return MutableMap<K,V> The current map.
	 */
	public function removeLast(): MutableMap;

	/**
	 * Removes all entries from the map.
	 *
	 * @return MutableMap<K,V> The current map.
	 */
	public function clear(): MutableMap;

	/**
	 * Executes the given action for each entry and returns the map for chaining.
	 *
	 * @param Closure(V, K):void $action
	 * @return MutableMap<K,V> The current map.
	 */
	public function forEach(Closure $action): MutableMap;

	/**
	 * Executes the given action for each key and returns the map for chaining.
	 *
	 * @param Closure(K):void $action
	 * @return MutableMap<K,V> The current map.
	 */
	public function forEachKey(Closure $action): MutableMap;

	/**
	 * Executes the given action for each value and returns the map for chaining.
	 *
	 * @param Closure(V):void $action
	 * @return MutableMap<K,V> The current map.
	 */
	public function forEachValue(Closure $action): MutableMap;

	// --- Narrowing End (auto-generated) ---
}
