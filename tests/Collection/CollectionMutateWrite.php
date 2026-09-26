<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\MutableCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

trait CollectionMutateWrite
{
	#[Test]
	public function add_element(): void
	{
		$collection = $this->collectionOf([1, 2]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->add(3);
			$this->assertSame($tracked, $result);
			$this->assertSame([1, 2, 3], $collection->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $collection->add(3);
			$this->assertNotSame($collection, $result);
			$this->assertSame([1, 2], $collection->toArray());
			$this->assertSame([1, 2, 3], $result->toArray());
		}
	}

	#[Test]
	public function addFirst_prepends_element(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->addFirst(0);
			$this->assertSame($tracked, $result);
			$this->assertSame(0, $collection->first());
			$this->assertTrue($result->changed);
		} else {
			$result = $collection->addFirst(0);
			$this->assertNotSame($collection, $result);
			$this->assertSame(0, $result->first());
			$this->assertSame(1, $collection->first());
		}
	}

	#[Test]
	public function addFirst_on_empty_collection(): void
	{
		$collection = $this->collectionOf([]);

		if ($collection instanceof MutableCollection) {
			$this->assertSame($collection, $collection->addFirst(42));
			$this->assertSame([42], $collection->toArray());
		} else {
			$result = $collection->addFirst(42);
			$this->assertSame([42], $result->toArray());
		}
	}

	#[Test]
	public function addAll_elements(): void
	{
		$collection = $this->collectionOf([1]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->addAll([2, 3]);
			$this->assertSame($tracked, $result);
			$this->assertSame([1, 2, 3], $collection->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $collection->addAll([2, 3]);
			$this->assertNotSame($collection, $result);
			$this->assertSame([1], $collection->toArray());
			$this->assertSame([1, 2, 3], $result->toArray());
		}
	}

	#[Test]
	public function addAll_empty(): void
	{
		$collection = $this->collectionOf([1, 2]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->addAll([]);
			$this->assertFalse($result->changed);
			$this->assertSame([1, 2], $collection->toArray());
		} else {
			$result = $collection->addAll([]);
			$this->assertSame([1, 2], $result->toArray());
		}
	}

	#[Test]
	public function remove_element(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->removeElement(2);
			$this->assertSame($tracked, $result);
			$this->assertSame([1, 3], $collection->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $collection->removeElement(2);
			$this->assertNotSame($collection, $result);
			$this->assertSame([1, 2, 3], $collection->toArray());
			$this->assertSame([1, 3], $result->toArray());
		}
	}

	#[Test]
	public function remove_nonexistent_element(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->removeElement(99);
			$this->assertSame([1, 2, 3], $collection->toArray());
			$this->assertFalse($result->changed);
		} else {
			$result = $collection->removeElement(99);
			$this->assertSame([1, 2, 3], $result->toArray());
		}
	}

	#[Test]
	public function removeFirst_removes_first_element(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->removeFirst();
			$this->assertSame($tracked, $result);
			$this->assertSame([2, 3], $collection->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $collection->removeFirst();
			$this->assertNotSame($collection, $result);
			$this->assertSame([1, 2, 3], $collection->toArray());
			$this->assertSame([2, 3], $result->toArray());
		}
	}

	#[Test]
	public function removeFirst_on_empty_is_noop(): void
	{
		$collection = $this->collectionOf([]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->removeFirst();
			$this->assertEmpty($collection->toArray());
			$this->assertFalse($result->changed);
		} else {
			$result = $collection->removeFirst();
			$this->assertEmpty($result->toArray());
		}
	}

	#[Test]
	public function removeLast_removes_last_element(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->removeLast();
			$this->assertSame($tracked, $result);
			$this->assertSame([1, 2], $collection->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $collection->removeLast();
			$this->assertNotSame($collection, $result);
			$this->assertSame([1, 2, 3], $collection->toArray());
			$this->assertSame([1, 2], $result->toArray());
		}
	}

	#[Test]
	public function removeLast_on_empty_is_noop(): void
	{
		$collection = $this->collectionOf([]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->removeLast();
			$this->assertEmpty($collection->toArray());
			$this->assertFalse($result->changed);
		} else {
			$result = $collection->removeLast();
			$this->assertEmpty($result->toArray());
		}
	}

