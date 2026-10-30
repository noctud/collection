<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\IntMap;

use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\MutableMap;
use PHPUnit\Framework\Attributes\Test;
use function json_encode;
use function spl_object_id;

trait IntMapConvert
{
	#[Test]
	public function toArray(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$this->assertSame([1 => 'a', 2 => 'b', 3 => 'c'], $map->toArray());
	}

	#[Test]
	public function toArray_empty(): void
	{
		$map = $this->mapOf([]);

		$this->assertSame([], $map->toArray());
	}

	#[Test]
	public function toArray_with_negative_keys(): void
	{
		$map = $this->mapOf([-1 => 'a', -100 => 'b']);

		$array = $map->toArray();
		$this->assertSame([-1 => 'a', -100 => 'b'], $array);
	}

	#[Test]
	public function jsonSerialize(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$this->assertSame(
			'{"1":"a","2":"b","3":"c"}',
			json_encode($map),
		);
	}

	#[Test]
	public function toPairs(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$this->assertSame([[1, 'a'], [2, 'b'], [3, 'c']], $map->toPairs());
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
		$map = $this->mapOf([1 => 10, 2 => 20, 3 => 30, 4 => 40]);

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
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		$debugInfo = $map->__debugInfo(); /** @phpstan-ignore-line */
		$this->assertEquals([[1, 'a'], [2, 'b']], $debugInfo);
	}

	#[Test]
	public function toMutable_and_toImmutable(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

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
		$mutableMap->put(4, 'd');
		$this->assertFalse($mutableMap->toArray() === $map->toArray());
	}
}
