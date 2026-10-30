<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List\Unit;

use Closure;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Tests\Collection\List\Case\AbstractListTestCase;
use Noctud\Collection\Tests\EnumerableLazyInit;
use function Noctud\Collection\listOf;
use function is_callable;

final class ImmutableLazyListTest extends AbstractListTestCase
{
	use EnumerableLazyInit;

	/**
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return ImmutableList<E>
	 */
	public function collectionOf(iterable|Closure $data): ImmutableList
	{
		if (!is_callable($data)) {
			return listOf(fn () => $data);
		}

		return listOf($data);
	}

	/**
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return ImmutableList<E>
	 */
	public function enumerableOf(iterable|Closure $data): ImmutableList
	{
		return $this->collectionOf($data);
	}
}
