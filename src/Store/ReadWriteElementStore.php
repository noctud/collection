<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Store;

/**
 * Writable and readable element store interface.
 * Used only by traits of this library, not associated with main interfaces.
 *
 * @template E
 * @extends ReadOnlyElementStore<E>
 */
interface ReadWriteElementStore extends ReadOnlyElementStore
{
	// --- Mutation ---

	/**
	 * Adds a value to the storage.
	 *
	 * @param mixed $element
	 */
	public function add(mixed $element): void;

	/**
	 * Adds a value to the beginning of the storage.
	 * Implementations may move existing elements to the front (e.g. hash-based stores).
	 *
	 * @param mixed $element
	 */
	public function addFirst(mixed $element): void;

	/**
	 * Adds all elements from source into the storage.
	 *
	 * @param iterable<mixed> $source
	 */
	public function addAll(iterable $source): void;

	/**
	 * Removes the first occurrence of the element from the storage.
	 *
	 * @param E $element
	 */
	public function removeFirstOccurrence(mixed $element): void;

	/**
	 * Removes the first element from the storage.
	 * No-op if empty.
	 */
	public function removeFirst(): void;

	/**
	 * Removes the last element from the storage.
	 * No-op if empty.
	 */
	public function removeLast(): void;

	/**
	 * Removes all elements from the storage.
	 */
	public function clear(): void;

	/**
	 * Removes all elements matching the predicate in-place.
	 *
	 * @param callable(E):bool $predicate
	 */
	public function removeIf(callable $predicate): void;

	// --- Ordering ---

	/**
	 * Sorts elements in-place using an optional comparator.
	 * When no comparator is provided, sorts in natural order.
	 *
	 * @param callable(E,E):int|null $comparator
	 */
	public function sort(?callable $comparator = null): void;

	/**
	 * Sorts elements in-place by the value the selector returns for each of them, compared with <=>.
	 * Elements with equal values keep their order.
	 *
	 * @param callable(E):mixed $selector
	 */
	public function sortBy(callable $selector, bool $descending = false): void;

	/**
	 * Reverses the order of elements in-place.
	 */
	public function reverse(): void;

	/**
	 * Shuffles the elements in-place.
	 */
	public function shuffle(): void;
}
