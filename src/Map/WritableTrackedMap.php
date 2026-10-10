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
 * A writable map that tracks whether the last mutation operation changed the map.
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @extends WritableMap<K,V>
 * @method WritableTrackedMap<K,V>&TrackedResult onEach(Closure(V, K):void $action) Executes the given action for each entry and returns the current map for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 * @method WritableTrackedMap<K,V>&TrackedResult onEachKey(Closure(K):void $action) Executes the given action for each key and returns the current map for chaining. Use it instead of forEachKey() wherever the result is used - forEachKey() returns void since 0.2.
 * @method WritableTrackedMap<K,V>&TrackedResult onEachValue(Closure(V):void $action) Executes the given action for each value and returns the current map for chaining. Use it instead of forEachValue() wherever the result is used - forEachValue() returns void since 0.2.
 */
interface WritableTrackedMap extends WritableMap
{
	// --- Narrowing Start (auto-generated) ---

	/**
	 * Returns the current map with the given key/value added.
	 *
	 * Note: Mutable maps enforce strict typing - you can only add entries
	 * matching the declared types K and V. Use ImmutableMap if you need type widening.
	 *
	 * @param K $key
	 * @param V $value
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function put(string|int|bool|float|object $key, mixed $value): WritableTrackedMap&TrackedResult;

	/**
	 * Puts the given key/value only if the key is not already present.
	 * If the key already exists, this is a no-op.
	 *
	 * @param K $key
	 * @param V $value
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function putIfAbsent(string|int|bool|float|object $key, mixed $value): WritableTrackedMap&TrackedResult;

	/**
	 * Adds all entries from the given iterable to the current map.
	 * Example of $data: ['key' => 'value', 'key2' => 'value2']
	 *
	 * Note: Mutable maps enforce strict typing - you can only add entries
	 * matching the declared types K and V. Use ImmutableMap if you need type widening.
	 *
	 * @param iterable<K,V> $data
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function putAll(iterable $data): WritableTrackedMap&TrackedResult;

	/**
	 * Adds all entries from the given iterable to the current map.
	 * Example of $data: [['key','value'], ['key2','value2']]
	 *
	 * Note: Mutable maps enforce strict typing - you can only add entries
	 * matching the declared types K and V. Use ImmutableMap if you need type widening.
	 *
	 * @param iterable<array{0:K,1:V}> $data
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function putAllPairs(iterable $data): WritableTrackedMap&TrackedResult;

	/**
	 * Removes the entry for the specified key if present.
	 *
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function remove(string|int|bool|float|object $key): WritableTrackedMap&TrackedResult;

	/**
	 * Removes all entries that match the given predicate function.
	 *
	 * @param Closure(V, K):bool $predicate
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeIf(Closure $predicate): WritableTrackedMap&TrackedResult;

	/**
	 * Removes all entries whose keys match the given predicate function.
	 *
	 * @param Closure(K):bool $predicate
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeIfKey(Closure $predicate): WritableTrackedMap&TrackedResult;

	/**
	 * Removes all entries whose values match the given predicate function.
	 *
	 * @param Closure(V):bool $predicate
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeIfValue(Closure $predicate): WritableTrackedMap&TrackedResult;

	/**
	 * Removes all entries with null values.
	 *
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeNullValues(): WritableTrackedMap&TrackedResult;

	/**
	 * Removes the first entry from the map.
	 * No-op if empty.
	 *
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeFirst(): WritableTrackedMap&TrackedResult;

	/**
	 * Removes the last entry from the map.
	 * No-op if empty.
	 *
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function removeLast(): WritableTrackedMap&TrackedResult;

	/**
	 * Removes all entries from the map.
	 *
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function clear(): WritableTrackedMap&TrackedResult;

	/**
	 * Executes the given action for each entry and returns the map for chaining.
	 *
	 * @param Closure(V, K):void $action
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function forEach(Closure $action): WritableTrackedMap&TrackedResult;

	/**
	 * Executes the given action for each key and returns the map for chaining.
	 *
	 * @param Closure(K):void $action
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function forEachKey(Closure $action): WritableTrackedMap&TrackedResult;

	/**
	 * Executes the given action for each value and returns the map for chaining.
	 *
	 * @param Closure(V):void $action
	 * @return WritableTrackedMap<K,V>&TrackedResult The current map.
	 */
	public function forEachValue(Closure $action): WritableTrackedMap&TrackedResult;

	// --- Narrowing End (auto-generated) ---
}
