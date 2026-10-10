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
use Noctud\Collection\ImmutableCollection;
use Noctud\Collection\Map\ImmutableMap;

/**
 * @template E
 * @extends ListInterface<E>
 * @extends ImmutableCollection<E>
 * @method ImmutableList<E> onEach(Closure(E, int):void $action) Executes the given action for each element and returns the collection for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 */
interface ImmutableList extends ListInterface, ImmutableCollection
{
	/**
	 * {@inheritDoc}
	 *
	 * @deprecated You are using toImmutable() on an ImmutableList!
	 * Same instance will be returned, this deprecation is intended
	 * as a hint that you were not in read-only/mutable context.
	 *
	 * @return ImmutableList<E> The immutable copy of this collection.
	 */
	#[NoDiscard]
	public function toImmutable(): ImmutableList;

	// --- Mutation (returns new) ---

	/**
	 * Creates a new list with the element at the specified index replaced.
	 *
	 * Note: Immutable collections allow type widening - you can set an element
	 * of a different type, and the returned list will have the union type.
	 *
	 * @template NE
	 *
	 * @param int $index The index at which to set the element
	 * @param NE $element The element to set
	 * @return ImmutableList<E|NE> The new list with the replaced element
	 * @throws IndexOutOfBoundsException If the index is out of bounds
	 */
	#[NoDiscard]
	public function set(int $index, mixed $element): ImmutableList;

	/**
	 * Removes all occurrences of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return ImmutableList<E> A new list without the element
	 */
	#[NoDiscard]
	public function removeEvery(mixed $element): ImmutableList;

	/**
	 * Returns a new list with the element at the specified index removed.
	 *
	 * @param int $index The index of the element to remove
	 * @return ImmutableList<E> A new list without the element at the specified index
	 * @throws IndexOutOfBoundsException If the index is out of bounds
	 */
	#[NoDiscard]
	public function removeAt(int $index): ImmutableList;

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
	 * Returns a new collection with the specified element added.
	 *
	 * Note: Immutable collections allow type widening - you can add an element
	 * of a different type, and the returned collection will have the union type.
	 *
	 * @template NE
	 *
	 * @param NE $element The element to add
	 * @return ImmutableList<E|NE> A new collection with the element added
	 */
	#[NoDiscard]
	public function add(mixed $element): ImmutableList;

	/**
	 * Returns a new collection with the specified element at the beginning.
	 *
	 * For Sets: if the element is already present, it is moved to the first position.
	 * If the element is already first, the same collection is returned.
	 * For Lists: the element is always prepended (duplicates are allowed).
	 *
	 * @template NE
	 *
	 * @param NE $element The element to add
	 * @return ImmutableList<E|NE> A new collection with the element first
	 */
	#[NoDiscard]
	public function addFirst(mixed $element): ImmutableList;

	/**
	 * Returns a new collection with all elements from the iterable added.
	 *
	 * Note: Immutable collections allow type widening - you can add elements
	 * of a different type, and the returned collection will have the union type.
	 *
	 * @template NE
	 *
	 * @param iterable<NE> $elements The elements to add
	 * @return ImmutableList<E|NE> A new collection with the elements added
	 */
	#[NoDiscard]
	public function addAll(iterable $elements): ImmutableList;

	/**
	 * Returns a new collection without elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableList<E> The new immutable collection
	 */
	#[NoDiscard]
	public function removeIf(Closure $predicate): ImmutableList;

	/**
	 * Returns a new collection with all occurrences of every element in the given iterable removed.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return ImmutableList<E> The new immutable collection
	 */
	#[NoDiscard]
	public function removeAll(iterable $elements): ImmutableList;

	/**
	 * Returns a new collection without the first occurrence of the specified element.
	 *
	 * @param E $element The element to remove
	 * @return ImmutableList<E> The new immutable collection
	 */
	#[NoDiscard]
	public function removeElement(mixed $element): ImmutableList;

	/**
	 * Returns a new collection without the first element.
	 * Returns the same collection if empty.
	 *
	 * @return ImmutableList<E> The new immutable collection
	 */
	#[NoDiscard]
	public function removeFirst(): ImmutableList;

	/**
	 * Returns a new collection without the last element.
	 * Returns the same collection if empty.
	 *
	 * @return ImmutableList<E> The new immutable collection
	 */
	#[NoDiscard]
	public function removeLast(): ImmutableList;

	/**
	 * Returns a new collection retaining only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return ImmutableList<E> The new immutable collection
	 */
	#[NoDiscard]
	public function retainAll(iterable $elements): ImmutableList;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return ImmutableList<E> The collection for chaining
	 */
	public function forEach(Closure $action): ImmutableList;

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
