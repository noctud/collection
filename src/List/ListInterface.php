<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List;

use ArrayAccess;
use Closure;
use Noctud\Collection\Collection;
use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\Map\ImmutableMap;
use NoDiscard;
use ReturnTypeWillChange;

/**
 * Ordered collection of elements.
 *
 * @template E
 * @extends Collection<E>
 * @extends ArrayAccess<int,E>
 */
interface ListInterface extends Collection, ArrayAccess
{
	// --- Element Access ---

	/**
	 * Returns the element at the specified index, or throws if out of bounds.
	 *
	 * @throws IndexOutOfBoundsException
	 * @return E
	 */
	public function get(int $index);

	/**
	 * Returns the element at the specified index, or null if out of bounds.
	 *
	 * @return E|null
	 */
	public function getOrNull(int $index): mixed;

	/**
	 * Returns the element at the specified index, or throws if out of bounds.
	 * Alias of get() for array access syntax `$list[0]`.
	 * The native return type is omitted for compatibility with existing implementations.
	 *
	 * @param int $offset
	 * @return E
	 * @throws IndexOutOfBoundsException
	 */
	#[ReturnTypeWillChange]
	public function offsetGet(mixed $offset);

	/**
	 * Returns the element at the specified index, or the default value if out of bounds.
	 *
	 * @template D
	 * @param D $default
	 * @return E|D
	 */
	public function getOrDefault(int $index, mixed $default): mixed;

	/**
	 * Returns the element at the specified index, or computes a value if out of bounds.
	 *
	 * @template D
	 * @param Closure():D $compute
	 * @return E|D
	 */
	public function getOrCompute(int $index, Closure $compute): mixed;

	/**
	 * Returns the index of the first occurrence of the specified element using strict comparison, or -1 if not found.
	 */
	public function indexOf(mixed $element): int;

	/**
	 * Returns the index of the last occurrence of the specified element using strict comparison, or -1 if not found.
	 */
	public function lastIndexOf(mixed $element): int;

	/**
	 * Returns the index of the first element matching the predicate, or -1 if not found.
	 *
	 * @param Closure(E, int):bool $predicate
	 */
	public function indexOfFirst(Closure $predicate): int;

	/**
	 * Returns the index of the last element matching the predicate, or -1 if not found.
	 *
	 * @param Closure(E, int):bool $predicate
	 */
	public function indexOfLast(Closure $predicate): int;

	/**
	 * Creates a view of the list from index 'from' (inclusive) to 'to' (exclusive).
	 *
	 * @param non-negative-int $from Starting index (inclusive)
	 * @param non-negative-int $to Ending index (exclusive)
	 * @return ListInterface<E> New list containing the sliced elements
	 * @throws IndexOutOfBoundsException If indices are out of bounds
	 */
	#[NoDiscard]
	public function slice(int $from, int $to): ListInterface;

	// --- Conversion ---

	/**
	 * Convert to Map using index as the key.
	 *
	 * @template V = E
	 * @param ?Closure(E, int):V $valueTransform
	 * @return ImmutableMap<int,V>
	 */
	#[NoDiscard]
	public function toIndexedMap(?Closure $valueTransform = null): ImmutableMap;

	// --- Narrowing Start (auto-generated) ---

	/**
	 * Filter elements by predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): ListInterface;

	/**
	 * Filter non-null elements.
	 *
	 * @return ListInterface<(E is null ? never : E)>
	 */
	#[NoDiscard]
	public function filterNotNull(): ListInterface;

	/**
	 * Filter elements that are instances of the given class or interface.
	 *
	 * @template T
	 * @param class-string<T> $type
	 * @return ListInterface<T>
	 */
	#[NoDiscard]
	public function filterInstanceOf(string $type): ListInterface;

	/**
	 * Map elements to a new collection.
	 * The transform receives the element and optionally the index.
	 *
	 * @template R
	 * @param Closure(E, int):R $transform
	 * @return ListInterface<R>
	 */
	#[NoDiscard]
	public function map(Closure $transform): ListInterface;

