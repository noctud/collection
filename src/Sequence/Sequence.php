<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Sequence;

use Closure;
use IteratorAggregate;
use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\Exception\InvalidSequenceSourceException;
use Noctud\Collection\Exception\NonReplayableSourceException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Exception\UnsupportedOperationException;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Set\ImmutableSet;
use NoDiscard;

/**
 * Lazily evaluated stream of values.
 *
 * Nothing is pulled from the source before the sequence is iterated (a foreach or a
 * terminal operation). A sequence guarantees at least one pass; whether it can be
 * iterated again depends on its source:
 *
 * - an array: the sequence replays, pulling fresh elements every pass;
 * - a Closure or an IteratorAggregate (e.g. a Collection): a producer, asked for an
 *   iterable on every pass. Whatever it does to build that iterable runs again each
 *   time - if it fires a SQL query, that query is re-executed on every iteration of
 *   the sequence. It must hand back a fresh iterator on each call; handing back the
 *   iterator of the previous pass (a getIterator() returning a Generator it keeps
 *   around, for instance) throws NonReplayableSourceException, and a Closure handing
 *   back something that is not an iterable throws InvalidSequenceSourceException;
 * - a raw Iterator/Generator: the sequence is single-pass and any further iteration
 *   throws NonReplayableSourceException (a partial pass counts as consumed).
 *
 * Keys are positional: every pass yields fresh 0..n keys, whatever the source yields.
 *
 * Deliberately neither Countable (native count($seq) stays a TypeError by design;
 * $seq->count() exists as an explicit O(n) terminal that drains a pass) nor
 * JsonSerializable (json_encode would be a hidden materialization) - materialize
 * explicitly with toList() or toArray() instead.
 *
 * @template E
 * @extends IteratorAggregate<int, E>
 */
interface Sequence extends IteratorAggregate
{
	// --- Transformation ---

	/**
	 * Filter elements by predicate.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Sequence<E>
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): Sequence;

	/**
	 * Filter non-null elements.
	 *
	 * @return Sequence<(E is null ? never : E)>
	 */
	#[NoDiscard]
	public function filterNotNull(): Sequence;

	/**
	 * Filter elements that are instances of the given class or interface.
	 *
	 * @template T
	 * @param class-string<T> $type
	 * @return Sequence<T>
	 */
	#[NoDiscard]
	public function filterInstanceOf(string $type): Sequence;

	/**
	 * Map elements to a new sequence.
	 * The transform receives the element and optionally the index.
	 *
	 * @template R
	 * @param Closure(E, int):R $transform
	 * @return Sequence<R>
	 */
	#[NoDiscard]
	public function map(Closure $transform): Sequence;

