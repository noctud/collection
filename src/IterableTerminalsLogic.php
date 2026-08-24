<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

use Closure;
use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Exception\UnsupportedOperationException;
use Stringable;

/**
 * Terminal operations implemented by walking $this and nothing else, shared by the eager
 * Collection side and the lazy Sequence side.
 *
 * A body belongs here only if it is identical for both, which excludes two families: the one the
 * eager side answers from its store in O(1) (first/last/isEmpty/contains/count), and containsAll,
 * whose one-lookup-per-value shape would cost a sequence one pass per value. A message naming the
 * subject is no longer a reason to split: the exception derives that noun from $this.
 *
 * Consumers declare the contract, so the PHPDoc here is {@inheritDoc}: it resolves against
 * Collection<E> or Sequence<E> depending on who uses the trait.
 *
 * @template E
 *
 * @internal
 */
trait IterableTerminalsLogic
{
	// --- Element Access ---

	/** {@inheritDoc} */
	public function single()
	{
		$found = false;
		$result = null;

		foreach ($this as $v) {
			if ($found) {
				throw NoSuchElementException::subjectHasMoreThanOneElement($this);
			}

			$result = $v;
			$found = true;
		}

		if (!$found) {
			throw NoSuchElementException::emptySubject($this);
		}

		return $result; // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * Not a try-catch around single(): iterating $this runs user closures on the lazy side, and a
	 * NoSuchElementException raised inside one is a real error, not an answer to this question.
	 */
	public function singleOrNull(): mixed
	{
		$found = false;
		$result = null;

		foreach ($this as $v) {
			if ($found) {
				return null;
			}

			$result = $v;
			$found = true;
		}

		return $result;
	}

	/** {@inheritDoc} */
	public function find(Closure $predicate): mixed
	{
		foreach ($this as $i => $v) {
			if ($predicate($v, $i)) {
				return $v;
			}
		}

		return null;
	}

	/** {@inheritDoc} */
	public function expect(Closure $predicate)
	{
		foreach ($this as $i => $v) {
			if ($predicate($v, $i)) {
				return $v;
			}
		}

		throw new NoSuchElementException('No element matching the predicate was found');
	}

	// --- Querying ---

	/** {@inheritDoc} */
	public function isNotEmpty(): bool
	{
		return !$this->isEmpty();
	}

	/** {@inheritDoc} */
	public function all(Closure $predicate): bool
	{
		foreach ($this as $i => $v) {
			if (!$predicate($v, $i)) {
				return false;
			}
		}

		return true;
	}

	/** {@inheritDoc} */
	public function any(Closure $predicate): bool
	{
		foreach ($this as $i => $v) {
			if ($predicate($v, $i)) {
				return true;
			}
		}

		return false;
	}

	/** {@inheritDoc} */
	public function none(Closure $predicate): bool
	{
		return !$this->any($predicate);
	}

	/** {@inheritDoc} */
	public function countWhere(Closure $predicate): int
	{
		/** @var int<0, max> $count */
		$count = 0;
		foreach ($this as $i => $v) {
			if ($predicate($v, $i)) {
				$count++;
			}
		}

		return $count;
	}

	// --- Aggregation ---

	/** {@inheritDoc} */
	public function fold(mixed $initial, Closure $operation): mixed
	{
		$acc = $initial;
		foreach ($this as $v) {
			$acc = $operation($acc, $v);
		}
		return $acc;
	}

	/** {@inheritDoc} */
	public function reduce(Closure $operation): mixed
	{
		$first = true;
		$acc = null;

		foreach ($this as $v) {
			if ($first) {
				$acc = $v;
				$first = false;
			} else {
				$acc = $operation($acc, $v);
			}
		}

		if ($first) {
			throw UnsupportedOperationException::cannotReduceEmptySubject($this);
		}

		return $acc;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Not a try-catch around reduce(): the operation is user code, and an
	 * UnsupportedOperationException raised inside it is a real error, not an answer to this
	 * question. An empty subject needs no flag here - the accumulator simply stays null.
	 */
	public function reduceOrNull(Closure $operation): mixed
	{
		$first = true;
		$acc = null;

		foreach ($this as $v) {
			if ($first) {
				$acc = $v;
				$first = false;
			} else {
				$acc = $operation($acc, $v);
			}
		}

		return $acc;
	}

	/** {@inheritDoc} */
	// @phpstan-ignore conditionalType.subjectNotFound, conditionalType.alwaysFalse (E is concrete in the extending fixtures and a MapEntry in MapEntrySet: either way the conditional is already decided)
	public function sum(?Closure $selector = null): int|float
	{
		$sum = 0;
		foreach ($this as $i => $v) {
			$sum += $selector !== null ? $selector($v, $i) : $v; // @phpstan-ignore assignOp.invalid
		}

		return $sum;
	}

	/** {@inheritDoc} */
	public function avg(?Closure $selector = null): float
	{
		return $this->avgOrNull($selector) ?? throw UnsupportedOperationException::cannotAverageEmptySubject($this);
	}

	/** {@inheritDoc} */
	public function avgOrNull(?Closure $selector = null): float|null
	{
		$sum = 0;
		$count = 0;
		foreach ($this as $i => $v) {
			$sum += $selector !== null ? $selector($v, $i) : $v; // @phpstan-ignore assignOp.invalid
			$count++;
		}

		return $count > 0 ? $sum / $count : null;
	}

	/** {@inheritDoc} */
	public function min(?Closure $selector = null): mixed
	{
		$minValue = null;
		$minElement = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector !== null ? $selector($v, $i) : $v;
			if (!$found || $value < $minValue) {
				$minValue = $value;
				$minElement = $v;
				$found = true;
			}
		}

		if (!$found) {
			throw NoSuchElementException::emptySubject($this);
		}

		return $minElement;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Not a try-catch around min(): the selector is user code, and a NoSuchElementException
	 * raised inside it is a real error, not an answer to this question.
	 */
	public function minOrNull(?Closure $selector = null): mixed
	{
		$minValue = null;
		$minElement = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector !== null ? $selector($v, $i) : $v;
			if (!$found || $value < $minValue) {
				$minValue = $value;
				$minElement = $v;
				$found = true;
			}
		}

		return $minElement;
	}

	/** {@inheritDoc} */
	public function max(?Closure $selector = null): mixed
	{
		$maxValue = null;
		$maxElement = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector !== null ? $selector($v, $i) : $v;
			if (!$found || $value > $maxValue) {
				$maxValue = $value;
				$maxElement = $v;
				$found = true;
			}
		}

		if (!$found) {
			throw NoSuchElementException::emptySubject($this);
		}

		return $maxElement;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Not a try-catch around max(): the selector is user code, and a NoSuchElementException
	 * raised inside it is a real error, not an answer to this question.
	 */
	public function maxOrNull(?Closure $selector = null): mixed
	{
		$maxValue = null;
		$maxElement = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector !== null ? $selector($v, $i) : $v;
			if (!$found || $value > $maxValue) {
				$maxValue = $value;
				$maxElement = $v;
				$found = true;
			}
		}

		return $maxElement;
	}

