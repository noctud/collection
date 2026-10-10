<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

use Closure;

/**
 * A mutable collection that tracks whether the last mutation operation changed the collection.
 *
 * @template E
 * @extends MutableCollection<E>
 * @extends WritableTrackedCollection<E>
 * @method MutableTrackedCollection<E>&TrackedResult onEach(Closure(E, int):void $action) Executes the given action for each element and returns the collection itself for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 */
interface MutableTrackedCollection extends MutableCollection, WritableTrackedCollection
{
	// --- Narrowing Start (auto-generated) ---

	/**
	 * Adds a single element to the beginning of the collection.
	 *
	 * For Sets: if the element is already present, it is moved to the first position.
	 * If the element is already first, this is a no-op.
	 * For Lists: the element is always prepended (duplicates are allowed).
	 *
	 * @param E $element The element to add
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function addFirst(mixed $element): MutableTrackedCollection&TrackedResult;

	/**
	 * Sorts the collection in-place in its natural order.
	 * Example: listOf(3, 1, 2)->sort() // [1, 2, 3]
	 *
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function sort(): MutableTrackedCollection&TrackedResult;

	/**
	 * Sorts the collection in-place in descending natural order.
	 * Example: listOf(1, 3, 2)->sortDesc() // [3, 2, 1]
	 *
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function sortDesc(): MutableTrackedCollection&TrackedResult;

	/**
	 * Sorts the collection in-place by a selector.
	 * Example: listOf($users)->sortBy(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function sortBy(Closure $selector): MutableTrackedCollection&TrackedResult;

	/**
	 * Sorts the collection in-place by a selector in descending order.
	 * Example: listOf($users)->sortByDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function sortByDesc(Closure $selector): MutableTrackedCollection&TrackedResult;

	/**
	 * Sorts the collection in-place using a comparator.
	 * Example: listOf($users)->sortWith(fn($u1, $u2) => $u1->age <=> $u2->age)
	 *
	 * @param Closure(E, E):int $comparator
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function sortWith(Closure $comparator): MutableTrackedCollection&TrackedResult;

	/**
	 * Reverses the order of elements in the collection in-place.
	 *
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function reverse(): MutableTrackedCollection&TrackedResult;

	/**
	 * Shuffles the elements in the collection in-place.
	 *
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function shuffle(): MutableTrackedCollection&TrackedResult;

	/**
	 * Adds a single element to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param E $element The element to add
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function add(mixed $element): MutableTrackedCollection&TrackedResult;

	/**
	 * Adds all elements from an iterable source to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param iterable<E> $elements The elements to add
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function addAll(iterable $elements): MutableTrackedCollection&TrackedResult;

	/**
	 * Removes the first occurrence of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function removeElement(mixed $element): MutableTrackedCollection&TrackedResult;

	/**
	 * Removes all elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function removeIf(Closure $predicate): MutableTrackedCollection&TrackedResult;

	/**
	 * Removes all occurrences of every element in the given iterable from the collection.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function removeAll(iterable $elements): MutableTrackedCollection&TrackedResult;

	/**
	 * Removes the first element from the collection.
	 * No-op if empty.
	 *
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function removeFirst(): MutableTrackedCollection&TrackedResult;

	/**
	 * Removes the last element from the collection.
	 * No-op if empty.
	 *
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function removeLast(): MutableTrackedCollection&TrackedResult;

	/**
	 * Retains only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function retainAll(iterable $elements): MutableTrackedCollection&TrackedResult;

	/**
	 * Removes all elements from the collection.
	 *
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function clear(): MutableTrackedCollection&TrackedResult;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return MutableTrackedCollection<E>&TrackedResult The collection itself for chaining
	 */
	public function forEach(Closure $action): MutableTrackedCollection&TrackedResult;

	// --- Narrowing End (auto-generated) ---
}
