<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set\Unit;

use Closure;
use Noctud\Collection\Set\Set;
use Noctud\Collection\Tests\Collection\Set\Case\AbstractSetTestCase;
use function Noctud\Collection\mutableMapOf;

final class MutableMapEntrySetTest extends AbstractSetTestCase
{
	/**
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return Set<E>
	 */
	public function collectionOf(iterable|Closure $data): Set
	{
		return mutableMapOf($data)->entries->map(fn ($entry) => $entry->value);
	}

	/**
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return Set<E>
	 */
	public function enumerableOf(iterable|Closure $data): Set
	{
		return $this->collectionOf($data);
	}
}
