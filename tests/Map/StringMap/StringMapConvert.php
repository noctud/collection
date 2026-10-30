<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\StringMap;

use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\MutableMap;
use PHPUnit\Framework\Attributes\Test;
use function json_encode;
use function spl_object_id;

trait StringMapConvert
{
	#[Test]
	public function toArray(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $map->toArray());
	}

	#[Test]
	public function toArray_empty(): void
	{
		$map = $this->mapOf([]);

		$this->assertSame([], $map->toArray());
	}

	#[Test]
	public function toArray_with_numeric_string_keys(): void
	{
		// PHP arrays cast numeric string keys to int, so use generator to preserve string keys
		$generator = (function () {
			yield '123' => 'a';
			yield '456' => 'b';
		})();

		$map = $this->mapOf($generator);

		$array = $map->toArray();
		// PHP will still cast "123" to 123 in the resulting array
		$this->assertSame([123 => 'a', 456 => 'b'], $array);
	}

	#[Test]
	public function jsonSerialize(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$this->assertSame(
			'{"a":1,"b":2,"c":3}',
			json_encode($map),
		);
	}

	#[Test]
	public function toPairs(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$this->assertSame([['a', 1], ['b', 2], ['c', 3]], $map->toPairs());
	}

	#[Test]
	public function toPairs_empty(): void
	{
		$map = $this->mapOf([]);

		$this->assertSame([], $map->toPairs());
	}

	#[Test]
	public function count_returns_total_entries(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);

		$this->assertSame(4, $map->count());
	}

	#[Test]
	public function count_on_empty_map(): void
	{
		$map = $this->mapOf([]);

		$this->assertSame(0, $map->count());
	}

	#[Test]
	public function debug_info(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);

		$debugInfo = $map->__debugInfo(); /** @phpstan-ignore-line */
		$this->assertEquals([['a', 1], ['b', 2]], $debugInfo);
	}

	#[Test]
	public function toMutable_and_toImmutable(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$immutableMap = $map->toImmutable();
		$this->assertFalse($immutableMap instanceof MutableMap);
		$this->assertSame($map->toArray(), $immutableMap->toArray());

		// since the object is guaranteed to never change, why not return itself?
		if ($map instanceof ImmutableMap) {
			$this->assertTrue(spl_object_id($immutableMap) === spl_object_id($map));
		} else {
			$this->assertFalse(spl_object_id($immutableMap) === spl_object_id($map));
		}

		$mutableMap = $map->toMutable();
		$this->assertFalse($mutableMap instanceof ImmutableMap);
		$this->assertSame($immutableMap->toArray(), $mutableMap->toArray());
		$this->assertFalse(spl_object_id($mutableMap) === spl_object_id($map));
		$mutableMap->put('d', 4);
		$this->assertFalse($mutableMap->toArray() === $map->toArray());
	}
}