	/** {@inheritDoc} */
	public function minOf(Closure $selector): mixed
	{
		$minValue = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector($v, $i);
			if (!$found || $value < $minValue) {
				$minValue = $value;
				$found = true;
			}
		}

		if (!$found) {
			throw NoSuchElementException::emptySubject($this);
		}

		return $minValue;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Not a try-catch around minOf(): the selector is user code, and a NoSuchElementException
	 * raised inside it is a real error, not an answer to this question.
	 */
	public function minOfOrNull(Closure $selector): mixed
	{
		$best = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector($v, $i);
			if (!$found || $value < $best) {
				$best = $value;
				$found = true;
			}
		}

		return $best;
	}

	/** {@inheritDoc} */
	public function maxOf(Closure $selector): mixed
	{
		$maxValue = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector($v, $i);
			if (!$found || $value > $maxValue) {
				$maxValue = $value;
				$found = true;
			}
		}

		if (!$found) {
			throw NoSuchElementException::emptySubject($this);
		}

		return $maxValue;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Not a try-catch around maxOf(): the selector is user code, and a NoSuchElementException
	 * raised inside it is a real error, not an answer to this question.
	 */
	public function maxOfOrNull(Closure $selector): mixed
	{
		$best = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector($v, $i);
			if (!$found || $value > $best) {
				$best = $value;
				$found = true;
			}
		}

		return $best;
	}

	/** {@inheritDoc} */
	public function joinToString(string $separator = ', ', string $prefix = '', string $postfix = '', int $limit = -1, string $truncated = '...', ?Closure $transform = null): string
	{
		$parts = [];
		$i = 0;
		foreach ($this as $v) {
			if ($limit >= 0 && $i >= $limit) {
				$parts[] = $truncated;
				break;
			}

			if ($transform !== null) {
				$parts[] = $transform($v, $i);
			} elseif (is_scalar($v) || $v === null || $v instanceof Stringable) {
				$parts[] = (string) $v;
			} else {
				throw new ConversionException(sprintf(
					'Value of type "%s" at index %d cannot be converted to string. Provide a $transform closure to resolve.',
					get_debug_type($v),
					$i
				));
			}

			$i++;
		}

		return $prefix . implode($separator, $parts) . $postfix;
	}
}