	/**
	 * Transforms each element using the given function and excludes null results.
	 * Combines map and filterNotNull in a single operation.
	 *
	 * @template R
	 * @param Closure(E, int):(R|null) $transform
	 * @return ListInterface<R>
	 */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): ListInterface;

	/**
	 * Flat map elements to a new collection.
	 *
	 * @template R
	 * @param Closure(E, int):iterable<R> $transform
	 * @return ListInterface<R>
	 */
	#[NoDiscard]
	public function flatMap(Closure $transform): ListInterface;

	/**
	 * Flatten a collection of iterables into a single collection.
	 * Iterable elements are flattened one level; non-iterable elements are kept as-is.
	 * The array{} in value-of keeps the type resolvable when E is never (empty collections).
	 *
	 * @return ListInterface<(E is iterable<mixed> ? value-of<E|array{}> : E)>
	 */
	#[NoDiscard]
	public function flatten(): ListInterface;

	/**
	 * Take the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): ListInterface;

	/**
	 * Drops the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): ListInterface;

	/**
	 * Take the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): ListInterface;

	/**
	 * Drops the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): ListInterface;

	/**
	 * Takes elements while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): ListInterface;

	/**
	 * Drops elements while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): ListInterface;

	/**
	 * Takes elements from the end while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): ListInterface;

	/**
	 * Drops elements from the end while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): ListInterface;

	/**
	 * Distinct elements by identity.
	 *
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function distinct(): ListInterface;

	/**
	 * Distinct elements by selector.
	 *
	 * @template K
	 * @param Closure(E, int):K $selector
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function distinctBy(Closure $selector): ListInterface;

	/**
	 * Returns a new collection with elements sorted in their natural order.
	 * Example: listOf(3, 1, 2)->sorted() // [1, 2, 3]
	 *
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function sorted(): ListInterface;

	/**
	 * Returns a new collection with elements sorted in descending natural order.
	 * Example: listOf(1, 3, 2)->sortedDesc() // [3, 2, 1]
	 *
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function sortedDesc(): ListInterface;

	/**
	 * Returns a new collection with elements sorted by a selector.
	 * Example: listOf($users)->sortedBy(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): ListInterface;

	/**
	 * Returns a new collection with elements sorted by a selector in descending order.
	 * Example: listOf($users)->sortedByDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): ListInterface;

	/**
	 * Returns a new collection with elements sorted using a comparator.
	 * Example: listOf($users)->sortedWith(fn($u1, $u2) => $u1->age <=> $u2->age)
	 *
	 * @param Closure(E, E):int $comparator
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): ListInterface;

	/**
	 * Returns a new collection with elements in reversed order.
	 *
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function reversed(): ListInterface;

	/**
	 * Returns a new collection with elements in random order.
	 *
	 * @return ListInterface<E>
	 */
	#[NoDiscard]
	public function shuffled(): ListInterface;

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return ListInterface<E>
	 */
	public function forEach(Closure $action): ListInterface;

	/**
	 * Splits the collection into two collections based on a predicate.
	 * The first collection contains elements matching the predicate,
	 * the second contains the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return array{ListInterface<E>, ListInterface<E>}
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
	 * @return ($valueTransform is null ? ImmutableMap<K, ListInterface<E>> : ImmutableMap<K, ImmutableList<V>>)
	 */
	#[NoDiscard]
	public function groupBy(Closure $keySelector, ?Closure $valueTransform = null): ImmutableMap;

	/**
	 * Returns an immutable collection.
	 * If this collection is already immutable, it may return itself.
	 *
	 * @return ImmutableList<E> The immutable copy of this collection.
	 */
	#[NoDiscard]
	public function toImmutable(): ImmutableList;

	// --- Narrowing End (auto-generated) ---
}
