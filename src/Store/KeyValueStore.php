<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Store;

use IteratorAggregate;
use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Map\KeyCollisionStrategy;
use Noctud\Collection\Map\MapEntry;
use Traversable;

/**
 * Key-value store interface.
 * Used only by traits of this library, not associated with main interfaces.
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @extends IteratorAggregate<K,V>
 */
interface KeyValueStore extends IteratorAggregate
{
	// --- Element Access ---

	/**
	 * Returns the value associated with the given key, or null if not found.
	 *
	 * @param K $key
	 * @return ($throw is true ? V : V|null)
	 * @throws NoSuchElementException
	 */
	public function get(string|int|bool|float|object $key, bool $throw = false): mixed;

	/**
	 * Returns the first entry, or null if the storage is empty.
	 *
	 * @return ($throw is true ? MapEntry<K,V> : MapEntry<K,V>|null)
	 * @throws NoSuchElementException
	 */
	public function first(bool $throw = false): ?MapEntry;

	/**
	 * Returns the last entry, or null if the storage is empty.
	 *
	 * @return ($throw is true ? MapEntry<K,V> : MapEntry<K,V>|null)
	 * @throws NoSuchElementException
	 */
	public function last(bool $throw = false): ?MapEntry;

	/**
	 * Returns the random entry, or null if the storage is empty.
	 *
	 * @return ($throw is true ? MapEntry<K,V> : MapEntry<K,V>|null)
	 * @throws NoSuchElementException
	 */
	public function random(bool $throw = false): ?MapEntry;

	// --- Querying ---

	/**
	 * Returns true if the storage contains the given key.
	 *
	 * @param K $key
	 */
	public function containsKey(string|int|bool|float|object $key): bool;

	/**
	 * Returns true if the storage contains the given value (strict comparison).
	 *
	 * @param V $value
	 */
	public function contains(mixed $value): bool;

	/**
	 * Returns true if the storage is empty.
	 */
	public function isEmpty(): bool;

	/**
	 * Returns the number of entries.
	 */
	public function count(): int;

	// --- Mutation ---

	/**
	 * Sets the value for the given key.
	 *
	 * @param K $key
	 * @param V $value
	 */
	public function put(string|int|bool|float|object $key, mixed $value): void;

	/**
	 * Sets the value for the given key at the beginning of the storage.
	 * If the key already exists, it is moved to the front (with value updated).
	 *
	 * @param K $key
	 * @param V $value
	 */
	public function putFirst(string|int|bool|float|object $key, mixed $value): void;

	/**
	 * Puts all entries from an associative source into the storage.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 *
	 * @param iterable<NK,NV> $source
	 */
	public function putAllFromAssoc(iterable $source): void;

	/**
	 * Puts all entries from a pairs source into the storage.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 *
	 * @param iterable<array{0:NK,1:NV}> $source
	 */
	public function putAllFromPairs(iterable $source): void;

	/**
	 * Removes the entry with the specified key.
	 *
	 * @param K $key
	 */
	public function remove(string|int|bool|float|object $key): void;

	/**
	 * Removes the first entry from the storage.
	 * No-op if empty.
	 */
	public function removeFirst(): void;

	/**
	 * Removes the last entry from the storage.
	 * No-op if empty.
	 */
	public function removeLast(): void;

	/**
	 * Removes all entries from the storage.
	 */
	public function clear(): void;

	/**
	 * Removes all entries where the predicate returns true.
	 *
	 * @param callable(V, K):bool $predicate
	 */
	public function removeIf(callable $predicate): void;

	/**
	 * Removes all entries where the key predicate returns true.
	 *
	 * @param callable(K):bool $predicate
	 */
	public function removeIfKey(callable $predicate): void;

	/**
	 * Removes all entries where the value predicate returns true.
	 *
	 * @param callable(V):bool $predicate
	 */
	public function removeIfValue(callable $predicate): void;

	// --- Ordering ---

	/**
	 * Sorts entries in-place using a comparator on pairs [K,V].
	 *
	 * @param callable(array{0:K,1:V}, array{0:K,1:V}):int $comparator
	 */
	public function sortByPairs(callable $comparator): void;

	/**
	 * Reverses the order of entries in-place.
	 */
	public function reverse(): void;

	/**
	 * Shuffles the entries in-place.
	 */
	public function shuffle(): void;

	// --- Conversion ---

	/**
	 * Returns all entries as a PHP array.
	 *
	 * @return array<array-key,V>
	 * @throws ConversionException
	 */
	public function toArray(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): array;

	/**
	 * Returns all entries as an array of [key, value] pairs.
	 *
	 * @return list<array{0:K,1:V}>
	 */
	public function toPairs(): array;

	// --- Internal ---

	/**
	 * Returns an iterator over all entries.
	 *
	 * @return Traversable<K,V>
	 */
	public function getIterator(): Traversable;
}
