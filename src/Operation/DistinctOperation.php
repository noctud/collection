<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

use Generator;
use Noctud\Collection\KeyHasher;

/**
 * @internal
 * @template V
 * @extends AbstractOperation<int,V>
 */
final class DistinctOperation extends AbstractOperation
{
	/**
	 * @return Generator<V>
	 */
	public function items(): Generator
	{
		$set = [];
		foreach ($this->data as $v) {
			$nk = KeyHasher::hashSetKey($v);
			if (!array_key_exists($nk, $set)) {
				// Holding the value keeps objects alive, so their spl_object_id() can't be reused by a later object
				$set[$nk] = $v;
				yield $v;
			}
		}
	}

	/**
	 * @template SK
	 * @param callable(V, int):SK $selector
	 * @return Generator<V>
	 */
	public function bySelector(callable $selector): Generator
	{
		$set = [];
		foreach ($this->data as $i => $v) {
			$key = $selector($v, $i);
			$nk = KeyHasher::hashSetKey($key);
			if (!array_key_exists($nk, $set)) {
				// Holding the key keeps objects alive, so their spl_object_id() can't be reused by a later object
				$set[$nk] = $key;
				yield $v;
			}
		}
	}
}
