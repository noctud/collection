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
final class DropOperation extends AbstractOperation
{
	/**
	 * @param int $n
	 * @return Generator<V>
	 */
	public function first(int $n): Generator
	{
		$i = 0;
		foreach ($this->data as $v) {
			if ($i++ < $n) {
				continue;
			}
			yield $v;
		}
	}

	/**
	 * @param int $n
	 * @return list<V>
	 */
	public function last(int $n): array
	{
		if ($n <= 0) {
			return is_array($this->data) ? array_values($this->data) : iterator_to_array($this->data, false);
		}

		$arr = is_array($this->data) ? array_values($this->data) : iterator_to_array($this->data, false);
		$length = count($arr) - $n;

		return $length > 0 ? array_slice($arr, 0, $length) : [];
	}

	/**
	 * @param callable(V):bool $predicate
	 * @return Generator<V>
	 */
	public function byPredicate(callable $predicate): Generator
	{
		$dropping = true;
		foreach ($this->data as $v) {
			if ($dropping && $predicate($v)) {
				continue;
			}
			$dropping = false;
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

		return array_slice($arr, 0, $i + 1);
	}
}