	/**
	 * Transforms each element using the given function and excludes null results.
	 * Combines map and filterNotNull in a single operation.
	 *
	 * @template R
	 * @param Closure(E, int):(R|null) $transform
	 * @return Sequence<R>
	 */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): Sequence;

	/**
	 * Flat map elements to a new sequence.
	 * Each iterable returned by the transform is consumed lazily, element by element.
	 *
	 * @template R
	 * @param Closure(E, int):iterable<R> $transform
	 * @return Sequence<R>
	 */
	#[NoDiscard]
	public function flatMap(Closure $transform): Sequence;

	/**
	 * Flatten a sequence of iterables into a single sequence.
	 * Iterable elements are flattened one level; non-iterable elements are kept as-is.
	 * The array{} in value-of keeps the type resolvable when E is never (empty sequences).
	 *
	 * @return Sequence<(E is iterable<mixed> ? value-of<E|array{}> : E)>
	 */
	#[NoDiscard]
	public function flatten(): Sequence;

	/**
	 * Take the first N elements.
	 * The source is not pulled any further once N elements have been yielded.
	 *
	 * @param non-negative-int $n
	 * @return Sequence<E>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): Sequence;

	/**
	 * Drops the first N elements.
	 *
	 * @param non-negative-int $n
	 * @return Sequence<E>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): Sequence;

	/**
	 * Takes elements while the predicate is true.
	 * The source is not pulled any further once the predicate has returned false.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Sequence<E>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): Sequence;

	/**
	 * Drops elements while the predicate is true, then returns the rest.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return Sequence<E>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): Sequence;

	/**
	 * Distinct elements by identity.
	 * Elements already seen during the current pass are skipped, so the memory held grows
	 * with the number of distinct elements.
	 *
	 * @return Sequence<E>
	 */
	#[NoDiscard]
	public function distinct(): Sequence;

	/**
	 * Distinct elements by selector.
	 *
	 * @template K
	 * @param Closure(E, int):K $selector
	 * @return Sequence<E>
	 */
	#[NoDiscard]
	public function distinctBy(Closure $selector): Sequence;

	/**
	 * Combines this sequence with another iterable by pairing elements at the same position.
	 * The resulting sequence has the length of the shorter input.
	 *
	 * The other side is pulled in lockstep rather than copied. A Generator resumes from its
	 * current position, so the head of a stream can be consumed before zipping the rest; any
	 * other iterator is rewound first, and an already advanced one therefore restarts from its
	 * first element - wrap it in a NoRewindIterator to resume it instead.
	 *
	 * @template U
	 * @param iterable<U> $other
	 * @return Sequence<array{E, U}>
	 */
	#[NoDiscard]
	public function zip(iterable $other): Sequence;

	/**
	 * Returns a sequence of pairs of each two adjacent elements in this sequence.
	 * If the sequence has fewer than two elements, yields nothing.
	 *
	 * @return Sequence<array{E, E}>
	 */
	#[NoDiscard]
	public function zipWithNext(): Sequence;

	// --- Iteration ---

	/**
	 * Returns a sequence applying the given action to each element as it goes through,
	 * then yielding the element unchanged.
	 *
	 * The lazy, chainable counterpart of forEach: the action runs once per element and per
	 * pass, while the element flows through the pipeline - nothing happens before a
	 * terminal operation pulls.
	 *
	 * @param Closure(E, int):void $action
	 * @return Sequence<E>
	 */
	#[NoDiscard]
	public function onEach(Closure $action): Sequence;

	// --- Element Access ---

	/**
	 * Returns the first element, consuming one pass. Pulls exactly one element.
	 *
	 * @return E
	 * @throws NoSuchElementException If the sequence is empty
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function first();

	/**
	 * Returns the first element, or null if the sequence is empty. Pulls exactly one element.
	 *
	 * @return E|null
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function firstOrNull(): mixed;

	/**
	 * Returns the last element, consuming one pass.
	 * Unlike its Collection counterpart this drains the whole sequence - the last element is
	 * only knowable at the end.
	 *
	 * @return E
	 * @throws NoSuchElementException If the sequence is empty
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function last();

	/**
	 * Returns the last element, or null if the sequence is empty. Drains the sequence.
	 *
	 * @return E|null
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function lastOrNull(): mixed;

	/**
	 * Returns the single element, consuming one pass. Pulls at most two elements: a second one
	 * existing is already an error.
	 *
	 * @return E
	 * @throws NoSuchElementException If the sequence is empty or holds more than one element
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function single();

	/**
	 * Returns the single element, or null if the sequence is empty or holds more than one.
	 *
	 * @return E|null
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function singleOrNull(): mixed;

	/**
	 * Returns the element at the given position, consuming one pass.
	 * Pulls up to that position and no further; there is no length to check the index against
	 * beforehand, so an index past the end is only known once the sequence runs out.
	 *
	 * @param non-negative-int $index
	 * @return E
	 * @throws IndexOutOfBoundsException If the sequence holds fewer elements than that or if the index is a negative int
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function elementAt(int $index);

	/**
	 * Returns the element at the given position, or null if the sequence is shorter than that.
	 *
	 * @param non-negative-int $index
	 * @return E|null
	 * @throws IndexOutOfBoundsException If the index is a negative int
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function elementAtOrNull(int $index): mixed;

	/**
	 * Returns the first element matching the predicate, or null if none does.
	 * Stops pulling at the first match.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return E|null
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function find(Closure $predicate): mixed;

	/**
	 * Returns the first element matching the predicate, throwing if none does.
	 * Stops pulling at the first match.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return E
	 * @throws NoSuchElementException If no element matches the predicate
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function expect(Closure $predicate);

	// --- Querying ---

	/**
	 * Whether the sequence does not contain any elements. Pulls exactly one element.
	 *
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function isEmpty(): bool;

	/**
	 * Whether the sequence contains at least one element. Pulls exactly one element.
	 *
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function isNotEmpty(): bool;

	/**
	 * Whether the sequence contains a value (strict comparison).
	 * Stops pulling at the first match.
	 *
	 * @param E $element
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function contains(mixed $element): bool;

	/**
	 * Whether the sequence contains all the provided values.
	 * Walks the sequence once, stopping as soon as none is left to look for.
	 *
	 * @param iterable<E> $elements
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function containsAll(iterable $elements): bool;

	/**
	 * Returns true if all elements match the predicate.
	 * Stops pulling at the first element that does not.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function all(Closure $predicate): bool;

	/**
	 * Returns true if any element matches the predicate.
	 * Stops pulling at the first match.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function any(Closure $predicate): bool;

	/**
	 * Returns true if no element matches the predicate.
	 * Stops pulling at the first match.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function none(Closure $predicate): bool;

	/**
	 * Returns the number of elements in the sequence, draining it.
	 * The O(n) counterpart of a Collection's O(1) count: a sequence has no length to read, only
	 * elements to pull, which is why the cost has to be asked for explicitly.
	 *
	 * @return int<0, max>
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function count(): int;

	/**
	 * Returns the number of elements matching the predicate, draining the sequence.
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return int<0, max>
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function countWhere(Closure $predicate): int;

	// --- Aggregation ---

	/**
	 * Left fold. Accumulates a result starting from the initial value by applying the operation
	 * to each element sequentially. Drains the sequence.
	 *
	 * @template R
	 * @param R $initial
	 * @param Closure(R, E):R $operation
	 * @return R
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function fold(mixed $initial, Closure $operation): mixed;

	/**
	 * Reduce with a binary operation, draining the sequence.
	 *
	 * @param Closure(E, E):E $operation
	 * @return E
	 * @throws UnsupportedOperationException If the sequence is empty
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function reduce(Closure $operation);

	/**
	 * Reduces the sequence using a binary operation, or returns null if it is empty.
	 * Drains the sequence.
	 *
	 * @param Closure(E, E):E $operation
	 * @return E|null
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function reduceOrNull(Closure $operation): mixed;

	/**
	 * Returns the sum of all elements or values returned by the selector. Drains the sequence.
	 *
	 * @template TSum
	 * @param (Closure(E, int):TSum)|null $selector
	 * @return ($selector is null ? (E is int ? int : int|float) : (TSum is int ? int : int|float))
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function sum(?Closure $selector = null): int|float;

	/**
	 * Returns the average of all elements or values returned by the selector, draining the
	 * sequence. Throws if it is empty.
	 *
	 * @param Closure(E, int):(int|float)|null $selector
	 * @throws UnsupportedOperationException If the sequence is empty
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function avg(?Closure $selector = null): float;

	/**
	 * Returns the average of all elements or values returned by the selector, or null if the
	 * sequence is empty. Drains the sequence.
	 *
	 * @param Closure(E, int):(int|float)|null $selector
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function avgOrNull(?Closure $selector = null): float|null;

	/**
	 * Returns the element with the minimum value, draining the sequence.
	 * When a selector is given, returns the element whose selector value is minimum.
	 *
	 * @param Closure(E, int):mixed|null $selector
	 * @return E
	 * @throws NoSuchElementException If the sequence is empty
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function min(?Closure $selector = null): mixed;

	/**
	 * Returns the element with the minimum value, or null if the sequence is empty.
	 * Drains the sequence.
	 *
	 * @param Closure(E, int):mixed|null $selector
	 * @return E|null
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function minOrNull(?Closure $selector = null): mixed;

	/**
	 * Returns the element with the maximum value, draining the sequence.
	 * When a selector is given, returns the element whose selector value is maximum.
	 *
	 * @param Closure(E, int):mixed|null $selector
	 * @return E
	 * @throws NoSuchElementException If the sequence is empty
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function max(?Closure $selector = null): mixed;

	/**
	 * Returns the element with the maximum value, or null if the sequence is empty.
	 * Drains the sequence.
	 *
	 * @param Closure(E, int):mixed|null $selector
	 * @return E|null
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function maxOrNull(?Closure $selector = null): mixed;

	/**
	 * Returns the minimum value produced by the selector, draining the sequence.
	 *
	 * @template R of mixed
	 * @param Closure(E, int):R $selector
	 * @return R
	 * @throws NoSuchElementException If the sequence is empty
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function minOf(Closure $selector): mixed;

	/**
	 * Returns the minimum value produced by the selector, or null if the sequence is empty.
	 * Drains the sequence.
	 *
	 * @template R of mixed
	 * @param Closure(E, int):R $selector
	 * @return R|null
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function minOfOrNull(Closure $selector): mixed;

	/**
	 * Returns the maximum value produced by the selector, draining the sequence.
	 *
	 * @template R of mixed
	 * @param Closure(E, int):R $selector
	 * @return R
	 * @throws NoSuchElementException If the sequence is empty
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function maxOf(Closure $selector): mixed;

	/**
	 * Returns the maximum value produced by the selector, or null if the sequence is empty.
	 * Drains the sequence.
	 *
	 * @template R of mixed
	 * @param Closure(E, int):R $selector
	 * @return R|null
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function maxOfOrNull(Closure $selector): mixed;

	/**
	 * Joins elements into a string with the given separator, prefix, postfix, and optional
	 * transform.
	 *
	 * The one aggregation that can stop early: a non-negative $limit stops pulling once that many
	 * elements have been joined, so joining the head of a long stream costs only that head.
	 * Without a limit it drains.
	 *
	 * When no transform is provided, elements are converted to strings using (string) cast.
	 * Scalars, null, and Stringable objects are supported. Non-stringable objects and arrays
	 * will throw a ConversionException.
	 *
	 * @param Closure(E, int):string|null $transform Optional transform to apply to each element
	 * @throws ConversionException When an element cannot be converted to string and no transform is provided
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	public function joinToString(string $separator = ', ', string $prefix = '', string $postfix = '', int $limit = -1, string $truncated = '...', ?Closure $transform = null): string;

	// --- Conversion ---

	/**
	 * Convert to an immutable list, consuming one pass of the sequence.
	 *
	 * @return ImmutableList<E>
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	#[NoDiscard]
	public function toList(): ImmutableList;

	/**
	 * Convert to an immutable set (duplicates removed), consuming one pass of the sequence.
	 *
	 * @return ImmutableSet<E>
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	#[NoDiscard]
	public function toSet(): ImmutableSet;

	/**
	 * Convert to a primitive PHP array, consuming one pass of the sequence.
	 *
	 * @return list<E>
	 * @throws NonReplayableSourceException If a non-replayable source has already been consumed
	 * @throws InvalidSequenceSourceException If a Closure source returns a non-iterable
	 */
	#[NoDiscard]
	public function toArray(): array;
}
