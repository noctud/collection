<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List;

use Closure;
use NoDiscard;
use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\MutableCollection;

/**
 * @template E
 * @extends WritableList<E>
 * @extends MutableCollection<E>
 */
interface MutableList extends WritableList, MutableCollection
{
	// --- Tracking ---

	/**
	 * Returns a tracked version of this list that tracks whether mutations change the list.
	 * The tracked list shares the same underlying store.
	 *
	 * @return MutableTrackedList<E>
	 */
	#[NoDiscard]
	public function tracked(): MutableTrackedList;

	// --- Element Access ---

	/**
	 * {@inheritDoc}
	 *
	 * @param non-negative-int $from Starting index (inclusive)
	 * @param non-negative-int $to Ending index (exclusive)
	 * @return ImmutableList<E> New list containing the sliced elements
	 */
	#[NoDiscard]
	public function slice(int $from, int $to): ImmutableList;

	// --- Narrowing Start (auto-generated) ---

	/**
	 * Adds a single element to the beginning of the collection.
	 *
	 * For Sets: if the element is already present, it is moved to the first position.
	 * If the element is already first, this is a no-op.
	 * For Lists: the element is always prepended (duplicates are allowed).
	 *
	 * @param E $element The element to add
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function addFirst(mixed $element): MutableList;

	/**
	 * Sorts the collection in-place in its natural order.
	 * Example: listOf(3, 1, 2)->sort() // [1, 2, 3]
	 *
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function sort(): MutableList;

	/**
	 * Sorts the collection in-place in descending natural order.
	 * Example: listOf(1, 3, 2)->sortDesc() // [3, 2, 1]
	 *
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function sortDesc(): MutableList;

	/**
	 * Sorts the collection in-place by a selector.
	 * Example: listOf($users)->sortBy(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function sortBy(Closure $selector): MutableList;

	/**
	 * Sorts the collection in-place by a selector in descending order.
	 * Example: listOf($users)->sortByDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function sortByDesc(Closure $selector): MutableList;

	/**
	 * Sorts the collection in-place using a comparator.
	 * Example: listOf($users)->sortWith(fn($u1, $u2) => $u1->age <=> $u2->age)
	 *
	 * @param Closure(E, E):int $comparator
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function sortWith(Closure $comparator): MutableList;

	/**
	 * Reverses the order of elements in the collection in-place.
	 *
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function reverse(): MutableList;

	/**
	 * Shuffles the elements in the collection in-place.
	 *
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function shuffle(): MutableList;

	/**
	 * Adds a single element to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param E $element The element to add
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function add(mixed $element): MutableList;

	/**
	 * Adds all elements from an iterable source to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param iterable<E> $elements The elements to add
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function addAll(iterable $elements): MutableList;

	/**
	 * Removes the first occurrence of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function removeElement(mixed $element): MutableList;

	/**
	 * Removes all elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function removeIf(Closure $predicate): MutableList;

	/**
	 * Removes all occurrences of every element in the given iterable from the collection.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function removeAll(iterable $elements): MutableList;

	/**
	 * Removes the first element from the collection.
	 * No-op if empty.
	 *
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function removeFirst(): MutableList;

	/**
	 * Removes the last element from the collection.
	 * No-op if empty.
	 *
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function removeLast(): MutableList;

	/**
	 * Retains only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function retainAll(iterable $elements): MutableList;

	/**
	 * Removes all elements from the collection.
	 *
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function clear(): MutableList;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return MutableList<E> The collection itself for chaining
	 */
	public function forEach(Closure $action): MutableList;

	/**
	 * Sets the element at the specified index.
	 *
	 * @param int $index The index at which to set the element
	 * @param E $element The element to set
	 * @return MutableList<E> The list itself for chaining
	 * @throws IndexOutOfBoundsException If the index is out of bounds
	 */
	public function set(int $index, mixed $element): MutableList;

	/**
	 * Removes all occurrences of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return MutableList<E> The list itself for chaining
	 */
	public function removeEvery(mixed $element): MutableList;

	/**
	 * Removes the element at the specified index.
	 *
	 * @param int $index The index of the element to remove
	 * @return MutableList<E> The list itself for chaining
	 * @throws IndexOutOfBoundsException If the index is out of bounds
	 */
	public function removeAt(int $index): MutableList;

	// --- Narrowing End (auto-generated) ---
}
