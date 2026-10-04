<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\Set\Set;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

trait CollectionSlice
{
	#[Test]
	public function take_first_n(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$this->assertSame([1, 2, 3], $collection->takeFirst(3)->toArray());
	}

	#[Test]
	public function take_more_than_size(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([1, 2], $collection->takeFirst(5)->toArray());
	}

	#[Test]
	public function take_zero(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([], $collection->takeFirst(0)->toArray());
	}

	#[Test]
	public function take_negative(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([], $collection->takeFirst(-1)->toArray());
	}

	#[Test]
	public function drop_first_n(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$this->assertSame([4, 5], $collection->dropFirst(3)->toArray());
	}

	#[Test]
	public function drop_more_than_size(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([], $collection->dropFirst(5)->toArray());
	}

	#[Test]
	public function drop_zero(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([1, 2, 3], $collection->dropFirst(0)->toArray());
	}

	#[Test]
	public function takeLast_n(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$this->assertSame([3, 4, 5], $collection->takeLast(3)->toArray());
	}

	#[Test]
	public function takeLast_more_than_size(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([1, 2], $collection->takeLast(5)->toArray());
	}

	#[Test]
	public function takeLast_zero(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([], $collection->takeLast(0)->toArray());
	}

	#[Test]
	public function takeLast_negative(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([], $collection->takeLast(-1)->toArray());
	}

	#[Test]
	public function dropLast_n(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$this->assertSame([1, 2], $collection->dropLast(3)->toArray());
	}

	#[Test]
	public function dropLast_more_than_size(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([], $collection->dropLast(5)->toArray());
	}

	#[Test]
	public function dropLast_zero(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([1, 2, 3], $collection->dropLast(0)->toArray());
	}

	#[Test]
	public function dropLast_negative(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([1, 2, 3], $collection->dropLast(-1)->toArray());
	}

	#[Test]
	public function takeWhile_predicate(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$this->assertSame([1, 2], $collection->takeWhile(fn ($v) => $v < 3)->toArray());
	}

	#[Test]
	public function takeWhile_none_match(): void
	{
		$collection = $this->collectionOf([5, 6, 7]);
		$this->assertSame([], $collection->takeWhile(fn ($v) => $v < 3)->toArray());
	}

	#[Test]
	public function takeWhile_all_match(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([1, 2], $collection->takeWhile(fn ($v) => $v < 10)->toArray());
	}

	#[Test]
	public function dropWhile_predicate(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$this->assertSame([3, 4, 5], $collection->dropWhile(fn ($v) => $v < 3)->toArray());
	}

	#[Test]
	public function dropWhile_none_match(): void
	{
		$collection = $this->collectionOf([5, 6, 7]);
		$this->assertSame([5, 6, 7], $collection->dropWhile(fn ($v) => $v < 3)->toArray());
	}

	#[Test]
	public function dropWhile_all_match(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([], $collection->dropWhile(fn ($v) => $v < 10)->toArray());
	}

	#[Test]
	public function takeLastWhile_predicate(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$this->assertSame([4, 5], $collection->takeLastWhile(fn ($v) => $v > 3)->toArray());
	}

	#[Test]
	public function takeLastWhile_none_match(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([], $collection->takeLastWhile(fn ($v) => $v > 10)->toArray());
	}

	#[Test]
	public function takeLastWhile_all_match(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([1, 2], $collection->takeLastWhile(fn ($v) => $v < 10)->toArray());
	}

