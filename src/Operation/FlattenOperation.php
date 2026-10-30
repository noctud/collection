<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

use Generator;
use function is_iterable;

/**
 * @internal
 * @template V
 * @extends AbstractOperation<int,V>
 */
final class FlattenOperation extends AbstractOperation
{
	/**
	 * @return Generator<int,V>
	 */
	public function items(): Generator
	{
		foreach ($this->data as $v) {
			if (is_iterable($v)) {
				foreach ($v as $x) {
					yield $x;
				}
			} else {
				yield $v;
			}
		}
	}
}
