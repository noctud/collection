<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

use ArrayIterator;
use Generator;
use Iterator;
use IteratorIterator;
use Noctud\Collection\Exception\NonReplayableSourceException;
use NoRewindIterator;
use Throwable;
use function is_array;

/**
 * @internal
 * @template V
 * @extends AbstractOperation<int,V>
 */
final class ZipOperation extends AbstractOperation
{
	/**
	 * @template U
	 * @param iterable<U> $other
	 * @return Generator<array{V, U}>
	 * @throws NonReplayableSourceException If the other side cannot be positioned at its start
	 */
	public function with(iterable $other): Generator
	{
		$left = self::cursor($this->data);

		// Positioning the other side would pull an element no pair could ever use.
		if (!$left->valid()) {
			return;
		}

		$right = self::cursor($other);

		// This side is known to hold an element on every entry: the check above covers the
		// first one, the break below every later one.
		while ($right->valid()) {
			yield [$left->current(), $right->current()];

			// Advancing the other side only once this one still has an element spares it a
			// pull that would be thrown away whenever this side is the shorter one.
			$left->next();

			if (!$left->valid()) {
				break;
			}

			$right->next();
		}
	}

	/**
	 * @param iterable<mixed> $iterable
	 */
	public static function isReplayable(iterable $iterable): bool
	{
		return !$iterable instanceof Generator && !$iterable instanceof NoRewindIterator;
	}

	/**
	 * Positioned cursor over any iterable, so both sides can be walked in lockstep without
	 * either being buffered.
	 *
	 * @template T
	 * @param iterable<T> $iterable
	 * @return Iterator<T>
	 */
	private static function cursor(iterable $iterable): Iterator
	{
		if (is_array($iterable)) {
			return new ArrayIterator($iterable);
		}

		$cursor = $iterable instanceof Iterator ? $iterable : new IteratorIterator($iterable);

		// Asked rather than decided again, so that what counts as replayable and what actually
		// gets repositioned cannot drift apart.
		if (!self::isReplayable($iterable)) {
			return $cursor;
		}

		try {
			$cursor->rewind();
		} catch (Throwable $e) {
			throw NonReplayableSourceException::zippedIterableCannotRewind($e);
		}

		return $cursor;
	}
}
