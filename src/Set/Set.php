<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set;

use Closure;
use Noctud\Collection\Collection;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Map\ImmutableMap;
use NoDiscard;

/**
 * Ordered collection of unique elements.
 *
 * @template E
 * @extends Collection<E>
 * @method Set<E> onEach(Closure(E, int):void $action) Executes the given action for each element and returns the collection for chaining. Use it instead of forEach() wherever the result is used - forEach() returns void since 0.2.
 */
interface Set extends Collection
{
	// --- Narrowing Start (auto-generated) ---

	/**
	 * Filter elements by predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): Set;

	/**
	 * Filter non-null elements.
	 *
	 * @return Set<(E is null ? never : E)>
	 */
	#[NoDiscard]
	public function filterNotNull(): Set;

	/**
	 * Filter elements that are instances of the given class or interface.
	 *
	 * @template T
	 * @param class-string<T> $type
	 * @return Set<T>
	 */
	#[NoDiscard]
	public function filterInstanceOf(string $type): Set;

	/**
	 * Map elements to a new collection.
	 * The transform receives the element and optionally the index.
	 *
	 * @template R
	 * @param Closure(E, int):R $transform
	 * @return Set<R>
	 */
	#[NoDiscard]
	public function map(Closure $transform): Set;

	/**
	 * Transforms each element using the given function and excludes null results.
	 * Combines map and filterNotNull in a single operation.
	 *
	 * @template R
	 * @param Closure(E, int):(R|null) $transform
	 * @return Set<R>
	 */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): Set;

	/**
	 * Flat map elements to a new collection.
	 *
	 * @template R
	 * @param Closure(E, int):iterable<R> $transform
	 * @return Set<R>
	 */
	#[NoDiscard]
	public function flatMap(Closure $transform): Set;

	/**
	 * Flatten a collection of iterables into a single collection.
	 * Iterable elements are flattened one level; non-iterable elements are kept as-is.
	 * The array{} in value-of keeps the type resolvable when E is never (empty collections).
	 *
	 * @return Set<(E is iterable<mixed> ? value-of<E|array{}> : E)>
	 */
	#[NoDiscard]
	public function flatten(): Set;

	/**
	 * Take the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): Set;

	/**
	 * Drops the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): Set;

	/**
	 * Take the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): Set;

	/**
	 * Drops the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): Set;

	/**
	 * Takes elements while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): Set;

	/**
	 * Drops elements while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): Set;

	/**
	 * Takes elements from the end while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): Set;

	/**
	 * Drops elements from the end while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): Set;

	/**
	 * Distinct elements by identity.
	 *
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function distinct(): Set;

	/**
	 * Distinct elements by selector.
	 *
	 * @template K
	 * @param Closure(E, int):K $selector
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function distinctBy(Closure $selector): Set;

	/**
	 * Returns a new collection with elements sorted in their natural order.
	 * Example: listOf(3, 1, 2)->sorted() // [1, 2, 3]
	 *
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function sorted(): Set;

	/**
	 * Returns a new collection with elements sorted in descending natural order.
	 * Example: listOf(1, 3, 2)->sortedDesc() // [3, 2, 1]
	 *
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function sortedDesc(): Set;

	/**
	 * Returns a new collection with elements sorted by a selector.
	 * Example: listOf($users)->sortedBy(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): Set;

	/**
	 * Returns a new collection with elements sorted by a selector in descending order.
	 * Example: listOf($users)->sortedByDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): Set;

	/**
	 * Returns a new collection with elements sorted using a comparator.
	 * Example: listOf($users)->sortedWith(fn($u1, $u2) => $u1->age <=> $u2->age)
	 *
	 * @param Closure(E, E):int $comparator
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): Set;

	/**
	 * Returns a new collection with elements in reversed order.
	 *
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function reversed(): Set;

	/**
	 * Returns a new collection with elements in random order.
	 *
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function shuffled(): Set;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return Set<E>
	 */
	public function forEach(Closure $action): Set;

	/**
	 * Splits the collection into two collections based on a predicate.
	 * The first collection contains elements matching the predicate,
	 * the second contains the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return array{Set<E>, Set<E>}
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
	 * @return ($valueTransform is null ? ImmutableMap<K, Set<E>> : ImmutableMap<K, ImmutableList<V>>)
	 */
	#[NoDiscard]
	public function groupBy(Closure $keySelector, ?Closure $valueTransform = null): ImmutableMap;

	/**
	 * Returns an immutable collection.
	 * If this collection is already immutable, it may return itself.
	 *
	 * @return ImmutableSet<E> The immutable copy of this collection.
	 */
	#[NoDiscard]
	public function toImmutable(): ImmutableSet;

	// --- Narrowing End (auto-generated) ---
}
