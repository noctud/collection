<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Exception\UnsupportedOperationException;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\List\ListInterface;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\Set;
use NoDiscard;

/**
 * Ordered read-only collection of elements.
 * Common interface for sequence-like and set-like collections.
 *
 * @template E
 * @extends IteratorAggregate<int,E>
 */
interface Collection extends IteratorAggregate, Countable, JsonSerializable
{
	// --- Element Access ---

	/**
	 * Returns the first element in the collection.
	 * Throws if empty.
	 *
	 * @return E
	 * @throws NoSuchElementException
	 */
	public function first();

	/**
	 * Returns the first element in the collection, or null if empty.
	 *
	 * @return E|null
	 */
	public function firstOrNull(): mixed;

	/**
	 * Returns the last element in the collection.
	 * Throws if empty.
	 *
	 * @return E
	 * @throws NoSuchElementException
	 */
	public function last();

	/**
	 * Returns the last element in the collection, or null if empty.
	 *
	 * @return E|null
	 */
	public function lastOrNull(): mixed;

	/**
	 * Returns the single element in the collection.
	 * Throws if the collection is empty or has more than one element.
	 *
	 * @return E
	 * @throws NoSuchElementException
	 */
	public function single();

	/**
	 * Returns the single element in the collection.
	 * Returns null if the collection is empty or has more than one element.
	 *
	 * @return E|null
	 */
	public function singleOrNull(): mixed;

	/**
	 * Returns the first element matching the predicate, or null if no element matches.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return E|null
	 */
	public function find(Closure $predicate): mixed;

	/**
	 * Returns the last element matching the predicate, or null if no element matches.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return E|null
	 */
	public function findLast(Closure $predicate): mixed;

	/**
	 * Returns the first element matching the predicate.
	 * Throws if no element matches.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return E
	 * @throws NoSuchElementException
	 */
	public function expect(Closure $predicate);

	/**
	 * Returns the last element matching the predicate.
	 * Throws if no element matches.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return E
	 * @throws NoSuchElementException
	 */
	public function expectLast(Closure $predicate);

	/**
	 * Returns a random element or throws if empty.
	 *
	 * @return E
	 * @throws NoSuchElementException
	 */
	public function random();

	/**
	 * Returns a random element or null if empty.
	 *
	 * @return E|null
	 */
	public function randomOrNull(): mixed;

	// --- Querying ---

	/**
	 * Whether the collection does not contain any elements.
	 */
	public function isEmpty(): bool;

	/**
	 * Whether the collection contains at least one element.
	 */
	public function isNotEmpty(): bool;

	/**
	 * Whether the collection contains a value (strict comparison).
	 *
	 * @param E $element
	 */
	public function contains(mixed $element): bool;

	/**
	 * Whether the collection contains all the provided values.
	 *
	 * @param iterable<E> $elements
	 */
	public function containsAll(iterable $elements): bool;

	/**
	 * Returns true if all elements match the predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 */
	public function all(Closure $predicate): bool;

	/**
	 * Returns true if any element matches the predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 */
	public function any(Closure $predicate): bool;

	/**
	 * Returns true if no element matches the predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 */
	public function none(Closure $predicate): bool;

	/**
	 * Returns the number of elements in the collection.
	 */
	public function count(): int;

	/**
	 * Returns the number of elements matching the predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return int<0, max>
	 */
	public function countWhere(Closure $predicate): int;

	// --- Aggregation ---

	/**
	 * Left fold. Accumulates a result starting from the initial value
	 * by applying the operation to each element sequentially.
	 *
	 * @template R
	 * @param R $initial
	 * @param Closure(R, E):R $operation
	 * @return R
	 */
	public function fold(mixed $initial, Closure $operation): mixed;

	/**
	 * Reduce with a binary operation.
	 *
	 * @param Closure(E, E):E $operation
	 * @throws UnsupportedOperationException
	 * @return E
	 */
	public function reduce(Closure $operation);

	/**
	 * Reduces the collection using a binary operation, or returns null if the collection is empty.
	 *
	 * @param Closure(E, E):E $operation
	 * @return E|null
	 */
	public function reduceOrNull(Closure $operation): mixed;

