<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Sequence;

use Closure;
use Generator;
use IteratorAggregate;
use Noctud\Collection\Exception\InvalidSequenceSourceException;
use Noctud\Collection\Exception\SequenceAlreadyIteratedException;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Operation\FilterOperation;
use Noctud\Collection\Operation\MapKeyValueOperation;
use Noctud\Collection\Set\ImmutableSet;
use NoDiscard;
use Traversable;
use function Noctud\Collection\listOf;
use function Noctud\Collection\setOf;

/**
 * @template E
 * @mixin Sequence<E>
 */
trait SequenceLogic
{
	/** @var iterable<E>|Closure():iterable<E> */
	private iterable|Closure $source;

	/** Whether a single-pass source has already been handed out. */
	private bool $consumed = false;

	/**
	 * Iterator the source produced on the previous pass. The reference is kept (instead of
	 * spl_object_id, whose values can be reused after garbage collection) so the identity
	 * guard in resolveProducedIterable() is collision-free.
	 */
	private ?Traversable $lastProduced = null;

	/**
	 * @return Generator<int, E>
	 */
	public function getIterator(): Generator
	{
		$iterable = $this->resolveSourceForThisPass();

		return (static function () use ($iterable): Generator {
			$i = 0;
			foreach ($iterable as $value) {
				yield $i++ => $value;
			}
		})();
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function filter(Closure $predicate): Sequence
	{
		return $this->newSequenceOf(fn (): iterable => new FilterOperation($this)->byPredicate($predicate));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function map(Closure $transform): Sequence
	{
		return $this->newSequenceOf(fn (): iterable => new MapKeyValueOperation($this)->items($transform));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toList(): ImmutableList
	{
		return listOf($this);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toSet(): ImmutableSet
	{
		return setOf($this);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toArray(): array
	{
		return iterator_to_array($this, false);
	}

	/**
	 * Resolves the source for one pass, enforcing the replayability contract: an array
	 * replays freely, a Closure and an IteratorAggregate are both producers asked for an
	 * iterable once per pass (and must hand back a fresh one each time), and any other
	 * Traversable is single-pass - even when technically rewindable, matching Kotlin's
	 * Iterator.asSequence(). The guard runs here, at getIterator() call time, so a
	 * violation throws at the start of the offending pass instead of silently yielding
	 * nothing.
	 *
	 * @return iterable<E>
	 */
	private function resolveSourceForThisPass(): iterable
	{
		$source = $this->source;

		if ($source instanceof Closure) {
			$produced = $source();

			// @phpstan-ignore function.alreadyNarrowedType (the closure's return type is a PHPDoc promise PHP cannot enforce)
			if (!is_iterable($produced)) {
				throw InvalidSequenceSourceException::closureReturnedNonIterable($produced);
			}

			return $this->resolveProducedIterable($produced);
		}

		if (is_array($source)) {
			return $source;
		}

		if ($source instanceof IteratorAggregate) {
			return $this->resolveProducedIterable($source->getIterator());
		}

		if ($this->consumed) {
			throw SequenceAlreadyIteratedException::nonReplayableSourceAlreadyIterated();
		}

		$this->consumed = true;

		return $source;
	}

	/**
	 * Rejects a producer - a source Closure or an IteratorAggregate - handing back the
	 * cursor of the previous pass: implementing IteratorAggregate promises nothing about
	 * replayability, getIterator() is free to return a Generator the object keeps around,
	 * and traversing that one again would yield nothing (or leak a raw PHP error).
	 *
	 * @param iterable<E> $produced
	 * @return iterable<E>
	 */
	private function resolveProducedIterable(iterable $produced): iterable
	{
		if ($produced instanceof Traversable && !$produced instanceof IteratorAggregate) {
			if ($produced === $this->lastProduced) {
				throw SequenceAlreadyIteratedException::sourceReturnedSameIterator();
			}

			$this->lastProduced = $produced;
		}

		return $produced;
	}

	/**
	 * Chains a lazy stage. The factory is invoked once per pass (never at chain-build
	 * time), so every pass runs a fresh Operation generator and replayability follows
	 * the root source.
	 *
	 * @template R
	 * @param Closure():iterable<R> $factory
	 * @return Sequence<R>
	 */
	private function newSequenceOf(Closure $factory): Sequence
	{
		return new GeneratorSequence($factory);
	}
}
