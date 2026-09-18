<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\Set as SetInterface;
use Noctud\Collection\Tests\Collection\Fixture\DetailedHashableUser;
use Noctud\Collection\Tests\Collection\Fixture\HashableUser;
use PHPUnit\Framework\Attributes\Test;

trait CollectionTransform
{
	#[Test]
	public function map_transforms_elements(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$mapped = $collection->map(fn ($v) => $v * 2);
		$this->assertSame([2, 4, 6], $mapped->toArray());
	}

	#[Test]
	public function map_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->map(fn ($v) => $v * 2)->toArray());
	}

	#[Test]
	public function map_receives_key(): void
	{
		$collection = $this->collectionOf(['a', 'b', 'c']);
		$mapped = $collection->map(fn ($v, $k) => "$k:$v");
		$this->assertSame(['0:a', '1:b', '2:c'], $mapped->toArray());
	}

	#[Test]
	public function flatten_nested_arrays(): void
	{
		$collection = $this->collectionOf([[1, 2], [3, 4], [5]]);
		$flattened = $collection->flatten();

		if ($flattened instanceof SetInterface) {
			$this->assertSame([1, 2, 3, 4, 5], $flattened->toArray());
		} else {
			$this->assertSame([1, 2, 3, 4, 5], $flattened->toArray());
		}
	}

	#[Test]
	public function flatten_mixed_with_non_iterables(): void
	{
		$collection = $this->collectionOf([[1, 2], 3, [4]]);
		$flattened = $collection->flatten();

		if ($flattened instanceof SetInterface) {
			$this->assertSame([1, 2, 3, 4], $flattened->toArray());
		} else {
			$this->assertSame([1, 2, 3, 4], $flattened->toArray());
		}
	}

	#[Test]
	public function flatten_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->flatten()->toArray());
	}

	#[Test]
	public function flatMap(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$result = $collection->flatMap(fn ($v) => [$v, $v * 10]);

		if ($result instanceof SetInterface) {
			$this->assertSame([1, 10, 2, 20, 3, 30], $result->toArray());
		} else {
			$this->assertSame([1, 10, 2, 20, 3, 30], $result->toArray());
		}
	}

	#[Test]
	public function flatMap_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->flatMap(fn ($v) => [$v])->toArray());
	}

	#[Test]
	public function joinToString_default(): void
	{
		$collection = $this->collectionOf(['a', 'b', 'c']);
		$this->assertSame('a, b, c', $collection->joinToString());
	}

	#[Test]
	public function joinToString_custom_separator(): void
	{
		$collection = $this->collectionOf(['a', 'b', 'c']);
		$this->assertSame('a-b-c', $collection->joinToString('-'));
	}

	#[Test]
	public function joinToString_with_prefix_and_postfix(): void
	{
		$collection = $this->collectionOf(['a', 'b']);
		$this->assertSame('[a, b]', $collection->joinToString(', ', '[', ']'));
	}

	#[Test]
	public function joinToString_with_limit(): void
	{
		$collection = $this->collectionOf(['a', 'b', 'c', 'd']);
		$this->assertSame('a, b, ...', $collection->joinToString(', ', '', '', 2));
	}

	#[Test]
	public function joinToString_with_custom_truncated(): void
	{
		$collection = $this->collectionOf(['a', 'b', 'c']);
		$this->assertSame('a, [more]', $collection->joinToString(', ', '', '', 1, '[more]'));
	}

	#[Test]
	public function joinToString_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame('', $collection->joinToString());
	}

	#[Test]
	public function joinToString_with_integers(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame('1, 2, 3', $collection->joinToString());
	}

	// --- intersect ---

	#[Test]
	public function intersect_returns_common_elements(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([2, 3], $collection->intersect([2, 3, 4])->toArray());
	}

	#[Test]
	public function intersect_retains_the_left_hashable_object(): void
	{
		$left = new HashableUser('42');
		$right = new DetailedHashableUser('42', 'user@example.com');

		$this->assertSame([$left], $this->collectionOf([$left])->intersect([$right])->toArray());
	}

	#[Test]
	public function intersect_with_empty(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([], $collection->intersect([])->toArray());
	}

	#[Test]
	public function intersect_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->intersect([1, 2])->toArray());
	}

	#[Test]
	public function intersect_with_no_overlap(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([], $collection->intersect([3, 4])->toArray());
	}

	#[Test]
	public function intersect_with_duplicates_in_source(): void
	{
		if ($this->collectionOf([]) instanceof SetInterface) {
			$collection = $this->collectionOf([1, 2, 3]);
			$this->assertSame([2, 3], $collection->intersect([2, 3])->toArray());
		} else {
			$collection = $this->collectionOf([1, 1, 2, 2, 3]);
			$this->assertSame([2, 3], $collection->intersect([2, 3])->toArray());
		}
	}

	#[Test]
	public function intersect_returns_immutable_set(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertInstanceOf(ImmutableSet::class, $collection->intersect([2, 3]));
	}

	// --- union ---

	#[Test]
	public function union_combines_elements(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([1, 2, 3, 4, 5], $collection->union([3, 4, 5])->toArray());
	}

	#[Test]
	public function union_with_empty(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([1, 2, 3], $collection->union([])->toArray());
	}

	#[Test]
	public function union_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([1, 2], $collection->union([1, 2])->toArray());
	}

	#[Test]
	public function union_with_full_overlap(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([1, 2, 3], $collection->union([1, 2, 3])->toArray());
	}

	#[Test]
	public function union_with_no_overlap(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([1, 2, 3, 4], $collection->union([3, 4])->toArray());
	}

	#[Test]
	public function union_with_duplicates_in_source(): void
	{
		if ($this->collectionOf([]) instanceof SetInterface) {
			$collection = $this->collectionOf([1, 2, 3]);
			$this->assertSame([1, 2, 3, 4], $collection->union([3, 4])->toArray());
		} else {
			$collection = $this->collectionOf([1, 1, 2, 2, 3]);
			$this->assertSame([1, 2, 3, 4], $collection->union([3, 4])->toArray());
		}
	}

	#[Test]
	public function union_with_duplicates_in_other(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([1, 2, 3], $collection->union([2, 3, 3])->toArray());
	}

	#[Test]
	public function union_returns_immutable_set(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertInstanceOf(ImmutableSet::class, $collection->union([3]));
	}

	// --- subtract ---

	#[Test]
	public function subtract_removes_other_elements(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4]);
		$this->assertSame([1, 3], $collection->subtract([2, 4])->toArray());
	}

	#[Test]
	public function subtract_with_empty_other(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertSame([1, 2, 3], $collection->subtract([])->toArray());
	}

	#[Test]
	public function subtract_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->subtract([1, 2])->toArray());
	}

	#[Test]
	public function subtract_all_elements(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([], $collection->subtract([1, 2, 3])->toArray());
	}

	#[Test]
	public function subtract_with_duplicates_in_source(): void
	{
		if ($this->collectionOf([]) instanceof SetInterface) {
			$collection = $this->collectionOf([1, 2, 3]);
			$this->assertSame([1, 3], $collection->subtract([2])->toArray());
		} else {
			$collection = $this->collectionOf([1, 1, 2, 2, 3]);
			$this->assertSame([1, 3], $collection->subtract([2])->toArray());
		}
	}

	#[Test]
	public function subtract_returns_immutable_set(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$this->assertInstanceOf(ImmutableSet::class, $collection->subtract([2]));
	}
}
