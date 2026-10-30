<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

use Generator;
use function is_array;
use function count;
use function min;
use function array_slice;

/**
 * @internal
 * @template V
 * @extends AbstractOperation<int,V>
 */
final class WindowOperation extends AbstractOperation
{
	/**
	 * Slides a window over the source in a single forward pass, holding at most $size
	 * elements: every yielded window drops its first $step elements, and the following
	 * ones are appended as they arrive. When $step is larger than $size, the buffer
	 * empties and the surplus elements are skipped without ever being held.
	 *
	 * An array source takes the random-access path instead (see sliced()).
	 *
	 * @return Generator<int, list<V>>
	 */
	public function ofSize(int $size, int $step = 1, bool $partialWindows = false): Generator
	{
		if ($size <= 0 || $step <= 0) {
			return;
		}

		if (is_array($this->data)) {
			yield from $this->sliced($this->data, $size, $step, $partialWindows);
			return;
		}

		$buffer = [];
		$skip = 0;

		foreach ($this->data as $v) {
			if ($skip > 0) {
				$skip--;
				continue;
			}

			$buffer[] = $v;

			if (count($buffer) === $size) {
				yield $buffer;

				$dropped = min($step, $size);
				$buffer = array_slice($buffer, $dropped);
				$skip = $step - $dropped;
			}
		}

		if (!$partialWindows) {
			return;
		}

		// The source ran out before the window filled up: what is left still starts a
		// shorter window at each remaining step, which is what the index-based
		// definition of windowed() produces at the tail.
		while ($buffer !== []) {
			yield $buffer;

			$buffer = array_slice($buffer, min($step, count($buffer)));
		}
	}

	/**
	 * Random-access path for eager collections: jumps straight to each window
	 * start, so skipped elements and an oversized window cost nothing.
	 *
	 * @param array<int, V> $arr
	 * @return Generator<int, list<V>>
	 */
	private function sliced(array $arr, int $size, int $step, bool $partialWindows): Generator
	{
		$count = count($arr);
		for ($i = 0; $i < $count; $i += $step) {
			$windowSize = min($size, $count - $i);
			if (!$partialWindows && $windowSize < $size) {
				break;
			}

			yield array_slice($arr, $i, $windowSize);
		}
	}
}
