<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List;

use Closure;
use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\WritableCollection;
use NoDiscard;

/**
 * Writable list provides basic methods for read/write operations on a list.
 * Implementations that cannot support ordering operations (sort, reverse, shuffle)
 * should implement this interface instead of MutableList.
 *
 * @template E
 * @extends ListInterface<E>
 * @extends WritableCollection<E>
 */
interface WritableList extends ListInterface, WritableCollection
{
	// --- Tracking ---

	/**
	 * Returns a tracked version of this list that tracks whether mutations change the list.
	 * The tracked list shares the same underlying store.
	 *
	 * @return WritableTrackedList<E>
	 */
	#[NoDiscard]
	public function tracked(): WritableTrackedList;

	// --- Mutation ---

	/**
	 * Sets the element at the specified index.
	 *
	 * @param int $index The index at which to set the element
	 * @param E $element The element to set
	 * @return WritableList<E> The list itself for chaining
	 * @throws IndexOutOfBoundsException If the index is out of bounds
	 */
	public function set(int $index, mixed $element): WritableList;

	/**
	 * Removes all occurrences of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return WritableList<E> The list itself for chaining
	 */
	public function removeEvery(mixed $element): WritableList;

	/**
	 * Removes the element at the specified index.
	 *
	 * @param int $index The index of the element to remove
	 * @return WritableList<E> The list itself for chaining
	 * @throws IndexOutOfBoundsException If the index is out of bounds
	 */
	public function removeAt(int $index): WritableList;

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
	 * Adds a single element to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param E $element The element to add
	 * @return WritableList<E> The collection itself for chaining
	 */
	public function add(mixed $element): WritableList;

	/**
	 * Adds all elements from an iterable source to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param iterable<E> $elements The elements to add
	 * @return WritableList<E> The collection itself for chaining
	 */
	public function addAll(iterable $elements): WritableList;

	/**
	 * Removes the first occurrence of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return WritableList<E> The collection itself for chaining
	 */
	public function removeElement(mixed $element): WritableList;

	/**
	 * Removes all elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return WritableList<E> The collection itself for chaining
	 */
	public function removeIf(Closure $predicate): WritableList;

	/**
	 * Removes all occurrences of every element in the given iterable from the collection.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return WritableList<E> The collection itself for chaining
	 */
	public function removeAll(iterable $elements): WritableList;

	/**
	 * Removes the first element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableList<E> The collection itself for chaining
	 */
	public function removeFirst(): WritableList;

	/**
	 * Removes the last element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableList<E> The collection itself for chaining
	 */
	public function removeLast(): WritableList;

	/**
	 * Retains only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return WritableList<E> The collection itself for chaining
	 */
	public function retainAll(iterable $elements): WritableList;

	/**
	 * Removes all elements from the collection.
	 *
	 * @return WritableList<E> The collection itself for chaining
	 */
	public function clear(): WritableList;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return WritableList<E> The collection itself for chaining
	 */
	public function forEach(Closure $action): WritableList;

	/**
	 * Filter elements by predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): ImmutableList;

	/**
	 * Filter non-null elements.
	 *
	 * @return ImmutableList<(E is null ? never : E)>
	 */
	#[NoDiscard]
	public function filterNotNull(): ImmutableList;

	/**
	 * Filter elements that are instances of the given class or interface.
	 *
	 * @template T
	 * @param class-string<T> $type
	 * @return ImmutableList<T>
	 */
	#[NoDiscard]
	public function filterInstanceOf(string $type): ImmutableList;

	/**
	 * Map elements to a new collection.
	 * The transform receives the element and optionally the index.
	 *
	 * @template R
	 * @param Closure(E, int):R $transform
	 * @return ImmutableList<R>
	 */
	#[NoDiscard]
	public function map(Closure $transform): ImmutableList;

	/**
	 * Transforms each element using the given function and excludes null results.
	 * Combines map and filterNotNull in a single operation.
	 *
	 * @template R
	 * @param Closure(E, int):(R|null) $transform
	 * @return ImmutableList<R>
	 */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): ImmutableList;

	/**
	 * Flat map elements to a new collection.
	 *
	 * @template R
	 * @param Closure(E, int):iterable<R> $transform
	 * @return ImmutableList<R>
	 */
	#[NoDiscard]
	public function flatMap(Closure $transform): ImmutableList;

	/**
	 * Flatten a collection of iterables into a single collection.
	 * Iterable elements are flattened one level; non-iterable elements are kept as-is.
	 * The array{} in value-of keeps the type resolvable when E is never (empty collections).
	 *
	 * @return ImmutableList<(E is iterable<mixed> ? value-of<E|array{}> : E)>
	 */
	#[NoDiscard]
	public function flatten(): ImmutableList;

	/**
	 * Take the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): ImmutableList;

	/**
	 * Drops the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): ImmutableList;

	/**
	 * Take the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): ImmutableList;

	/**
	 * Drops the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): ImmutableList;

	/**
	 * Takes elements while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): ImmutableList;

	/**
	 * Drops elements while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): ImmutableList;

	/**
	 * Takes elements from the end while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): ImmutableList;

	/**
	 * Drops elements from the end while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): ImmutableList;

	/**
	 * Distinct elements by identity.
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function distinct(): ImmutableList;

	/**
	 * Distinct elements by selector.
	 *
	 * @template K
	 * @param Closure(E, int):K $selector
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function distinctBy(Closure $selector): ImmutableList;

	/**
	 * Returns a new collection with elements sorted in their natural order.
	 * Example: listOf(3, 1, 2)->sorted() // [1, 2, 3]
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function sorted(): ImmutableList;

	/**
	 * Returns a new collection with elements sorted in descending natural order.
	 * Example: listOf(1, 3, 2)->sortedDesc() // [3, 2, 1]
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function sortedDesc(): ImmutableList;

	/**
	 * Returns a new collection with elements sorted by a selector.
	 * Example: listOf($users)->sortedBy(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): ImmutableList;

	/**
	 * Returns a new collection with elements sorted by a selector in descending order.
	 * Example: listOf($users)->sortedByDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): ImmutableList;

	/**
	 * Returns a new collection with elements sorted using a comparator.
	 * Example: listOf($users)->sortedWith(fn($u1, $u2) => $u1->age <=> $u2->age)
	 *
	 * @param Closure(E, E):int $comparator
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): ImmutableList;

	/**
	 * Returns a new collection with elements in reversed order.
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function reversed(): ImmutableList;

	/**
	 * Returns a new collection with elements in random order.
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function shuffled(): ImmutableList;

	/**
	 * Splits the collection into two collections based on a predicate.
	 * The first collection contains elements matching the predicate,
	 * the second contains the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return array{ImmutableList<E>, ImmutableList<E>}
	 */
	#[NoDiscard]
	public function partition(Closure $predicate): array;

	/**
	 * Group by key selector. When a value transform is provided, each element is transformed
	 * before being added to its group.
	 *
	 * @template K of string|int|bool|float|object
	 * @template V
	 * @param Closure(E, int):K $keySelector
	 * @param (Closure(E, int):V)|null $valueTransform
	 * @return ($valueTransform is null ? ImmutableMap<K, ImmutableList<E>> : ImmutableMap<K, ImmutableList<V>>)
	 */
	#[NoDiscard]
	public function groupBy(Closure $keySelector, ?Closure $valueTransform = null): ImmutableMap;

	// --- Narrowing End (auto-generated) ---
}
