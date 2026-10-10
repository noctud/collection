<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use PHPUnit\Framework\Attributes\Test;

trait CollectionForEach
{
	#[Test]
	public function forEach_visits_all_elements(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$visited = [];
		$collection->forEach(function ($v) use (&$visited) {
			$visited[] = $v;
		});
		$this->assertSame([1, 2, 3], $visited);
	}

	#[Test]
	public function forEach_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$visited = [];
		$collection->forEach(function ($v) use (&$visited) {
			$visited[] = $v;
		});
		$this->assertSame([], $visited);
	}

	#[Test]
	public function onEach_visits_all_elements_and_hands_the_collection_back(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$visited = [];
		$result = $collection->onEach(function ($v) use (&$visited) {
			$visited[] = $v;
		});

		$this->assertSame([1, 2, 3], $visited);
		$this->assertSame($collection, $result);
	}

	#[Test]
	public function onEach_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$visited = [];
		$result = $collection->onEach(function ($v) use (&$visited) {
			$visited[] = $v;
		});

		$this->assertSame([], $visited);
		$this->assertSame($collection, $result);
	}

	#[Test]
	public function onEach_taps_a_chain_without_breaking_it(): void
	{
		$visited = [];
		$result = $this->collectionOf([1, 2, 3])
			->onEach(function ($v) use (&$visited) {
				$visited[] = $v;
			})
			->filter(fn ($v) => $v > 1);

		$this->assertSame([1, 2, 3], $visited);
		$this->assertSame([2, 3], $result->toArray());
	}
}
