<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List;

use Closure;
use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\TrackedResult;
use Noctud\Collection\WritableTrackedCollection;

/**
 * A writable list that tracks whether the last mutation operation changed the list.
 *
 * @template E
 * @extends WritableList<E>
 * @extends WritableTrackedCollection<E>
 * @method WritableTrackedList<E>&TrackedResult onEach(Closure(E, int):void $action) Executes the given action for each element and returns the collection itself for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 */
interface WritableTrackedList extends WritableList, WritableTrackedCollection
{
	// --- Narrowing Start (auto-generated) ---

	/**
	 * Sets the element at the specified index.
	 *
	 * @param int $index The index at which to set the element
	 * @param E $element The element to set
	 * @return WritableTrackedList<E>&TrackedResult The list itself for chaining
	 * @throws IndexOutOfBoundsException If the index is out of bounds
	 */
	public function set(int $index, mixed $element): WritableTrackedList&TrackedResult;

	/**
	 * Removes all occurrences of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return WritableTrackedList<E>&TrackedResult The list itself for chaining
	 */
	public function removeEvery(mixed $element): WritableTrackedList&TrackedResult;

	/**
	 * Removes the element at the specified index.
	 *
	 * @param int $index The index of the element to remove
	 * @return WritableTrackedList<E>&TrackedResult The list itself for chaining
	 * @throws IndexOutOfBoundsException If the index is out of bounds
	 */
	public function removeAt(int $index): WritableTrackedList&TrackedResult;

	/**
	 * Adds a single element to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param E $element The element to add
	 * @return WritableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function add(mixed $element): WritableTrackedList&TrackedResult;

	/**
	 * Adds all elements from an iterable source to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param iterable<E> $elements The elements to add
	 * @return WritableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function addAll(iterable $elements): WritableTrackedList&TrackedResult;

	/**
	 * Removes the first occurrence of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return WritableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function removeElement(mixed $element): WritableTrackedList&TrackedResult;

	/**
	 * Removes all elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return WritableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function removeIf(Closure $predicate): WritableTrackedList&TrackedResult;

	/**
	 * Removes all occurrences of every element in the given iterable from the collection.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return WritableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function removeAll(iterable $elements): WritableTrackedList&TrackedResult;

	/**
	 * Removes the first element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function removeFirst(): WritableTrackedList&TrackedResult;

	/**
	 * Removes the last element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function removeLast(): WritableTrackedList&TrackedResult;

	/**
	 * Retains only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return WritableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function retainAll(iterable $elements): WritableTrackedList&TrackedResult;

	/**
	 * Removes all elements from the collection.
	 *
	 * @return WritableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function clear(): WritableTrackedList&TrackedResult;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return WritableTrackedList<E>&TrackedResult The collection itself for chaining
	 */
	public function forEach(Closure $action): WritableTrackedList&TrackedResult;

	// --- Narrowing End (auto-generated) ---
}
