<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\StringMap\Unit;

use Closure;
use Noctud\Collection\Map\HashMap\ImmutableHashMap;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Tests\Map\MapChangedWithNull;
use Noctud\Collection\Tests\Map\StringMap\Case\AbstractStringMapTestCase;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\mutableStringMapOf;
use function strlen;

final class MutableStringMapTest extends AbstractStringMapTestCase
{
	use MapChangedWithNull;

	protected function sampleKey(): string
	{
		return 'test_key';
	}

	/**
	 * @template V
	 * @param iterable<string,V>|Closure():iterable<string,V> $data
	 * @return MutableMap<string,V>
	 */
	public function mapOf(iterable|Closure $data): MutableMap
	{
		return mutableStringMapOf($data);
	}

	/**
	 * @template V
	 * @param iterable<string,V>|Closure():iterable<string,V> $data
	 * @return MutableMap<string,V>
	 */
	public function enumerableOf(iterable|Closure $data): MutableMap
	{
		return $this->mapOf($data);
	}

	#[Test]
	public function map_keys_returns_hashmap(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);

		// mapKeys signature is (V, K) -> NK
		$mapped = $map->mapKeys(fn (int $v, string $k) => strlen($k));

		// mapKeys should return ImmutableHashMap since keys may change type
		$this->assertInstanceOf(ImmutableHashMap::class, $mapped);
	}

	#[Test]
	public function flip_returns_hashmap(): void
	{
		/** @var MutableMap<string, string> $map */
		$map = $this->mapOf(['a' => 'x', 'b' => 'y']);

		$flipped = $map->flip();

		// flip should return ImmutableHashMap since values become keys (any type)
		$this->assertInstanceOf(ImmutableHashMap::class, $flipped);
	}

	#[Test]
	public function getOrPut_returns_existing_value_without_calling_closure(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);
		$called = false;

		$result = $map->getOrPut('a', function () use (&$called) {
			$called = true;
			return 99;
		});

		$this->assertSame(1, $result);
		$this->assertFalse($called);
	}

	#[Test]
	public function getOrPut_computes_and_stores_when_key_missing(): void
	{
		$map = $this->mapOf(['a' => 1]);

		$result = $map->getOrPut('b', fn () => 42);

		$this->assertSame(42, $result);
		$this->assertTrue($map->containsKey('b'));
		$this->assertSame(42, $map->get('b'));
	}
}
