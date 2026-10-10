<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use PHPUnit\Framework\Attributes\Test;
use stdClass;

trait CollectionContains
{
	#[Test]
	public function contains(): void
	{
		$object = new stdClass();
		$collection = $this->collectionOf([1, 'a', null, $object, false]);

		$this->assertTrue($collection->contains(1));
		$this->assertTrue($collection->contains('a'));
		$this->assertTrue($collection->contains(null));
		$this->assertTrue($collection->contains($object));
		$this->assertTrue($collection->contains(false));

		$this->assertFalse($collection->contains(2));
		$this->assertFalse($collection->contains('b'));
		$this->assertFalse($collection->contains(true));
		$this->assertFalse($collection->contains(new stdClass()));
	}

	#[Test]
	public function contains_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertFalse($collection->contains(1));
		$this->assertFalse($collection->contains(null));
	}

	#[Test]
	public function containsAll(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);

		$this->assertTrue($collection->containsAll([1, 2, 3]));
		$this->assertTrue($collection->containsAll([5]));
		$this->assertTrue($collection->containsAll([]));

		$this->assertFalse($collection->containsAll([1, 6]));
		$this->assertFalse($collection->containsAll([0]));
	}

	#[Test]
	public function containsAll_on_empty(): void
	{
		$collection = $this->collectionOf([]);

		$this->assertTrue($collection->containsAll([]));
		$this->assertFalse($collection->containsAll([1]));
	}

	#[Test]
	public function containsAll_with_many_elements_compares_strictly(): void
	{
		// Enough elements on both sides for the lookup to be indexed by hash.
		$collection = $this->collectionOf(range(0, 299));

		$this->assertTrue($collection->containsAll(range(299, 0, -1)));
		$this->assertFalse($collection->containsAll([...range(0, 298), '299']));
		$this->assertFalse($collection->containsAll([...range(0, 298), 299.0]));
	}
}
