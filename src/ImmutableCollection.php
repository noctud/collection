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
 * @method ImmutableCollection<E> onEach(Closure(E, int):void $action) Executes the given action for each element and returns the collection for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 */
interface ImmutableCollection extends Collection
{
	// --- Mutation (returns new) ---

	/**
	 * Returns a new collection with the specified element added.
	 *
	 * Note: Immutable collections allow type widening - you can add an element
	 * of a different type, and the returned collection will have the union type.
	 *
	 * @template NE
	 *
	 * @param NE $element The element to add
	 * @return ImmutableCollection<E|NE> A new collection with the element added
	 */
	#[NoDiscard]
	public function add(mixed $element): ImmutableCollection;

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
	 * @return ImmutableCollection<E|NE> A new collection with the element first
	 */
	#[NoDiscard]
	public function addFirst(mixed $element): ImmutableCollection;

	/**
	 * Returns a new collection with all elements from the iterable added.
	 *
	 * Note: Immutable collections allow type widening - you can add elements
	 * of a different type, and the returned collection will have the union type.
	 *
	 * @template NE
	 *
	 * @param iterable<NE> $elements The elements to add
	 * @return ImmutableCollection<E|NE> A new collection with the elements added
	 */
	#[NoDiscard]
	public function addAll(iterable $elements): ImmutableCollection;

	/**
	 * Returns a new collection without elements matching the given predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ImmutableCollection<E> The new immutable collection
	 */
	#[NoDiscard]
	public function removeIf(Closure $predicate): ImmutableCollection;

	/**
	 * Returns a new collection with all occurrences of every element in the given iterable removed.
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return ImmutableCollection<E> The new immutable collection
	 */
	#[NoDiscard]
	public function removeAll(iterable $elements): ImmutableCollection;

	/**
	 * Returns a new collection without the first occurrence of the specified element.
	 *
	 * @param E $element The element to remove
	 * @return ImmutableCollection<E> The new immutable collection
	 */
	#[NoDiscard]
	public function removeElement(mixed $element): ImmutableCollection;

	/**
	 * Returns a new collection without the first element.
	 * Returns the same collection if empty.
	 *
	 * @return ImmutableCollection<E> The new immutable collection
	 */
	#[NoDiscard]
	public function removeFirst(): ImmutableCollection;

	/**
	 * Returns a new collection without the last element.
	 * Returns the same collection if empty.
	 *
	 * @return ImmutableCollection<E> The new immutable collection
	 */
	#[NoDiscard]
	public function removeLast(): ImmutableCollection;

	/**
	 * Returns a new collection retaining only elements that are present in the given iterable.
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return ImmutableCollection<E> The new immutable collection
	 */
	#[NoDiscard]
	public function retainAll(iterable $elements): ImmutableCollection;

	// --- Iteration ---

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return ImmutableCollection<E> The collection for chaining
	 */
	public function forEach(Closure $action): ImmutableCollection;

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
