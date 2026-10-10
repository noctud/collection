<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List;

use Closure;
use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\MutableTrackedCollection;
use Noctud\Collection\TrackedResult;

/**
 * A mutable list that tracks whether the last mutation operation changed the list.
 *
 * @template E
 * @extends MutableList<E>
 * @extends WritableTrackedList<E>
 * @extends MutableTrackedCollection<E>
 * @method MutableTrackedList<E>&TrackedResult onEach(Closure(E, int):void $action) Executes the given action for each element and returns the collection itself for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 */
interface MutableTrackedList extends MutableList, WritableTrackedList, MutableTrackedCollection
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
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function addFirst(mixed $element): MutableTrackedList&TrackedResult;

	/**
	 * Sorts the collection in-place in its natural order.
	 * Example: listOf(3, 1, 2)->sort() // [1, 2, 3]
	 *
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function sort(): MutableTrackedList&TrackedResult;

	/**
	 * Sorts the collection in-place in descending natural order.
	 * Example: listOf(1, 3, 2)->sortDesc() // [3, 2, 1]
	 *
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function sortDesc(): MutableTrackedList&TrackedResult;

	/**
	 * Sorts the collection in-place by a selector.
	 * Example: listOf($users)->sortBy(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function sortBy(Closure $selector): MutableTrackedList&TrackedResult;

	/**
	 * Sorts the collection in-place by a selector in descending order.
	 * Example: listOf($users)->sortByDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function sortByDesc(Closure $selector): MutableTrackedList&TrackedResult;

	/**
	 * Sorts the collection in-place using a comparator.
	 * Example: listOf($users)->sortWith(fn($u1, $u2) => $u1->age <=> $u2->age)
	 *
	 * @param Closure(E, E):int $comparator
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function sortWith(Closure $comparator): MutableTrackedList&TrackedResult;

	/**
	 * Reverses the order of elements in the collection in-place.
	 *
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function reverse(): MutableTrackedList&TrackedResult;

	/**
	 * Shuffles the elements in the collection in-place.
	 *
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function shuffle(): MutableTrackedList&TrackedResult;

	/**
	 * Adds a single element to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param E $element The element to add
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function add(mixed $element): MutableTrackedList&TrackedResult;

	/**
	 * Adds all elements from an iterable source to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param iterable<E> $elements The elements to add
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function addAll(iterable $elements): MutableTrackedList&TrackedResult;

	/**
	 * Removes the first occurrence of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function removeElement(mixed $element): MutableTrackedList&TrackedResult;

	/**
	 * Removes all elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function removeIf(Closure $predicate): MutableTrackedList&TrackedResult;

	/**
	 * Removes all occurrences of every element in the given iterable from the collection.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function removeAll(iterable $elements): MutableTrackedList&TrackedResult;

	/**
	 * Removes the first element from the collection.
	 * No-op if empty.
	 *
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function removeFirst(): MutableTrackedList&TrackedResult;

	/**
	 * Removes the last element from the collection.
	 * No-op if empty.
	 *
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function removeLast(): MutableTrackedList&TrackedResult;

	/**
	 * Retains only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function retainAll(iterable $elements): MutableTrackedList&TrackedResult;

	/**
	 * Removes all elements from the collection.
	 *
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function clear(): MutableTrackedList&TrackedResult;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return MutableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function forEach(Closure $action): MutableTrackedList&TrackedResult;

	/**
	 * Sets the element at the specified index.
	 *
	 * @param int $index The index at which to set the element
	 * @param E $element The element to set
	 * @return MutableTrackedList<E>&TrackedResult The list itself for chaining
	 * @throws IndexOutOfBoundsException If the index is out of bounds
	 */
	public function set(int $index, mixed $element): MutableTrackedList&TrackedResult;

	/**
	 * Removes all occurrences of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return MutableTrackedList<E>&TrackedResult The list itself for chaining
	 */
	public function removeEvery(mixed $element): MutableTrackedList&TrackedResult;

	/**
	 * Removes the element at the specified index.
	 *
	 * @param int $index The index of the element to remove
	 * @return MutableTrackedList<E>&TrackedResult The list itself for chaining
	 * @throws IndexOutOfBoundsException If the index is out of bounds
	 */
	public function removeAt(int $index): MutableTrackedList&TrackedResult;

	// --- Narrowing End (auto-generated) ---
}
