<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set;

use Closure;
use Noctud\Collection\MutableTrackedCollection;
use Noctud\Collection\TrackedResult;

/**
 * A mutable set that tracks whether the last mutation operation changed the set.
 *
 * @template E
 * @extends MutableSet<E>
 * @extends WritableTrackedSet<E>
 * @extends MutableTrackedCollection<E>
 * @method MutableTrackedSet<E>&TrackedResult onEach(Closure(E, int):void $action) Executes the given action for each element and returns the collection itself for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 */
interface MutableTrackedSet extends MutableSet, WritableTrackedSet, MutableTrackedCollection
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
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function addFirst(mixed $element): MutableTrackedSet&TrackedResult;

	/**
	 * Sorts the collection in-place in its natural order.
	 * Example: listOf(3, 1, 2)->sort() // [1, 2, 3]
	 *
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function sort(): MutableTrackedSet&TrackedResult;

	/**
	 * Sorts the collection in-place in descending natural order.
	 * Example: listOf(1, 3, 2)->sortDesc() // [3, 2, 1]
	 *
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function sortDesc(): MutableTrackedSet&TrackedResult;

	/**
	 * Sorts the collection in-place by a selector.
	 * Example: listOf($users)->sortBy(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function sortBy(Closure $selector): MutableTrackedSet&TrackedResult;

	/**
	 * Sorts the collection in-place by a selector in descending order.
	 * Example: listOf($users)->sortByDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function sortByDesc(Closure $selector): MutableTrackedSet&TrackedResult;

	/**
	 * Sorts the collection in-place using a comparator.
	 * Example: listOf($users)->sortWith(fn($u1, $u2) => $u1->age <=> $u2->age)
	 *
	 * @param Closure(E, E):int $comparator
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function sortWith(Closure $comparator): MutableTrackedSet&TrackedResult;

	/**
	 * Reverses the order of elements in the collection in-place.
	 *
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function reverse(): MutableTrackedSet&TrackedResult;

	/**
	 * Shuffles the elements in the collection in-place.
	 *
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function shuffle(): MutableTrackedSet&TrackedResult;

	/**
	 * Adds a single element to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param E $element The element to add
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function add(mixed $element): MutableTrackedSet&TrackedResult;

	/**
	 * Adds all elements from an iterable source to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param iterable<E> $elements The elements to add
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function addAll(iterable $elements): MutableTrackedSet&TrackedResult;

	/**
	 * Removes the first occurrence of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function removeElement(mixed $element): MutableTrackedSet&TrackedResult;

	/**
	 * Removes all elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function removeIf(Closure $predicate): MutableTrackedSet&TrackedResult;

	/**
	 * Removes all occurrences of every element in the given iterable from the collection.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function removeAll(iterable $elements): MutableTrackedSet&TrackedResult;

	/**
	 * Removes the first element from the collection.
	 * No-op if empty.
	 *
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function removeFirst(): MutableTrackedSet&TrackedResult;

	/**
	 * Removes the last element from the collection.
	 * No-op if empty.
	 *
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function removeLast(): MutableTrackedSet&TrackedResult;

	/**
	 * Retains only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function retainAll(iterable $elements): MutableTrackedSet&TrackedResult;

	/**
	 * Removes all elements from the collection.
	 *
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function clear(): MutableTrackedSet&TrackedResult;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return MutableTrackedSet<E>&TrackedResult The collection itself for chaining
	 */
	public function forEach(Closure $action): MutableTrackedSet&TrackedResult;

	// --- Narrowing End (auto-generated) ---
}
