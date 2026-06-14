<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

/**
 * @internal
 * @template V
 * @extends AbstractOperation<int,V>
 */
final class ChunkOperation extends AbstractOperation
{
	/**
	 * @return list<list<V>>
	 */
	public function ofSize(int $size): array
	{
		if ($size <= 0) {
			return [];
		}
		$chunks = [];
		$buffer = [];
		foreach ($this->data as $v) {
			$buffer[] = $v;
			if (count($buffer) >= $size) {
				$chunks[] = $buffer;
				$buffer = [];
			}
		}
		if ($buffer) {
			$chunks[] = $buffer;
		}
		return $chunks;
	}
}
