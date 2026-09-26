<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set;

use Noctud\Collection\Set\MutableSet;
use PHPUnit\Framework\Attributes\Test;

trait SetMutateWrite
{
	#[Test]
	public function uniqueness_enforced_on_add(): void
	{
		$set = $this->collectionOf([1, 2, 3]);

		if ($set instanceof MutableSet) {
			$tracked = $set->tracked();
			$result = $tracked->add(2);
			$this->assertSame([1, 2, 3], $set->toArray());
			$this->assertFalse($result->changed);

			$result = $tracked->add(4);
			$this->assertSame([1, 2, 3, 4], $set->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $set->add(2);
			$this->assertSame([1, 2, 3], $result->toArray());

			$result = $set->add(4);
			$this->assertSame([1, 2, 3, 4], $result->toArray());
		}
	}

	#[Test]
	public function addFirst_moves_existing_to_front(): void
	{
		$set = $this->collectionOf([1, 2, 3]);

		if ($set instanceof MutableSet) {
			$tracked = $set->tracked();
			$result = $tracked->addFirst(3);
			$this->assertSame([3, 1, 2], $set->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $set->addFirst(3);
			$this->assertSame([3, 1, 2], $result->toArray());
			$this->assertSame([1, 2, 3], $set->toArray());
		}
	}

	#[Test]
	public function addFirst_noop_when_already_first(): void
	{
		$set = $this->collectionOf([1, 2, 3]);

		if ($set instanceof MutableSet) {
			$tracked = $set->tracked();
			$result = $tracked->addFirst(1);
			$this->assertSame([1, 2, 3], $set->toArray());
			$this->assertFalse($result->changed);
		} else {
			$result = $set->addFirst(1);
			$this->assertSame([1, 2, 3], $result->toArray());
		}
	}

	#[Test]
	public function addFirst_adds_new_element_at_front(): void
	{
		$set = $this->collectionOf([1, 2, 3]);

		if ($set instanceof MutableSet) {
			$tracked = $set->tracked();
			$result = $tracked->addFirst(0);
			$this->assertSame([0, 1, 2, 3], $set->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $set->addFirst(0);
			$this->assertSame([0, 1, 2, 3], $result->toArray());
		}
	}

	#[Test]
	public function addFirst_preserves_uniqueness(): void
	{
		$set = $this->collectionOf([1, 2, 3]);

		if ($set instanceof MutableSet) {
			$this->assertSame($set, $set->addFirst(2));
			$this->assertSame(3, $set->count());
			$this->assertSame([2, 1, 3], $set->toArray());
		} else {
			$result = $set->addFirst(2);
			$this->assertSame(3, $result->count());
			$this->assertSame([2, 1, 3], $result->toArray());
		}
	}

	#[Test]
	public function uniqueness_enforced_on_addAll(): void
	{
		$set = $this->collectionOf([1, 2, 3]);

		if ($set instanceof MutableSet) {
			$tracked = $set->tracked();
			$result = $tracked->addAll([2, 3, 4, 5]);
			$this->assertSame([1, 2, 3, 4, 5], $set->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $set->addAll([2, 3, 4, 5]);
			$this->assertSame([1, 2, 3, 4, 5], $result->toArray());
		}
	}
}
