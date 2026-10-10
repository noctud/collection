<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

use Closure;

/**
 * A writable collection that tracks whether the last mutation operation changed the collection.
 *
 * @template E
 * @extends WritableCollection<E>
 * @method WritableTrackedCollection<E>&TrackedResult onEach(Closure(E, int):void $action) Executes the given action for each element and returns the collection itself for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 */
interface WritableTrackedCollection extends WritableCollection
{
	// --- Narrowing Start (auto-generated) ---

	/**
	 * Adds a single element to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param E $element The element to add
	 * @return WritableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function add(mixed $element): WritableTrackedCollection&TrackedResult;

	/**
	 * Adds all elements from an iterable source to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param iterable<E> $elements The elements to add
	 * @return WritableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function addAll(iterable $elements): WritableTrackedCollection&TrackedResult;

	/**
	 * Removes the first occurrence of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return WritableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function removeElement(mixed $element): WritableTrackedCollection&TrackedResult;

	/**
	 * Removes all elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return WritableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function removeIf(Closure $predicate): WritableTrackedCollection&TrackedResult;

	/**
	 * Removes all occurrences of every element in the given iterable from the collection.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return WritableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function removeAll(iterable $elements): WritableTrackedCollection&TrackedResult;

	/**
	 * Removes the first element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function removeFirst(): WritableTrackedCollection&TrackedResult;

	/**
	 * Removes the last element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function removeLast(): WritableTrackedCollection&TrackedResult;

	/**
	 * Retains only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return WritableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function retainAll(iterable $elements): WritableTrackedCollection&TrackedResult;

	/**
	 * Removes all elements from the collection.
	 *
	 * @return WritableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function clear(): WritableTrackedCollection&TrackedResult;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return WritableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function forEach(Closure $action): WritableTrackedCollection&TrackedResult;

	// --- Narrowing End (auto-generated) ---
}