	/**
	 * Returns the sum of all elements or values returned by the selector.
	 *
	 * @template TSum
	 * @param (Closure(E, int):TSum)|null $selector
	 * @return ($selector is null ? (E is int ? int : int|float) : (TSum is int ? int : int|float))
	 */
	public function sum(?Closure $selector = null): int|float;

	/**
	 * Returns the average of all elements or values returned by the selector.
	 * Throws if the collection is empty.
	 *
	 * @param Closure(E, int):(int|float)|null $selector
	 * @throws UnsupportedOperationException
	 */
	public function avg(?Closure $selector = null): float;

	/**
	 * Returns the average of all elements or values returned by the selector, or null if empty.
	 *
	 * @param Closure(E, int):(int|float)|null $selector
	 */
	public function avgOrNull(?Closure $selector = null): float|null;

	/**
	 * Returns the element with the minimum value.
	 * When a selector is given, returns the element whose selector value is minimum.
	 * Throws if the collection is empty.
	 *
	 * @param Closure(E, int):mixed|null $selector
	 * @return E
	 * @throws NoSuchElementException
	 */
	public function min(?Closure $selector = null): mixed;

	/**
	 * Returns the element with the minimum value, or null if empty.
	 * When a selector is given, returns the element whose selector value is minimum.
	 *
	 * @param Closure(E, int):mixed|null $selector
	 * @return E|null
	 */
	public function minOrNull(?Closure $selector = null): mixed;

	/**
	 * Returns the element with the maximum value.
	 * When a selector is given, returns the element whose selector value is maximum.
	 * Throws if the collection is empty.
	 *
	 * @param Closure(E, int):mixed|null $selector
	 * @return E
	 * @throws NoSuchElementException
	 */
	public function max(?Closure $selector = null): mixed;

	/**
	 * Returns the element with the maximum value, or null if empty.
	 * When a selector is given, returns the element whose selector value is maximum.
	 *
	 * @param Closure(E, int):mixed|null $selector
	 * @return E|null
	 */
	public function maxOrNull(?Closure $selector = null): mixed;

	/**
	 * Returns the minimum value produced by the selector.
	 * Throws if the collection is empty.
	 *
	 * @template R of mixed
	 * @param Closure(E, int):R $selector
	 * @return R
	 * @throws NoSuchElementException
	 */
	public function minOf(Closure $selector): mixed;

	/**
	 * Returns the minimum value produced by the selector, or null if empty.
	 *
	 * @template R of mixed
	 * @param Closure(E, int):R $selector
	 * @return R|null
	 */
	public function minOfOrNull(Closure $selector): mixed;

	/**
	 * Returns the maximum value produced by the selector.
	 * Throws if the collection is empty.
	 *
	 * @template R of mixed
	 * @param Closure(E, int):R $selector
	 * @return R
	 * @throws NoSuchElementException
	 */
	public function maxOf(Closure $selector): mixed;

	/**
	 * Returns the maximum value produced by the selector, or null if empty.
	 *
	 * @template R of mixed
	 * @param Closure(E, int):R $selector
	 * @return R|null
	 */
	public function maxOfOrNull(Closure $selector): mixed;

	/**
	 * Joins elements into a string with the given separator, prefix, postfix, and optional transform.
	 *
	 * When no transform is provided, elements are converted to strings using (string) cast.
	 * Scalars, null, and Stringable objects are supported. Non-stringable objects and arrays
	 * will throw a ConversionException.
	 *
	 * @param Closure(E, int):string|null $transform Optional transform to apply to each element
	 * @throws ConversionException When an element cannot be converted to string and no transform is provided
	 */
	public function joinToString(string $separator = ', ', string $prefix = '', string $postfix = '', int $limit = -1, string $truncated = '...', ?Closure $transform = null): string;

	/**
	 * Groups elements by the key returned by the selector and counts elements in each group.
	 *
	 * @template NK of string|int|bool|float|object
	 * @param Closure(E, int):NK $keySelector
	 * @return ImmutableMap<NK, int>
	 */
	#[NoDiscard]
	public function countBy(Closure $keySelector): ImmutableMap;