	#[Test]
	public function removeIf_removes_matching_elements(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->removeIf(fn (int $v) => $v % 2 === 0);
			$this->assertSame($tracked, $result);
			$this->assertSame([1, 3, 5], $collection->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $collection->removeIf(fn (int $v) => $v % 2 === 0);
			$this->assertNotSame($collection, $result);
			$this->assertSame([1, 2, 3, 4, 5], $collection->toArray());
			$this->assertSame([1, 3, 5], $result->toArray());
		}
	}

	#[Test]
	public function removeIf_predicate_receives_index(): void
	{
		$collection = $this->collectionOf(['a', 'b', 'c', 'd']);

		if ($collection instanceof MutableCollection) {
			$this->assertSame($collection, $collection->removeIf(fn ($v, $i) => $i % 2 === 0));
			$this->assertSame(['b', 'd'], $collection->toArray());
		} else {
			$result = $collection->removeIf(fn ($v, $i) => $i % 2 === 0);
			$this->assertSame(['b', 'd'], $result->toArray());
		}
	}

	#[Test]
	public function removeIf_none_match(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->removeIf(fn (int $v) => $v > 10);
			$this->assertFalse($result->changed);
			$this->assertSame([1, 2, 3], $collection->toArray());
		} else {
			$result = $collection->removeIf(fn (int $v) => $v > 10);
			$this->assertSame([1, 2, 3], $result->toArray());
		}
	}

	#[Test]
	public function removeIf_all_match(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->removeIf(fn (int $v) => $v > 0);
			$this->assertTrue($result->changed);
			$this->assertSame([], $collection->toArray());
		} else {
			$result = $collection->removeIf(fn (int $v) => $v > 0);
			$this->assertSame([], $result->toArray());
		}
	}

	#[Test]
	public function removeAll_elements(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->removeAll([2, 4]);
			$this->assertSame($tracked, $result);
			$this->assertSame([1, 3, 5], $collection->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $collection->removeAll([2, 4]);
			$this->assertNotSame($collection, $result);
			$this->assertSame([1, 2, 3, 4, 5], $collection->toArray());
			$this->assertSame([1, 3, 5], $result->toArray());
		}
	}

	#[Test]
	public function removeAll_empty_iterable(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);

		if ($collection instanceof MutableCollection) {
			$this->assertSame($collection, $collection->removeAll([]));
			$this->assertSame([1, 2, 3], $collection->toArray());
		} else {
			$result = $collection->removeAll([]);
			$this->assertSame([1, 2, 3], $result->toArray());
		}
	}

	#[Test]
	public function retainAll_elements(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->retainAll([2, 4]);
			$this->assertSame($tracked, $result);
			$this->assertSame([2, 4], $collection->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $collection->retainAll([2, 4]);
			$this->assertNotSame($collection, $result);
			$this->assertSame([1, 2, 3, 4, 5], $collection->toArray());
			$this->assertSame([2, 4], $result->toArray());
		}
	}

	#[Test]
	public function retainAll_with_empty_keeps_nothing(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$result = $tracked->retainAll([]);
			$this->assertSame($tracked, $result);
			$this->assertSame([], $collection->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $collection->retainAll([]);
			$this->assertSame([], $result->toArray());
		}
	}

	#[Test]
	#[DataProvider('shouldReturnListProvider')]
	public function toArray_returns_list_after_removal(array $data): void
	{
		$collection = $this->collectionOf($data);

		if ($collection instanceof MutableCollection) {
			$this->assertSame($collection, $collection->removeElement(2));
			$this->assertTrue(array_is_list($collection->toArray()));
		} else {
			$result = $collection->removeElement(2);
			$this->assertTrue(array_is_list($result->toArray()));
		}
	}

	#[Test]
	#[DataProvider('shouldReturnListProvider')]
	public function toArray_returns_list_after_adding_element(array $data): void
	{
		$collection = $this->collectionOf($data);

		if ($collection instanceof MutableCollection) {
			$this->assertSame($collection, $collection->add(2));
			$this->assertTrue(array_is_list($collection->toArray()));
		} else {
			$result = $collection->add(2);
			$this->assertTrue(array_is_list($result->toArray()));
		}
	}

	public static function shouldReturnListProvider(): iterable
	{
		yield 'with list' => [['a', 'b', 'c']];
		yield 'with associative array' => [['a' => 1, 'b' => 2, 'c' => 3]];
	}
}
