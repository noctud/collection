<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\Unit;

use Closure;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Tests\Map\Case\AbstractMapTestCase;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\mapOf;
use function Noctud\Collection\mapOfPairs;

final class ImmutableHashMapTest extends AbstractMapTestCase
{
	/**
	 * @template K of string|int|bool|float|object
	 * @template V
	 * @param iterable<K,V>|Closure():iterable<K,V> $data
	 * @return ImmutableMap<K,V>
	 */
	public function mapOf(iterable|Closure $data): ImmutableMap
	{
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
		return $this->mapOf($data);
	}

	#[Test]
	#[IgnoreDeprecations]
	public function mapOfPairs_without_arguments_is_deprecated(): void
	{
		$this->expectUserDeprecationMessage('Calling mapOfPairs() without arguments is deprecated, 0.2 makes the $data argument required. Use mapOf() to create an empty map.');

		$this->assertTrue(mapOfPairs()->isEmpty()); // @phpstan-ignore argument.templateType, argument.templateType (an empty call has nothing to infer K and V from)
	}

	#[Test]
	public function mapOfPairs_with_empty_data_is_not_deprecated(): void
	{
		$deprecations = [];
		set_error_handler(function (int $errno, string $message) use (&$deprecations): bool {
			$deprecations[] = $message;
			return true;
		}, E_USER_DEPRECATED);

		try {
			$map = mapOfPairs([]); // @phpstan-ignore argument.templateType, argument.templateType (an empty array has nothing to infer K and V from)
		} finally {
			restore_error_handler();
		}

		$this->assertSame([], $deprecations);
		$this->assertTrue($map->isEmpty());
	}
}
