<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Unit;

use Closure;
use Noctud\Collection\Collection;
use Noctud\Collection\Tests\Collection\Case\AbstractCollectionTestCase;
use function Noctud\Collection\mutableMapOf;

final class MutableMapValueCollectionTest extends AbstractCollectionTestCase
{
	/**
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return Collection<E>
	 */
	public function collectionOf(iterable|Closure $data): Collection
	{
		return mutableMapOf($data)->values;
	}

	/**
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return Collection<E>
	 */
	public function enumerableOf(iterable|Closure $data): Collection
	{
		return $this->collectionOf($data);
	}
}
