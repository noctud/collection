<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Exception\UnsupportedOperationException;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

trait CollectionAggregate
{
	#[Test]
	public function sum_returns_zero_for_empty_collection(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame(0, $collection->sum());
	}

	#[Test]
	public function sum_calculates_sum_of_integers(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$this->assertSame(15, $collection->sum());
	}

	#[Test]
	public function sum_calculates_sum_of_floats(): void
	{
		$collection = $this->collectionOf([1.5, 2.5, 3.0]);
		$this->assertSame(7.0, $collection->sum());
	}

	#[Test]
	public function sum_with_selector(): void
	{
		$collection = $this->collectionOf([
			['value' => 10],
			['value' => 20],
			['value' => 30],
		]);
		$this->assertSame(60, $collection->sum(fn ($element) => $element['value']));
	}

	#[Test]
	public function sum_with_selector_on_objects(): void
	{
		$obj1 = new stdClass();
		$obj1->age = 25;
		$obj2 = new stdClass();
		$obj2->age = 30;
		$obj3 = new stdClass();
		$obj3->age = 35;

		$collection = $this->collectionOf([$obj1, $obj2, $obj3]);
		$this->assertSame(90, $collection->sum(fn ($obj) => $obj->age));
	}

	#[Test]
	public function avg_throws_for_empty_collection(): void
	{
		$collection = $this->collectionOf([]);

		$this->expectException(UnsupportedOperationException::class);
		$collection->avg();
	}

	#[Test]
	public function avg_calculates_average_of_integers(): void
	{
		$collection = $this->collectionOf([2, 4, 6]);
		$this->assertSame(4.0, $collection->avg());
	}

	#[Test]
	public function avg_calculates_average_of_floats(): void
	{
		$collection = $this->collectionOf([1.0, 2.0, 3.0]);
		$this->assertSame(2.0, $collection->avg());
	}

	#[Test]
	public function avg_with_selector(): void
	{
		$collection = $this->collectionOf([
			['score' => 80],
			['score' => 90],
			['score' => 100],
		]);
		$this->assertSame(90.0, $collection->avg(fn ($element) => $element['score']));
	}

	#[Test]
	public function avgOrNull_returns_null_for_empty_collection(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertNull($collection->avgOrNull());
	}

	#[Test]
	public function avgOrNull_calculates_average(): void
	{
		$collection = $this->collectionOf([10, 20, 30]);
		$this->assertSame(20.0, $collection->avgOrNull());
	}

	#[Test]
	public function avgOrNull_with_selector(): void
	{
		$collection = $this->collectionOf([
			['value' => 5],
			['value' => 15],
		]);
		$this->assertSame(10.0, $collection->avgOrNull(fn ($element) => $element['value']));
	}

	#[Test]
	public function max_throws_for_empty_collection(): void
	{
		$collection = $this->collectionOf([]);

		$this->expectException(NoSuchElementException::class);
		$collection->max();
	}

	#[Test]
	public function max_returns_maximum_integer(): void
	{
		$collection = $this->collectionOf([3, 1, 4, 1, 5, 9, 2, 6]);
		$this->assertSame(9, $collection->max());
	}

	#[Test]
	public function max_returns_maximum_string(): void
	{
		$collection = $this->collectionOf(['apple', 'banana', 'cherry']);
		$this->assertSame('cherry', $collection->max());
	}

	#[Test]
	public function max_with_selector(): void
	{
		$collection = $this->collectionOf([
			['age' => 25],
			['age' => 35],
			['age' => 30],
		]);
		$this->assertSame(['age' => 35], $collection->max(fn ($element) => $element['age']));
	}

	#[Test]
	public function max_with_single_element(): void
	{
		$collection = $this->collectionOf([42]);
		$this->assertSame(42, $collection->max());
	}

	#[Test]
	public function maxOrNull_returns_null_for_empty_collection(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertNull($collection->maxOrNull()); /** @phpstan-ignore-line **/
	}

	#[Test]
	public function maxOrNull_returns_maximum(): void
	{
		$collection = $this->collectionOf([5, 10, 3]);
		$this->assertSame(10, $collection->maxOrNull());
	}

	#[Test]
	public function maxOrNull_with_selector(): void
	{
		$collection = $this->collectionOf([
			['price' => 100],
			['price' => 200],
			['price' => 150],
		]);
		$this->assertSame(['price' => 200], $collection->maxOrNull(fn ($element) => $element['price']));
	}

	#[Test]
	public function min_throws_for_empty_collection(): void
	{
		$collection = $this->collectionOf([]);

		$this->expectException(NoSuchElementException::class);
		$collection->min();
	}

	#[Test]
	public function min_returns_minimum_integer(): void
	{
		$collection = $this->collectionOf([3, 1, 4, 1, 5, 9, 2, 6]);
		$this->assertSame(1, $collection->min());
	}

	#[Test]
	public function min_returns_minimum_string(): void
	{
		$collection = $this->collectionOf(['apple', 'banana', 'cherry']);
		$this->assertSame('apple', $collection->min());
	}

	#[Test]
	public function min_with_selector(): void
	{
		$collection = $this->collectionOf([
			['age' => 25],
			['age' => 35],
			['age' => 30],
		]);
		$this->assertSame(['age' => 25], $collection->min(fn ($element) => $element['age']));
	}

	#[Test]
	public function min_with_single_element(): void
	{
		$collection = $this->collectionOf([42]);
		$this->assertSame(42, $collection->min());
	}

