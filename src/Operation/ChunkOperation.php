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
final class ChunkOperation extends AbstractOperation
{
	/**
	 * Yields chunks as they fill up, so a lazy caller never holds more than one
	 * chunk at a time; an eager one collects them exactly as before. An array source
	 * is cut by array_chunk() instead.
	 *
	 * @return Generator<int, list<V>>
	 */
	public function ofSize(int $size): Generator
	{
		if ($size <= 0) {
			return;
		}

		if (is_array($this->data)) {
			yield from array_chunk($this->data, $size);
			return;
		}

		$buffer = [];
		foreach ($this->data as $v) {
			$buffer[] = $v;
			if (count($buffer) >= $size) {
				yield $buffer;
				$buffer = [];
			}
		}
		if ($buffer) {
			yield $buffer;
		}
	}
}
