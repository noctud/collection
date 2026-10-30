<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List;

use Noctud\Collection\Map\ImmutableMap;
use PHPUnit\Framework\Attributes\Test;
use function strlen;

trait ListConvert
{
	#[Test]
	public function toIndexedMap(): void
	{
		$list = $this->collectionOf(['a', 'b', 'c']);
		$map = $list->toIndexedMap();

		$this->assertSame('a', $map->get(0));
		$this->assertSame('b', $map->get(1));
		$this->assertSame('c', $map->get(2));
		$this->assertSame(3, $map->count());

		// Conversion methods always return immutable types
		$this->assertInstanceOf(ImmutableMap::class, $map);
	}

	#[Test]
	public function toIndexedMap_with_value_mapper(): void
	{
		$list = $this->collectionOf(['apple', 'banana']);
		$map = $list->toIndexedMap(fn ($v) => strlen($v));

		$this->assertSame(5, $map->get(0));
		$this->assertSame(6, $map->get(1));
	}

	#[Test]
	public function toIndexedMap_on_empty(): void
	{
		$list = $this->collectionOf([]);
		$this->assertSame(0, $list->toIndexedMap()->count());
	}
}
