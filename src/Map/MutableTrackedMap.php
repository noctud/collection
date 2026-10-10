<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

use Closure;
use Noctud\Collection\TrackedResult;

/**
 * A mutable map that tracks whether the last mutation operation changed the map.
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @extends MutableMap<K,V>
 * @extends WritableTrackedMap<K,V>
 * @method MutableTrackedMap<K,V>&TrackedResult onEach(Closure(V, K):void $action) Executes the given action for each entry and returns the current map for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 * @method MutableTrackedMap<K,V>&TrackedResult onEachKey(Closure(K):void $action) Executes the given action for each key and returns the current map for chaining. Use it instead of forEachKey() wherever the result is used - forEachKey() returns void since 0.2.
 * @method MutableTrackedMap<K,V>&TrackedResult onEachValue(Closure(V):void $action) Executes the given action for each value and returns the current map for chaining. Use it instead of forEachValue() wherever the result is used - forEachValue() returns void since 0.2.
 */
interface MutableTrackedMap extends MutableMap, WritableTrackedMap
{
	// --- Narrowing Start (auto-generated) ---

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
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function putFirst(string|int|bool|float|object $key, mixed $value): MutableTrackedMap&TrackedResult;

	/**
	 * Sorts the map entries based on their keys using an optional selector (Kotlin-like sortedBy).
	 * Example: mapOf($users)->toMutable()->sortByKey(fn($k) => $k->id)
	 *
	 * @template R of mixed
	 * @param Closure(K):R|null $selector Selector to extract comparable from key.
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function sortByKey(?Closure $selector = null): MutableTrackedMap&TrackedResult;

	/**
	 * Sorts the map entries based on their keys in descending order using an optional selector.
	 * Example: mapOf($users)->toMutable()->sortByKeyDesc(fn($k) => $k->id)
	 *
	 * @template R of mixed
	 * @param Closure(K):R|null $selector
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function sortByKeyDesc(?Closure $selector = null): MutableTrackedMap&TrackedResult;

	/**
	 * Sorts the map entries based on their values using an optional selector.
	 * Example: mapOf($users)->toMutable()->sortByValue(fn($v) => $v->age)
	 *
	 * @template R of mixed
	 * @param Closure(V):R|null $selector Selector to extract comparable from value.
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function sortByValue(?Closure $selector = null): MutableTrackedMap&TrackedResult;

	/**
	 * Sorts the map entries based on their values in descending order using an optional selector.
	 * Example: mapOf($users)->toMutable()->sortByValueDesc(fn($v) => $v->age)
	 *
	 * @template R of mixed
	 * @param Closure(V):R|null $selector
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function sortByValueDesc(?Closure $selector = null): MutableTrackedMap&TrackedResult;

	/**
	 * Sorts the map entries by a selector applied to each pair (V,K).
	 * Example: mapOf($users)->toMutable()->sortBy(fn($v,$k)=>$v->name)
	 *
	 * @template R of mixed
	 * @param Closure(V, K):R $selector Selector to extract comparable from the pair.
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function sortBy(Closure $selector): MutableTrackedMap&TrackedResult;

	/**
	 * Sorts the map entries by a selector applied to each pair (V,K) in descending order.
	 * Example: mapOf($users)->toMutable()->sortByDesc(fn($v,$k)=>$v->name)
	 *
	 * @template R of mixed
	 * @param Closure(V, K):R $selector Selector to extract comparable from the pair.
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function sortByDesc(Closure $selector): MutableTrackedMap&TrackedResult;

	/**
	 * Sorts the map entries based on their keys using a comparator.
	 * Example: mapOf($users)->toMutable()->sortWithKey(fn($a,$b)=>$a->id <=> $b->id)
	 *
	 * @param Closure(K, K):int $comparator
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function sortWithKey(Closure $comparator): MutableTrackedMap&TrackedResult;

	/**
	 * Sorts the map entries based on their values using a comparator.
	 * Example: mapOf($users)->toMutable()->sortWithValue(fn($a,$b)=>$a->age <=> $b->age)
	 *
	 * @param Closure(V, V):int $comparator
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function sortWithValue(Closure $comparator): MutableTrackedMap&TrackedResult;

	/**
	 * Sorts the map entries using a comparator on MapEntry objects.
	 *
	 * @param Closure(MapEntry<K,V>, MapEntry<K,V>): int $comparator
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function sortWith(Closure $comparator): MutableTrackedMap&TrackedResult;

	/**
	 * Reverses the order of entries in the map in-place.
	 *
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function reverse(): MutableTrackedMap&TrackedResult;

	/**
	 * Shuffles the map entries in-place.
	 *
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function shuffle(): MutableTrackedMap&TrackedResult;

	/**
	 * Returns the current map with the given key/value added.
	 *
	 * Note: Mutable maps enforce strict typing - you can only add entries
	 * matching the declared types K and V. Use ImmutableMap if you need type widening.
	 *
	 * @param K $key
	 * @param V $value
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function put(string|int|bool|float|object $key, mixed $value): MutableTrackedMap&TrackedResult;

	/**
	 * Puts the given key/value only if the key is not already present.
	 * If the key already exists, this is a no-op.
	 *
	 * @param K $key
	 * @param V $value
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function putIfAbsent(string|int|bool|float|object $key, mixed $value): MutableTrackedMap&TrackedResult;

	/**
	 * Adds all entries from the given iterable to the current map.
	 * Example of $data: ['key' => 'value', 'key2' => 'value2']
	 *
	 * Note: Mutable maps enforce strict typing - you can only add entries
	 * matching the declared types K and V. Use ImmutableMap if you need type widening.
	 *
	 * @param iterable<K,V> $data
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function putAll(iterable $data): MutableTrackedMap&TrackedResult;

	/**
	 * Adds all entries from the given iterable to the current map.
	 * Example of $data: [['key','value'], ['key2','value2']]
	 *
	 * Note: Mutable maps enforce strict typing - you can only add entries
	 * matching the declared types K and V. Use ImmutableMap if you need type widening.
	 *
	 * @param iterable<array{0:K,1:V}> $data
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function putAllPairs(iterable $data): MutableTrackedMap&TrackedResult;

	/**
	 * Removes the entry for the specified key if present.
	 *
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function remove(string|int|bool|float|object $key): MutableTrackedMap&TrackedResult;

	/**
	 * Removes all entries that match the given predicate function.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeIf(Closure $predicate): MutableTrackedMap&TrackedResult;

	/**
	 * Removes all entries whose keys match the given predicate function.
	 *
	 * @param Closure(K):bool $predicate
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeIfKey(Closure $predicate): MutableTrackedMap&TrackedResult;

	/**
	 * Removes all entries whose values match the given predicate function.
	 *
	 * @param Closure(V):bool $predicate
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeIfValue(Closure $predicate): MutableTrackedMap&TrackedResult;

	/**
	 * Removes all entries with null values.
	 *
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeNullValues(): MutableTrackedMap&TrackedResult;

	/**
	 * Removes the first entry from the map.
	 * No-op if empty.
	 *
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeFirst(): MutableTrackedMap&TrackedResult;

	/**
	 * Removes the last entry from the map.
	 * No-op if empty.
	 *
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeLast(): MutableTrackedMap&TrackedResult;

	/**
	 * Removes all entries from the map.
	 *
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function clear(): MutableTrackedMap&TrackedResult;

	/**
	 * Executes the given action for each entry and returns the map for chaining.
	 *
	 * @param Closure(V, K):void $action
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function forEach(Closure $action): MutableTrackedMap&TrackedResult;

	/**
	 * Executes the given action for each key and returns the map for chaining.
	 *
	 * @param Closure(K):void $action
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function forEachKey(Closure $action): MutableTrackedMap&TrackedResult;

	/**
	 * Executes the given action for each value and returns the map for chaining.
	 *
	 * @param Closure(V):void $action
	 * @return MutableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function forEachValue(Closure $action): MutableTrackedMap&TrackedResult;

	// --- Narrowing End (auto-generated) ---
}
