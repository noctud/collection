<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Sequence;

use Closure;
use IteratorAggregate;
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
 *   around, for instance) throws SequenceAlreadyIteratedException;
 * - a raw Iterator/Generator: the sequence is single-pass and any further iteration
 *   throws SequenceAlreadyIteratedException (a partial pass counts as consumed).
 *
 * Keys are positional: every pass yields fresh 0..n keys, whatever the source yields.
 *
 * Deliberately neither Countable (counting would silently consume a pass; native
 * count($seq) is a TypeError by design) nor JsonSerializable
 * (json_encode would be a hidden materialization) - materialize explicitly with
 * toList() or toArray() instead.
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
	 * Map elements to a new sequence.
	 * The transform receives the element and optionally the index.
	 *
	 * @template R
	 * @param Closure(E, int):R $transform
	 * @return Sequence<R>
	 */
	#[NoDiscard]
	public function map(Closure $transform): Sequence;

	// --- Conversion ---

	/**
	 * Convert to an immutable list, consuming one pass of the sequence.
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function toList(): ImmutableList;

	/**
	 * Convert to an immutable set (duplicates removed), consuming one pass of the sequence.
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function toSet(): ImmutableSet;

	/**
	 * Convert to a primitive PHP array, consuming one pass of the sequence.
	 *
	 * @return list<E>
	 */
	#[NoDiscard]
	public function toArray(): array;
}
