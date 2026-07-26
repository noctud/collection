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
 * IteratorAggregate handing back the very same traversable on every call: implementing
 * IteratorAggregate is no promise of replayability.
 *
 * @template TKey
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
final class SharedIteratorAggregate implements IteratorAggregate
{
	/** @param Traversable<TKey, TValue> $iterator */
	public function __construct(
		private readonly Traversable $iterator,
	) {}

	/** @return Traversable<TKey, TValue> */
	public function getIterator(): Traversable
	{
		return $this->iterator;
	}
}