	#[Test]
	public function minOrNull_returns_null_for_empty_collection(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertNull($collection->minOrNull()); /** @phpstan-ignore-line **/
	}

	#[Test]
	public function minOrNull_returns_minimum(): void
	{
		$collection = $this->collectionOf([5, 10, 3]);
		$this->assertSame(3, $collection->minOrNull());
	}

	#[Test]
	public function minOrNull_with_selector(): void
	{
		$collection = $this->collectionOf([
			['price' => 100],
			['price' => 200],
			['price' => 150],
		]);
		$this->assertSame(['price' => 100], $collection->minOrNull(fn ($element) => $element['price']));
	}

	#[Test]
	public function max_with_negative_numbers(): void
	{
		$collection = $this->collectionOf([-5, -3, -10, -1]);
		$this->assertSame(-1, $collection->max());
	}

	#[Test]
	public function min_with_negative_numbers(): void
	{
		$collection = $this->collectionOf([-5, -3, -10, -1]);
		$this->assertSame(-10, $collection->min());
	}

	#[Test]
	public function sum_with_negative_numbers(): void
	{
		$collection = $this->collectionOf([-5, 10, -3, 8]);
		$this->assertSame(10, $collection->sum());
	}

	#[Test]
	public function avg_with_mixed_numbers(): void
	{
		$collection = $this->collectionOf([-10, 0, 10, 20]);
		$this->assertSame(5.0, $collection->avg());
	}

	#[Test]
	public function max_on_objects_compares_by_properties(): void
	{
		$a = new stdClass();
		$a->value = 1;

		$b = new stdClass();
		$b->value = 3;

		$c = new stdClass();
		$c->value = 2;

		$collection = $this->collectionOf([$a, $b, $c]);
		$this->assertSame($b, $collection->max());
	}

	#[Test]
	public function min_on_objects_compares_by_properties(): void
	{
		$a = new stdClass();
		$a->value = 1;

		$b = new stdClass();
		$b->value = 3;

		$c = new stdClass();
		$c->value = 2;

		$collection = $this->collectionOf([$a, $b, $c]);
		$this->assertSame($a, $collection->min());
	}

	#[Test]
	public function sum_of_floats_with_selector(): void
	{
		$collection = $this->collectionOf([
			['price' => 1.5],
			['price' => 2.5],
			['price' => 3.0],
		]);
		$this->assertSame(7.0, $collection->sum(fn ($element) => $element['price']));
	}

	#[Test]
	public function avg_of_floats(): void
	{
		$collection = $this->collectionOf([1.5, 2.5, 5.0]);
		$this->assertEqualsWithDelta(3.0, $collection->avg(), 0.0001);
	}

	#[Test]
	public function max_with_floats(): void
	{
		$collection = $this->collectionOf([1.1, 3.14, 2.7]);
		$this->assertSame(3.14, $collection->max());
	}

	#[Test]
	public function min_with_floats(): void
	{
		$collection = $this->collectionOf([1.1, 3.14, 2.7]);
		$this->assertSame(1.1, $collection->min());
	}

	#[Test]
	public function minOf_returns_selector_value(): void
	{
		$collection = $this->collectionOf([
			['age' => 25],
			['age' => 15],
			['age' => 30],
		]);
		$this->assertSame(15, $collection->minOf(fn ($element) => $element['age']));
	}

	#[Test]
	public function minOf_throws_for_empty(): void
	{
		$collection = $this->collectionOf([]);

		$this->expectException(NoSuchElementException::class);
		$collection->minOf(fn ($element) => $element);
	}

	#[Test]
	public function minOf_with_single_element(): void
	{
		$collection = $this->collectionOf([['value' => 42]]);
		$this->assertSame(42, $collection->minOf(fn ($element) => $element['value']));
	}

	#[Test]
	public function minOfOrNull_returns_null_for_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertNull($collection->minOfOrNull(fn ($element) => $element));
	}

	#[Test]
	public function minOfOrNull_returns_value(): void
	{
		$collection = $this->collectionOf([
			['price' => 100],
			['price' => 50],
			['price' => 150],
		]);
		$this->assertSame(50, $collection->minOfOrNull(fn ($element) => $element['price']));
	}

	#[Test]
	public function maxOf_returns_selector_value(): void
	{
		$collection = $this->collectionOf([
			['age' => 25],
			['age' => 15],
			['age' => 30],
		]);
		$this->assertSame(30, $collection->maxOf(fn ($element) => $element['age']));
	}

	#[Test]
	public function maxOf_throws_for_empty(): void
	{
		$collection = $this->collectionOf([]);

		$this->expectException(NoSuchElementException::class);
		$collection->maxOf(fn ($element) => $element);
	}

	#[Test]
	public function maxOf_with_single_element(): void
	{
		$collection = $this->collectionOf([['value' => 42]]);
		$this->assertSame(42, $collection->maxOf(fn ($element) => $element['value']));
	}

	#[Test]
	public function maxOfOrNull_returns_null_for_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertNull($collection->maxOfOrNull(fn ($element) => $element));
	}

	#[Test]
	public function maxOfOrNull_returns_value(): void
	{
		$collection = $this->collectionOf([
			['price' => 100],
			['price' => 50],
			['price' => 150],
		]);
		$this->assertSame(150, $collection->maxOfOrNull(fn ($element) => $element['price']));
	}
}
