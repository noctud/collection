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
final class PartitionOperation extends AbstractOperation
{
	/**
	 * @param callable(V, int):bool $predicate
	 * @return array{list<V>, list<V>}
	 */
	public function byPredicate(callable $predicate): array
	{
		$matching = [];
		$nonMatching = [];

		foreach ($this->data as $i => $v) {
			if ($predicate($v, $i)) {
				$matching[] = $v;
			} else {
				$nonMatching[] = $v;
			}
		}

		return [$matching, $nonMatching];
	}
}