	#[Test]
	public function takeLastWhile_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->takeLastWhile(fn ($v) => true)->toArray());
	}

	#[Test]
	public function dropLastWhile_predicate(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$this->assertSame([1, 2, 3], $collection->dropLastWhile(fn ($v) => $v > 3)->toArray());
	}

	#[Test]
	public function dropLastWhile_none_match(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([1, 2, 3], $collection->dropLastWhile(fn ($v) => $v > 10)->toArray());
	}

	#[Test]
	public function dropLastWhile_all_match(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([], $collection->dropLastWhile(fn ($v) => $v < 10)->toArray());
	}

	#[Test]
	public function dropLastWhile_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->dropLastWhile(fn ($v) => true)->toArray());
	}

	#[Test]
	public function distinct_removes_duplicates(): void
	{
		$collection = $this->collectionOf([1, 2, 2, 3, 1, 3]);

		if ($collection instanceof Set) {
			// Sets are already distinct
			$this->assertSame([1, 2, 3], $collection->distinct()->toArray());
		} else {
			$this->assertSame([1, 2, 3], $collection->distinct()->toArray());
		}
	}

	#[Test]
	public function distinct_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->distinct()->toArray());
	}

	#[Test]
	public function distinctBy_selector(): void
	{
		$collection = $this->collectionOf(['aa', 'bb', 'c', 'ddd']);
		$result = $collection->distinctBy(fn ($v) => strlen($v));

		$this->assertSame(['aa', 'c', 'ddd'], $result->toArray());
	}

	#[Test]
	public function distinctBy_selector_returning_new_objects(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5, 6]);
		$result = $collection->distinctBy(fn ($v) => new stdClass());

		$this->assertSame([1, 2, 3, 4, 5, 6], $result->toArray());
	}

	#[Test]
	public function distinctBy_selector_returning_null(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$result = $collection->distinctBy(fn ($v) => null);

		$this->assertSame([1], $result->toArray());
	}

	#[Test]
	public function reversed_order(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([3, 2, 1], $collection->reversed()->toArray());
	}

	#[Test]
	public function reversed_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->reversed()->toArray());
	}

	#[Test]
	public function reversed_single_element(): void
	{
		$collection = $this->collectionOf([42]);
		$this->assertSame([42], $collection->reversed()->toArray());
	}

	#[Test]
	public function chunked_into_groups(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$chunks = $collection->chunked(2);

		$this->assertSame(3, $chunks->count());
		$this->assertSame([1, 2], $chunks->get(0)->toArray());
		$this->assertSame([3, 4], $chunks->get(1)->toArray());
		$this->assertSame([5], $chunks->get(2)->toArray());
	}

	#[Test]
	public function chunked_exact_division(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4]);
		$chunks = $collection->chunked(2);

		$this->assertSame(2, $chunks->count());
		$this->assertSame([1, 2], $chunks->get(0)->toArray());
		$this->assertSame([3, 4], $chunks->get(1)->toArray());
	}

	#[Test]
	public function chunked_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame(0, $collection->chunked(3)->count());
	}

	#[Test]
	public function chunked_size_larger_than_collection(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$chunks = $collection->chunked(5);

		$this->assertSame(1, $chunks->count());
		$this->assertSame([1, 2], $chunks->get(0)->toArray());
	}

	#[Test]
	public function chunked_zero_size(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame(0, $collection->chunked(0)->count());
	}

	#[Test]
	public function chunked_negative_size(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame(0, $collection->chunked(-1)->count());
	}

	#[Test]
	public function chunked_with_map(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$result = $collection->chunked(2)->map(fn ($chunk) => $chunk->sum());

		$this->assertSame([3, 7, 5], $result->toArray());
	}

	#[Test]
	public function chunked_with_map_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$result = $collection->chunked(2)->map(fn ($chunk) => $chunk->sum());

		$this->assertSame([], $result->toArray());
	}

	#[Test]
	public function windowed_basic(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$windows = $collection->windowed(3);

		$this->assertSame(3, $windows->count());
		$this->assertSame([1, 2, 3], $windows->get(0)->toArray());
		$this->assertSame([2, 3, 4], $windows->get(1)->toArray());
		$this->assertSame([3, 4, 5], $windows->get(2)->toArray());
	}

	#[Test]
	public function windowed_with_step(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$windows = $collection->windowed(3, 2);

		$this->assertSame(2, $windows->count());
		$this->assertSame([1, 2, 3], $windows->get(0)->toArray());
		$this->assertSame([3, 4, 5], $windows->get(1)->toArray());
	}

	#[Test]
	public function windowed_with_partial_windows(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$windows = $collection->windowed(3, 2, true);

		$this->assertSame(3, $windows->count());
		$this->assertSame([1, 2, 3], $windows->get(0)->toArray());
		$this->assertSame([3, 4, 5], $windows->get(1)->toArray());
		$this->assertSame([5], $windows->get(2)->toArray());
	}

	#[Test]
	public function windowed_without_partial_windows(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$windows = $collection->windowed(3, 2, false);

		$this->assertSame(2, $windows->count());
		$this->assertSame([1, 2, 3], $windows->get(0)->toArray());
		$this->assertSame([3, 4, 5], $windows->get(1)->toArray());
	}

	#[Test]
	public function windowed_with_map(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$result = $collection->windowed(3)->map(fn ($w) => $w->sum());

		$this->assertSame([6, 9, 12], $result->toArray());
	}

	#[Test]
	public function windowed_size_larger_than_collection(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$windows = $collection->windowed(5);

		$this->assertSame(0, $windows->count());
	}

	#[Test]
	public function windowed_size_larger_than_collection_with_partial(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$windows = $collection->windowed(5, 1, true);

		$this->assertSame(2, $windows->count());
		$this->assertSame([1, 2], $windows->get(0)->toArray());
		$this->assertSame([2], $windows->get(1)->toArray());
	}

	#[Test]
	public function windowed_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame(0, $collection->windowed(3)->count());
	}

	#[Test]
	public function windowed_size_equals_collection(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$windows = $collection->windowed(3);

		$this->assertSame(1, $windows->count());
		$this->assertSame([1, 2, 3], $windows->get(0)->toArray());
	}

	#[Test]
	public function windowed_step_equals_size_is_chunked(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$windows = $collection->windowed(2, 2, true);

		$this->assertSame(3, $windows->count());
		$this->assertSame([1, 2], $windows->get(0)->toArray());
		$this->assertSame([3, 4], $windows->get(1)->toArray());
		$this->assertSame([5], $windows->get(2)->toArray());
	}

	#[Test]
	public function windowed_zero_size(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame(0, $collection->windowed(0)->count());
	}

	#[Test]
	public function windowed_zero_step(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame(0, $collection->windowed(2, 0)->count());
	}

	#[Test]
	public function windowed_single_element_windows(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$windows = $collection->windowed(1);

		$this->assertSame(3, $windows->count());
		$this->assertSame([1], $windows->get(0)->toArray());
		$this->assertSame([2], $windows->get(1)->toArray());
		$this->assertSame([3], $windows->get(2)->toArray());
	}
}
