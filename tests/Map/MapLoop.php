<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\mutableSetOf;

trait MapLoop
{
	#[Test]
	public function forEach(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$pairs = [];
		$keys = [];
		$values = [];

		$map->forEach(function ($v, $k) use (&$pairs): void {
			$pairs[] = $k . ':' . $v;
		});

		$map->forEachKey(function ($k) use (&$keys): void {
			$keys[] = $k;
		});

		$map->forEachValue(function ($v) use (&$values): void {
			$values[] = $v;
		});

		$this->assertSame(['a:1', 'b:2', 'c:3'], $pairs);
		$this->assertSame(['a', 'b', 'c'], $keys);
		$this->assertSame([1, 2, 3], $values);
	}

	#[Test]
	public function onEach_visits_all_entries_and_hands_the_map_back(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$pairs = [];
		$keys = [];
		$values = [];

		$result = $map
			->onEach(function ($v, $k) use (&$pairs): void {
				$pairs[] = $k . ':' . $v;
			})
			->onEachKey(function ($k) use (&$keys): void {
				$keys[] = $k;
			})
			->onEachValue(function ($v) use (&$values): void {
				$values[] = $v;
			});

		$this->assertSame(['a:1', 'b:2', 'c:3'], $pairs);
		$this->assertSame(['a', 'b', 'c'], $keys);
		$this->assertSame([1, 2, 3], $values);
		$this->assertSame($map, $result);
	}

	#[Test]
	public function onEach_taps_a_chain_without_breaking_it(): void
	{
		$visited = [];
		$result = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3])
			->onEach(function ($v, $k) use (&$visited): void {
				$visited[] = $k;
			})
			->filter(fn ($v) => $v > 1);

		$this->assertSame(['a', 'b', 'c'], $visited);
		$this->assertSame(['b' => 2, 'c' => 3], $result->toArray());
	}

	#[Test]
	public function loop_on_snapshot(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$keys = mutableSetOf();
		$map->forEach(function ($v, $k) use (&$map, &$keys): void {
			$map = $map->remove('c');
			$keys->add($k);
		});

		$this->assertTrue(
			$keys->contains('c'),
			'The key "c" should have been iterated even if removed during iteration.',
		);
	}
}
