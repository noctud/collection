<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Sequence\Fixture;

use IteratorAggregate;
use Traversable;

/**
 * IteratorAggregate whose getIterator() is a generator function: every call hands out a
 * fresh Generator, so the aggregate replays (the shape of the library's own map views).
 *
 * @template TValue
 * @implements IteratorAggregate<int, TValue>
 */
final class GeneratorAggregate implements IteratorAggregate
{
	/** @param list<TValue> $values */
	public function __construct(
		private readonly array $values,
	) {}

	/** @return Traversable<int, TValue> */
	public function getIterator(): Traversable
	{
		yield from $this->values;
	}
}
