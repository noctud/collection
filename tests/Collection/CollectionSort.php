<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\MutableCollection;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

trait CollectionSort
{
	#[Test]
	public function sorted(): void
	{
		$collection = $this->collectionOf([3, 1, 2, 4]);

		$ascending = $collection->sorted();
		$this->assertSame([1, 2, 3, 4], $ascending->toArray());
		$this->assertSame(1, $ascending->first());
		$this->assertSame(4, $ascending->last());

		$descending = $collection->sortedDesc();
		$this->assertSame([4, 3, 2, 1], $descending->toArray());
		$this->assertSame(4, $descending->first());
		$this->assertSame(1, $descending->last());

		if ($collection instanceof MutableCollection) {
			$result = $collection->sort();
			$this->assertSame($collection, $result);
			$this->assertSame([1, 2, 3, 4], $collection->toArray());

			$result = $collection->sortDesc();
			$this->assertSame($collection, $result);
			$this->assertSame([4, 3, 2, 1], $collection->toArray());

			$tracked = $collection->tracked();
			$this->assertTrue($tracked->sort()->changed);
			$this->assertSame([1, 2, 3, 4], $collection->toArray());

			$this->assertTrue($tracked->sortDesc()->changed);
			$this->assertSame([4, 3, 2, 1], $collection->toArray());

			$collection->clear()->add(1);
			$tracked2 = $collection->tracked();
			$this->assertFalse($tracked2->sort()->changed);
			$this->assertFalse($tracked2->sortDesc()->changed);
		}
	}

	#[Test]
	public function sortedBy(): void
	{
		$collection = $this->collectionOf(['ccc', 'a', 'bb', 'dddd']);
		$selector = fn ($s) => strlen($s);

		$ascending = $collection->sortedBy($selector);
		$this->assertSame(['a', 'bb', 'ccc', 'dddd'], $ascending->toArray());

		$descending = $collection->sortedByDesc($selector);
		$this->assertSame(['dddd', 'ccc', 'bb', 'a'], $descending->toArray());

		if ($collection instanceof MutableCollection) {
			$result = $collection->sortBy($selector);
			$this->assertSame($collection, $result);
			$this->assertSame(['a', 'bb', 'ccc', 'dddd'], $collection->toArray());

			$result = $collection->sortByDesc($selector);
			$this->assertSame($collection, $result);
			$this->assertSame(['dddd', 'ccc', 'bb', 'a'], $collection->toArray());

			$tracked = $collection->tracked();
			$this->assertTrue($tracked->sortBy($selector)->changed);
			$this->assertSame(['a', 'bb', 'ccc', 'dddd'], $collection->toArray());

			$this->assertTrue($tracked->sortByDesc($selector)->changed);
			$this->assertSame(['dddd', 'ccc', 'bb', 'a'], $collection->toArray());

			$collection->clear()->add('a');
			$tracked2 = $collection->tracked();
			$this->assertFalse($tracked2->sortBy($selector)->changed);
			$this->assertFalse($tracked2->sortByDesc($selector)->changed);
		}
	}

	#[Test]
	public function sortedWith(): void
	{
		$collection = $this->collectionOf(['ccc', 'a', 'bb', 'dddd']);
		$comparator = fn ($a, $b) => strlen($a) <=> strlen($b);

		$ascending = $collection->sortedWith($comparator);
		$this->assertSame(['a', 'bb', 'ccc', 'dddd'], $ascending->toArray());

		$descComparator = fn ($a, $b) => strlen($b) <=> strlen($a);
		$descending = $collection->sortedWith($descComparator);
		$this->assertSame(['dddd', 'ccc', 'bb', 'a'], $descending->toArray());

		if ($collection instanceof MutableCollection) {
			$result = $collection->sortWith($comparator);
			$this->assertSame($collection, $result);
			$this->assertSame(['a', 'bb', 'ccc', 'dddd'], $collection->toArray());

			$collection2 = $this->collectionOf(['ccc', 'a', 'bb', 'dddd']);
			$tracked = $collection2->tracked();
			$this->assertTrue($tracked->sortWith($comparator)->changed);
			$this->assertSame(['a', 'bb', 'ccc', 'dddd'], $collection2->toArray());

			$collection2->clear()->add('a');
			$tracked2 = $collection2->tracked();
			$this->assertFalse($tracked2->sortWith($comparator)->changed);
		}
	}

	#[Test]
	public function sorted_strings_alphabetically(): void
	{
		$collection = $this->collectionOf(['banana', 'apple', 'cherry', 'date']);

		$this->assertSame(['apple', 'banana', 'cherry', 'date'], $collection->sorted()->toArray());
		$this->assertSame(['date', 'cherry', 'banana', 'apple'], $collection->sortedDesc()->toArray());
	}

	#[Test]
	public function sorted_floats(): void
	{
		$collection = $this->collectionOf([3.14, 1.1, 2.7, 0.5]);

		$this->assertSame([0.5, 1.1, 2.7, 3.14], $collection->sorted()->toArray());
		$this->assertSame([3.14, 2.7, 1.1, 0.5], $collection->sortedDesc()->toArray());
	}

