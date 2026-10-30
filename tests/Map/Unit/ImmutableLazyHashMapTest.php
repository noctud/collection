<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\Unit;

use Closure;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Tests\EnumerableLazyInit;
use Noctud\Collection\Tests\Map\Case\AbstractMapTestCase;
use function Noctud\Collection\mapOf;
use function Noctud\Collection\mapOfPairs;
use function is_callable;

final class ImmutableLazyHashMapTest extends AbstractMapTestCase
{
	use EnumerableLazyInit;

	/**
	 * @template K of string|int|bool|float|object
	 * @template V
	 * @param iterable<K,V>|Closure():iterable<K,V> $data
	 * @return ImmutableMap<K,V>
	 */
	public function mapOf(iterable|Closure $data): ImmutableMap
	{
		if (!is_callable($data)) {
			return mapOf(fn () => $data);
		}

		return mapOf($data);
	}

	/**
	 * @template K of string|int|bool|float|object
	 * @template V
	 * @param iterable<array{0:K,1:V}>|Closure():iterable<array{0:K,1:V}> $data
	 * @return ImmutableMap<K,V>
	 */
	public function mapOfPairs(iterable|Closure $data): ImmutableMap
	{
		if (!is_callable($data)) {
			return mapOfPairs(fn () => $data);
		}

		return mapOfPairs($data);
	}

	/**
	 * @template K of string|int|bool|float|object
	 * @template V
	 * @param iterable<K,V>|Closure():iterable<K,V> $data
	 * @return ImmutableMap<K,V>
	 */
	public function enumerableOf(iterable|Closure $data): ImmutableMap
	{
		if (!is_callable($data)) {
			return mapOf(fn () => $data);
		}

		return mapOf($data);
	}
}
