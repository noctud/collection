<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set;

use Closure;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\WritableCollection;
use NoDiscard;

/**
 * Writable set provides basic methods for read/write operations on a set.
 * Implementations that cannot support ordering operations (sort, reverse, shuffle)
 * should implement this interface instead of MutableSet.
 *
 * @template E
 * @extends Set<E>
 * @extends WritableCollection<E>
 * @method WritableSet<E> onEach(Closure(E, int):void $action) Executes the given action for each element and returns the collection itself for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 */
interface WritableSet extends Set, WritableCollection
{
	// --- Tracking ---

	/**
	 * Returns a tracked version of this set that tracks whether mutations change the set.
	 * The tracked set shares the same underlying store.
	 *
	 * @return WritableTrackedSet<E>
	 */
	#[NoDiscard]
	public function tracked(): WritableTrackedSet;

	// --- Narrowing Start (auto-generated) ---

	/**
	 * Adds a single element to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param E $element The element to add
	 * @return WritableSet<E> The collection itself for chaining
	 */
	public function add(mixed $element): WritableSet;

	/**
	 * Adds all elements from an iterable source to the collection.
	 *
	 * Note: Mutable collections enforce strict typing - you can only add elements
	 * of the declared type E. Use ImmutableCollection if you need type widening.
	 *
	 * @param iterable<E> $elements The elements to add
	 * @return WritableSet<E> The collection itself for chaining
	 */
	public function addAll(iterable $elements): WritableSet;

	/**
	 * Removes the first occurrence of the specified element from the collection.
	 *
	 * @param E $element The element to remove
	 * @return WritableSet<E> The collection itself for chaining
	 */
	public function removeElement(mixed $element): WritableSet;

	/**
	 * Removes all elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return WritableSet<E> The collection itself for chaining
	 */
	public function removeIf(Closure $predicate): WritableSet;

	/**
	 * Removes all occurrences of every element in the given iterable from the collection.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return WritableSet<E> The collection itself for chaining
	 */
	public function removeAll(iterable $elements): WritableSet;

	/**
	 * Removes the first element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableSet<E> The collection itself for chaining
	 */
	public function removeFirst(): WritableSet;

	/**
	 * Removes the last element from the collection.
	 * No-op if empty.
	 *
	 * @return WritableSet<E> The collection itself for chaining
	 */
	public function removeLast(): WritableSet;

	/**
	 * Retains only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return WritableSet<E> The collection itself for chaining
	 */
	public function retainAll(iterable $elements): WritableSet;

	/**
	 * Removes all elements from the collection.
	 *
	 * @return WritableSet<E> The collection itself for chaining
	 */
	public function clear(): WritableSet;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return WritableSet<E> The collection itself for chaining
	 */
	public function forEach(Closure $action): WritableSet;

	/**
	 * Filter elements by predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): ImmutableSet;

	/**
	 * Filter non-null elements.
	 *
	 * @return ImmutableSet<(E is null ? never : E)>
	 */
	#[NoDiscard]
	public function filterNotNull(): ImmutableSet;

	/**
	 * Filter elements that are instances of the given class or interface.
	 *
	 * @template T
	 * @param class-string<T> $type
	 * @return ImmutableSet<T>
	 */
	#[NoDiscard]
	public function filterInstanceOf(string $type): ImmutableSet;

	/**
	 * Map elements to a new collection.
	 * The transform receives the element and optionally the index.
	 *
	 * @template R
	 * @param Closure(E, int):R $transform
	 * @return ImmutableSet<R>
	 */
	#[NoDiscard]
	public function map(Closure $transform): ImmutableSet;

	/**
	 * Transforms each element using the given function and excludes null results.
	 * Combines map and filterNotNull in a single operation.
	 *
	 * @template R
	 * @param Closure(E, int):(R|null) $transform
	 * @return ImmutableSet<R>
	 */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): ImmutableSet;

	/**
	 * Flat map elements to a new collection.
	 *
	 * @template R
	 * @param Closure(E, int):iterable<R> $transform
	 * @return ImmutableSet<R>
	 */
	#[NoDiscard]
	public function flatMap(Closure $transform): ImmutableSet;

	/**
	 * Flatten a collection of iterables into a single collection.
	 * Iterable elements are flattened one level; non-iterable elements are kept as-is.
	 * The array{} in value-of keeps the type resolvable when E is never (empty collections).
	 *
	 * @return ImmutableSet<(E is iterable<mixed> ? value-of<E|array{}> : E)>
	 */
	#[NoDiscard]
	public function flatten(): ImmutableSet;

	/**
	 * Take the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): ImmutableSet;

	/**
	 * Drops the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): ImmutableSet;

	/**
	 * Take the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): ImmutableSet;

	/**
	 * Drops the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): ImmutableSet;

	/**
	 * Takes elements while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): ImmutableSet;

	/**
	 * Drops elements while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): ImmutableSet;

	/**
	 * Takes elements from the end while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): ImmutableSet;

	/**
	 * Drops elements from the end while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): ImmutableSet;

	/**
	 * Distinct elements by identity.
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function distinct(): ImmutableSet;

	/**
	 * Distinct elements by selector.
	 *
	 * @template K
	 * @param Closure(E, int):K $selector
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function distinctBy(Closure $selector): ImmutableSet;

	/**
	 * Returns a new collection with elements sorted in their natural order.
	 * Example: listOf(3, 1, 2)->sorted() // [1, 2, 3]
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function sorted(): ImmutableSet;

	/**
	 * Returns a new collection with elements sorted in descending natural order.
	 * Example: listOf(1, 3, 2)->sortedDesc() // [3, 2, 1]
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function sortedDesc(): ImmutableSet;

	/**
	 * Returns a new collection with elements sorted by a selector.
	 * Example: listOf($users)->sortedBy(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): ImmutableSet;

	/**
	 * Returns a new collection with elements sorted by a selector in descending order.
	 * Example: listOf($users)->sortedByDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): ImmutableSet;

	/**
	 * Returns a new collection with elements sorted using a comparator.
	 * Example: listOf($users)->sortedWith(fn($u1, $u2) => $u1->age <=> $u2->age)
	 *
	 * @param Closure(E, E):int $comparator
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): ImmutableSet;

	/**
	 * Returns a new collection with elements in reversed order.
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function reversed(): ImmutableSet;

	/**
	 * Returns a new collection with elements in random order.
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function shuffled(): ImmutableSet;

	/**
	 * Splits the collection into two collections based on a predicate.
	 * The first collection contains elements matching the predicate,
	 * the second contains the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return array{ImmutableSet<E>, ImmutableSet<E>}
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
	 * @return ($valueTransform is null ? ImmutableMap<K, ImmutableSet<E>> : ImmutableMap<K, ImmutableList<V>>)
	 */
	#[NoDiscard]
	public function groupBy(Closure $keySelector, ?Closure $valueTransform = null): ImmutableMap;

	// --- Narrowing End (auto-generated) ---
}