	#[Test]
	public function sorted_empty_collection(): void
	{
		$collection = $this->collectionOf([]);

		$this->assertSame([], $collection->sorted()->toArray());
		$this->assertSame([], $collection->sortedDesc()->toArray());
	}

	#[Test]
	public function sorted_single_element(): void
	{
		$collection = $this->collectionOf([42]);

		$this->assertSame([42], $collection->sorted()->toArray());
		$this->assertSame([42], $collection->sortedDesc()->toArray());
	}

	#[Test]
	public function sorted_already_sorted(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4]);

		$this->assertSame([1, 2, 3, 4], $collection->sorted()->toArray());
		$this->assertSame([4, 3, 2, 1], $collection->sortedDesc()->toArray());

		if ($collection instanceof MutableCollection) {
			$tracked = $collection->tracked();
			$this->assertFalse($tracked->sort()->changed);
		}
	}

	#[Test]
	public function sorted_with_duplicates(): void
	{
		$collection = $this->collectionOf([3, 1, 4, 2, 5]);

		$this->assertSame([1, 2, 3, 4, 5], $collection->sorted()->toArray());
		$this->assertSame([5, 4, 3, 2, 1], $collection->sortedDesc()->toArray());
	}

	#[Test]
	public function sorted_negative_numbers(): void
	{
		$collection = $this->collectionOf([-3, 5, -1, 0, 2]);

		$this->assertSame([-3, -1, 0, 2, 5], $collection->sorted()->toArray());
		$this->assertSame([5, 2, 0, -1, -3], $collection->sortedDesc()->toArray());
	}

	#[Test]
	public function sortedBy_keeps_equal_elements_in_their_order(): void
	{
		// strlen(...) takes a single argument: the selector must not be handed anything else.
		$collection = $this->collectionOf(['bb', 'a', 'cc', 'b', 'aa']);

		$this->assertSame(['a', 'b', 'bb', 'cc', 'aa'], $collection->sortedBy(strlen(...))->toArray());
		$this->assertSame(['bb', 'cc', 'aa', 'a', 'b'], $collection->sortedByDesc(strlen(...))->toArray());

		if ($collection instanceof MutableCollection) {
			$this->assertSame(['a', 'b', 'bb', 'cc', 'aa'], $collection->sortBy(strlen(...))->toArray());
			$this->assertSame(['bb', 'cc', 'aa', 'a', 'b'], $collection->sortByDesc(strlen(...))->toArray());
			$this->assertSame(['a', 'b', 'bb', 'cc', 'aa'], $collection->tracked()->sortBy(strlen(...))->toArray());
		}
	}

	#[Test]
	public function sortedBy_on_objects(): void
	{
		$a = new stdClass();
		$a->name = 'Charlie';
		$b = new stdClass();
		$b->name = 'Alice';
		$c = new stdClass();
		$c->name = 'Bob';

		$collection = $this->collectionOf([$a, $b, $c]);
		$sorted = $collection->sortedBy(fn ($o) => $o->name);

		$this->assertSame([$b, $c, $a], $sorted->toArray());
	}

	#[Test]
	public function sortedBy_with_numeric_selector(): void
	{
		$collection = $this->collectionOf([
			['name' => 'c', 'age' => 30],
			['name' => 'a', 'age' => 10],
			['name' => 'b', 'age' => 20],
		]);

		$sorted = $collection->sortedBy(fn ($element) => $element['age']);
		$this->assertSame(10, $sorted->first()['age']);
		$this->assertSame(30, $sorted->last()['age']);

		$sortedDesc = $collection->sortedByDesc(fn ($element) => $element['age']);
		$this->assertSame(30, $sortedDesc->first()['age']);
		$this->assertSame(10, $sortedDesc->last()['age']);
	}

	#[Test]
	public function shuffled_preserves_elements(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);

		$shuffled = $collection->shuffled();
		$this->assertSame(5, $shuffled->count());
		$this->assertSame([1, 2, 3, 4, 5], $shuffled->sorted()->toArray());

		if ($collection instanceof MutableCollection) {
			$this->assertNotSame($collection, $shuffled);
		}
	}

	#[Test]
	public function shuffled_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->shuffled()->toArray());
	}

	#[Test]
	public function shuffled_single_element(): void
	{
		$collection = $this->collectionOf([42]);
		$this->assertSame([42], $collection->shuffled()->toArray());
	}

	#[Test]
	public function shuffle_mutable_in_place(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);

		if ($collection instanceof MutableCollection) {
			$result = $collection->shuffle();
			$this->assertSame($collection, $result);
			$this->assertSame(5, $collection->count());
			$this->assertSame([1, 2, 3, 4, 5], $collection->sorted()->toArray());

			$collection2 = $this->collectionOf([1, 2, 3, 4, 5]);
			if ($collection2 instanceof MutableCollection) {
				$tracked = $collection2->tracked();
				$originalOrder = $collection2->toArray();
				do {
					$trackedResult = $tracked->shuffle();
				} while ($collection2->toArray() === $originalOrder);

				$this->assertSame($tracked, $trackedResult);
				$this->assertSame(5, $collection2->count());
				$this->assertSame([1, 2, 3, 4, 5], $collection2->sorted()->toArray());
				$this->assertTrue($trackedResult->changed);
			}
		} else {
			$this->assertSame(5, $collection->count());
		}
	}
}
