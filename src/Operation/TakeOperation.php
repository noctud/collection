<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

use Generator;

/**
 * @internal
 * @template V
 * @extends AbstractOperation<int,V>
 */
final class TakeOperation extends AbstractOperation
{
	/**
	 * An array is sliced, and a collection takes the slice over as is. Any other source is
	 * streamed, so that a lazy caller stops pulling after n elements.
	 *
	 * @return iterable<V>
	 */
	public function first(int $n): iterable
	{
		if ($n <= 0) {
			return [];
		}

		if (is_array($this->data)) {
			return array_slice($this->data, 0, $n);
		}

		return $this->streamFirst($n);
	}

	/**
	 * @return Generator<V>
	 */
	private function streamFirst(int $n): Generator
	{
		$i = 0;
		foreach ($this->data as $v) {
			yield $v;

			if (++$i >= $n) {
				break;
			}
		}
	}

	/**
	 * @param int $n
	 * @return list<V>
	 */
	public function last(int $n): array
	{
		if ($n <= 0) {
			return [];
		}

		$arr = is_array($this->data) ? array_values($this->data) : iterator_to_array($this->data, false);

		return array_slice($arr, -$n);
	}

	/**
	 * @param callable(V, int):bool $predicate
	 * @return Generator<V>
	 */
	public function byPredicate(callable $predicate): Generator
	{
		foreach ($this->data as $i => $v) {
			if (!$predicate($v, $i)) {
				break;
			}
			yield $v;
		}
	}

	/**
	 * @param callable(V, int):bool $predicate
	 * @return list<V>
	 */
	public function lastByPredicate(callable $predicate): array
	{
		$arr = is_array($this->data) ? array_values($this->data) : iterator_to_array($this->data, false);
		$i = count($arr) - 1;
		while ($i >= 0 && $predicate($arr[$i], $i)) {
			$i--;
		}

		return array_slice($arr, $i + 1);
	}
}
