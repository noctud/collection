<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set;

use Closure;
use Noctud\Collection\TrackedResult;
use Noctud\Collection\WritableTrackedCollection;

/**
 * A writable set that tracks whether the last mutation operation changed the set.
 *
 * @template E
 * @extends WritableSet<E>
 * @extends WritableTrackedCollection<E>
 * @method WritableTrackedSet<E>&TrackedResult onEach(Closure(E, int):void $action) Executes the given action for each element and returns the collection itself for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 */
interface WritableTrackedSet extends WritableSet, WritableTrackedCollection
{
	// --- Narrowing Start (auto-generated) ---

	/**
	 * Adds a single element to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param E $element The element to add
	 * @return WritableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function add(mixed $element): WritableTrackedSet&TrackedResult;

	/**
	 * Adds all elements from an iterable source to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param iterable<E> $elements The elements to add
	 * @return WritableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function addAll(iterable $elements): WritableTrackedSet&TrackedResult;

	/**
	 * Removes the first occurrence of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return WritableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function removeElement(mixed $element): WritableTrackedSet&TrackedResult;

	/**
	 * Removes all elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return WritableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function removeIf(Closure $predicate): WritableTrackedSet&TrackedResult;

	/**
	 * Removes all occurrences of every element in the given iterable from the collection.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return WritableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function removeAll(iterable $elements): WritableTrackedSet&TrackedResult;

	/**
	 * Removes the first element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function removeFirst(): WritableTrackedSet&TrackedResult;

	/**
	 * Removes the last element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function removeLast(): WritableTrackedSet&TrackedResult;

	/**
	 * Retains only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return WritableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function retainAll(iterable $elements): WritableTrackedSet&TrackedResult;

	/**
	 * Removes all elements from the collection.
	 *
	 * @return WritableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function clear(): WritableTrackedSet&TrackedResult;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return WritableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function forEach(Closure $action): WritableTrackedSet&TrackedResult;

	// --- Narrowing End (auto-generated) ---
}
