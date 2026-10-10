<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

use Closure;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Map\ImmutableMap;
use NoDiscard;

/**
 * @template E
 * @extends Collection<E>
 * @method WritableCollection<E> onEach(Closure(E, int):void $action) Executes the given action for each element and returns the collection itself for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 */
interface WritableCollection extends Collection
{
	// --- Tracking ---

	/**
	 * Returns a tracked version of this collection that tracks whether mutations change the collection.
	 * The tracked collection shares the same underlying store.
	 *
	 * @return WritableTrackedCollection<E>
	 */
	#[NoDiscard]
	public function tracked(): WritableTrackedCollection;

	// --- Mutation: Add ---

	/**
	 * Adds a single element to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param E $element The element to add
	 * @return WritableCollection<E> The collection itself for chaining
	 */
	public function add(mixed $element): WritableCollection;

	/**
	 * Adds all elements from an iterable source to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param iterable<E> $elements The elements to add
	 * @return WritableCollection<E> The collection itself for chaining
	 */
	public function addAll(iterable $elements): WritableCollection;

	// --- Mutation: Remove ---

	/**
	 * Removes the first occurrence of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return WritableCollection<E> The collection itself for chaining
	 */
	public function removeElement(mixed $element): WritableCollection;

	/**
	 * Removes all elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return WritableCollection<E> The collection itself for chaining
	 */
	public function removeIf(Closure $predicate): WritableCollection;

	/**
	 * Removes all occurrences of every element in the given iterable from the collection.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return WritableCollection<E> The collection itself for chaining
	 */
	public function removeAll(iterable $elements): WritableCollection;

	/**
	 * Removes the first element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableCollection<E> The collection itself for chaining
	 */
	public function removeFirst(): WritableCollection;

	/**
	 * Removes the last element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableCollection<E> The collection itself for chaining
	 */
	public function removeLast(): WritableCollection;

	/**
	 * Retains only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return WritableCollection<E> The collection itself for chaining
	 */
	public function retainAll(iterable $elements): WritableCollection;

	/**
	 * Removes all elements from the collection.
	 *
	 * @return WritableCollection<E> The collection itself for chaining
	 */
	public function clear(): WritableCollection;

	// --- Iteration ---

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return WritableCollection<E> The collection itself for chaining
	 */
	public function forEach(Closure $action): WritableCollection;

	// --- Narrowing Start (auto-generated) ---

	/**
	 * Filter elements by predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): ImmutableCollection;

	/**
	 * Filter non-null elements.
	 *
	 * @return ImmutableCollection<(E is null ? never : E)>
	 */
	#[NoDiscard]
	public function filterNotNull(): ImmutableCollection;

	/**
	 * Filter elements that are instances of the given class or interface.
	 *
	 * @template T
	 * @param class-string<T> $type
	 * @return ImmutableCollection<T>
	 */
	#[NoDiscard]
	public function filterInstanceOf(string $type): ImmutableCollection;

	/**
	 * Map elements to a new collection.
	 * The transform receives the element and optionally the index.
	 *
	 * @template R
	 * @param Closure(E, int):R $transform
	 * @return ImmutableCollection<R>
	 */
	#[NoDiscard]
	public function map(Closure $transform): ImmutableCollection;

	/**
	 * Transforms each element using the given function and excludes null results.
	 * Combines map and filterNotNull in a single operation.
	 *
	 * @template R
	 * @param Closure(E, int):(R|null) $transform
	 * @return ImmutableCollection<R>
	 */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): ImmutableCollection;

	/**
	 * Flat map elements to a new collection.
	 *
	 * @template R
	 * @param Closure(E, int):iterable<R> $transform
	 * @return ImmutableCollection<R>
	 */
	#[NoDiscard]
	public function flatMap(Closure $transform): ImmutableCollection;

	/**
	 * Flatten a collection of iterables into a single collection.
	 * Iterable elements are flattened one level; non-iterable elements are kept as-is.
	 * The array{} in value-of keeps the type resolvable when E is never (empty collections).
	 *
	 * @return ImmutableCollection<(E is iterable<mixed> ? value-of<E|array{}> : E)>
	 */
	#[NoDiscard]
	public function flatten(): ImmutableCollection;

	/**
	 * Take the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): ImmutableCollection;

	/**
	 * Drops the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): ImmutableCollection;

	/**
	 * Take the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): ImmutableCollection;

	/**
	 * Drops the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): ImmutableCollection;

	/**
	 * Takes elements while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): ImmutableCollection;

	/**
	 * Drops elements while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): ImmutableCollection;

	/**
	 * Takes elements from the end while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): ImmutableCollection;

	/**
	 * Drops elements from the end while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): ImmutableCollection;

	/**
	 * Distinct elements by identity.
	 *
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function distinct(): ImmutableCollection;

	/**
	 * Distinct elements by selector.
	 *
	 * @template K
	 * @param Closure(E, int):K $selector
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function distinctBy(Closure $selector): ImmutableCollection;

	/**
	 * Returns a new collection with elements sorted in their natural order.
	 * Example: listOf(3, 1, 2)->sorted() // [1, 2, 3]
	 *
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function sorted(): ImmutableCollection;

	/**
	 * Returns a new collection with elements sorted in descending natural order.
	 * Example: listOf(1, 3, 2)->sortedDesc() // [3, 2, 1]
	 *
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function sortedDesc(): ImmutableCollection;

	/**
	 * Returns a new collection with elements sorted by a selector.
	 * Example: listOf($users)->sortedBy(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): ImmutableCollection;

	/**
	 * Returns a new collection with elements sorted by a selector in descending order.
	 * Example: listOf($users)->sortedByDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): ImmutableCollection;

	/**
	 * Returns a new collection with elements sorted using a comparator.
	 * Example: listOf($users)->sortedWith(fn($u1, $u2) => $u1->age <=> $u2->age)
	 *
	 * @param Closure(E, E):int $comparator
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): ImmutableCollection;

	/**
	 * Returns a new collection with elements in reversed order.
	 *
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function reversed(): ImmutableCollection;

	/**
	 * Returns a new collection with elements in random order.
	 *
	 * @return ImmutableCollection<E>
	 */
	#[NoDiscard]
	public function shuffled(): ImmutableCollection;

	/**
	 * Splits the collection into two collections based on a predicate.
	 * The first collection contains elements matching the predicate,
	 * the second contains the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return array{ImmutableCollection<E>, ImmutableCollection<E>}
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
	 * @return ($valueTransform is null ? ImmutableMap<K, ImmutableCollection<E>> : ImmutableMap<K, ImmutableList<V>>)
	 */
	#[NoDiscard]
	public function groupBy(Closure $keySelector, ?Closure $valueTransform = null): ImmutableMap;

	// --- Narrowing End (auto-generated) ---
}