	// --- Transformation ---

	/**
	 * Filter elements by predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): Collection;

	/**
	 * Filter non-null elements.
	 *
	 * @return Collection<(E is null ? never : E)>
	 */
	#[NoDiscard]
	public function filterNotNull(): Collection;

	/**
	 * Filter elements that are instances of the given class or interface.
	 *
	 * @template T
	 * @param class-string<T> $type
	 * @return Collection<T>
	 */
	#[NoDiscard]
	public function filterInstanceOf(string $type): Collection;

	/**
	 * Map elements to a new collection.
	 * The transform receives the element and optionally the index.
	 *
	 * @template R
	 * @param Closure(E, int):R $transform
	 * @return Collection<R>
	 */
	#[NoDiscard]
	public function map(Closure $transform): Collection;

	/**
	 * Transforms each element using the given function and excludes null results.
	 * Combines map and filterNotNull in a single operation.
	 *
	 * @template R
	 * @param Closure(E, int):(R|null) $transform
	 * @return Collection<R>
	 */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): Collection;

	/**
	 * Flat map elements to a new collection.
	 *
	 * @template R
	 * @param Closure(E, int):iterable<R> $transform
	 * @return Collection<R>
	 */
	#[NoDiscard]
	public function flatMap(Closure $transform): Collection;

	/**
	 * Flatten a collection of iterables into a single collection.
	 * Iterable elements are flattened one level; non-iterable elements are kept as-is.
	 * The array{} in value-of keeps the type resolvable when E is never (empty collections).
	 *
	 * @return Collection<(E is iterable<mixed> ? value-of<E|array{}> : E)>
	 */
	#[NoDiscard]
	public function flatten(): Collection;

	/**
	 * Take the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): Collection;

	/**
	 * Drops the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): Collection;

	/**
	 * Take the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): Collection;

	/**
	 * Drops the last N elements.
	 *
	 * @param non-negative-int $n
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): Collection;

	/**
	 * Takes elements while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): Collection;

	/**
	 * Drops elements while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): Collection;

	/**
	 * Takes elements from the end while the predicate is true.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): Collection;

	/**
	 * Drops elements from the end while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): Collection;

	/**
	 * Distinct elements by identity.
	 *
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function distinct(): Collection;

	/**
	 * Distinct elements by selector.
	 *
	 * @template K
	 * @param Closure(E, int):K $selector
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function distinctBy(Closure $selector): Collection;

	/**
	 * Split into chunks of the given size. The last chunk may be smaller.
	 *
	 * @param positive-int $size
	 * @return ListInterface<ListInterface<E>>
	 */
	#[NoDiscard]
	public function chunked(int $size): ListInterface;

	/**
	 * Returns a list of snapshots of the window of the given size
	 * sliding along this collection with the given step.
	 * When $partialWindows is true, includes smaller windows at the end.
	 *
	 * @param positive-int $size
	 * @param positive-int $step
	 * @return ListInterface<ListInterface<E>>
	 */
	#[NoDiscard]
	public function windowed(int $size, int $step = 1, bool $partialWindows = false): ListInterface;

	/**
	 * Combines this collection with another iterable by pairing elements at the same position.
	 * The resulting collection has the length of the shorter input.
	 *
	 * The other side is pulled in lockstep rather than copied. A Generator resumes from its
	 * current position, so the head of a stream can be consumed before zipping the rest; any
	 * other iterator is rewound first, and an already advanced one therefore restarts from its
	 * first element - wrap it in a NoRewindIterator to resume it instead.
	 *
	 * @template U
	 * @param iterable<U> $other
	 * @return ListInterface<array{E, U}>
	 */
	#[NoDiscard]
	public function zip(iterable $other): ListInterface;

	/**
	 * Returns a list of pairs of each two adjacent elements in this collection.
	 * If the collection has fewer than two elements, returns an empty list.
	 *
	 * @return ListInterface<array{E, E}>
	 */
	#[NoDiscard]
	public function zipWithNext(): ListInterface;

	/**
	 * Splits a collection of pairs into two lists — one from the first component, one from the second.
	 * This is the inverse of zip().
	 *
	 * @return array{ImmutableList<mixed>, ImmutableList<mixed>}
	 */
	#[NoDiscard]
	public function unzip(): array;

	/**
	 * Splits the collection into two collections based on a predicate.
	 * The first collection contains elements matching the predicate,
	 * the second contains the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return array{Collection<E>, Collection<E>}
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
	 * @return ($valueTransform is null ? ImmutableMap<K, Collection<E>> : ImmutableMap<K, ImmutableList<V>>)
	 */
	#[NoDiscard]
	public function groupBy(Closure $keySelector, ?Closure $valueTransform = null): ImmutableMap;

	/**
	 * Returns a set containing elements from this collection whose hashes match elements in the given iterable.
	 * Matching elements retain the instances and types from this collection.
	 *
	 * @param iterable<mixed> $other
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function intersect(iterable $other): Set;

	/**
	 * Returns a set containing all elements from both this collection and the given iterable.
	 *
	 * @template NE
	 * @param iterable<NE> $other
	 * @return Set<E|NE>
	 */
	#[NoDiscard]
	public function union(iterable $other): Set;

	/**
	 * Returns a set containing elements present in this collection but not in the given iterable.
	 *
	 * @param iterable<mixed> $other
	 * @return Set<E>
	 */
	#[NoDiscard]
	public function subtract(iterable $other): Set;

	// --- Ordering ---

	/**
	 * Returns a new collection with elements sorted in their natural order.
	 * Example: listOf(3, 1, 2)->sorted() // [1, 2, 3]
	 *
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function sorted(): Collection;

	/**
	 * Returns a new collection with elements sorted in descending natural order.
	 * Example: listOf(1, 3, 2)->sortedDesc() // [3, 2, 1]
	 *
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function sortedDesc(): Collection;

	/**
	 * Returns a new collection with elements sorted by a selector.
	 * Example: listOf($users)->sortedBy(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): Collection;

	/**
	 * Returns a new collection with elements sorted by a selector in descending order.
	 * Example: listOf($users)->sortedByDesc(fn($u) => $u->age)
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): Collection;

	/**
	 * Returns a new collection with elements sorted using a comparator.
	 * Example: listOf($users)->sortedWith(fn($u1, $u2) => $u1->age <=> $u2->age)
	 *
	 * @param Closure(E, E):int $comparator
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): Collection;

	/**
	 * Returns a new collection with elements in reversed order.
	 *
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function reversed(): Collection;

	/**
	 * Returns a new collection with elements in random order.
	 *
	 * @return Collection<E>
	 */
	#[NoDiscard]
	public function shuffled(): Collection;

	// --- Iteration ---

	/**
	 * Executes the given action for each element and returns the collection for chaining.
	 *
	 * @param Closure(E, int):void $action
	 * @return Collection<E>
	 */
	public function forEach(Closure $action): Collection;

	// --- Conversion ---

	/**
	 * Convert to Map using key and value selectors.
	 *
	 * @template K of string|int|bool|float|object
	 * @template V = E
	 * @param Closure(E, int):K $keySelector
	 * @param ?Closure(E, int):V $valueTransform
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function toMap(Closure $keySelector, ?Closure $valueTransform = null): ImmutableMap;

	/**
	 * Convert to an immutable list preserving iteration order.
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function toList(): ImmutableList;

	/**
	 * Convert to an immutable set (duplicates removed).
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function toSet(): ImmutableSet;

	/**
	 * Convert to a primitive PHP array preserving iteration order.
	 *
	 * @return list<E>
	 */
	#[NoDiscard]
	public function toArray(): array;

	/**
	 * Always creates and returns in-memory mutable copy of this collection with the same elements.
	 * Even if this collection is already mutable, a new copy is created.
	 *
	 * @return MutableCollection<E> The mutable copy of this collection.
	 */
	#[NoDiscard]
	public function toMutable(): MutableCollection;

	/**
	 * Returns an immutable collection.
	 * If this collection is already immutable, it may return itself.
	 *
	 * @return ImmutableCollection<E> The immutable copy of this collection.
	 */
	#[NoDiscard]
	public function toImmutable(): ImmutableCollection;
}
