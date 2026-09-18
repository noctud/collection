<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set\Extending;

use Noctud\Collection\Set\ImmutableSet;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Verifies that extending a Set self-preserves the subtype at runtime.
 *
 * The fixtures themselves (their `: self` domain methods) are the compile-time
 * narrowing assertions, since tests/ is analysed by PHPStan at level 9.
 */
final class SetExtendingTest extends TestCase
{
	#[Test]
	public function trait_filter_returns_subtype(): void
	{
		$collection = new TraitItemCollection([new SwappableItem(1, true), new SwappableItem(2, false)]);

		$swapped = $collection->onlySwapped();

		self::assertInstanceOf(TraitItemCollection::class, $swapped);
		self::assertCount(1, $swapped);
		self::assertSame(1, $swapped->first()->id);
	}

	#[Test]
	public function trait_ordering_returns_subtype(): void
	{
		$collection = new TraitItemCollection([new SwappableItem(3), new SwappableItem(1), new SwappableItem(2)]);

		$sorted = $collection->sortedById();

		self::assertInstanceOf(TraitItemCollection::class, $sorted);
		self::assertSame([1, 2, 3], array_map(static fn (SwappableItem $i): int => $i->id, $sorted->toArray()));
	}

	#[Test]
	public function trait_mutation_returns_subtype(): void
	{
		$one = new SwappableItem(1);
		$two = new SwappableItem(2);
		$collection = new TraitItemCollection([$one]);

		$added = $collection->with($two);
		$removed = $added->without($one);

		self::assertInstanceOf(TraitItemCollection::class, $added);
		self::assertInstanceOf(TraitItemCollection::class, $removed);
		self::assertCount(2, $added);
		self::assertCount(1, $removed);
		self::assertSame(2, $removed->first()->id);
	}

	#[Test]
	public function trait_set_operation_returns_subtype(): void
	{
		$shared = new SwappableItem(1);
		$left = new TraitItemCollection([$shared, new SwappableItem(2)]);
		$right = new TraitItemCollection([$shared, new SwappableItem(3)]);

		$common = $left->common($right);

		self::assertInstanceOf(TraitItemCollection::class, $common);
		self::assertCount(1, $common);
		self::assertSame($shared, $common->first());
	}

	#[Test]
	public function trait_partition_returns_pair_of_subtype(): void
	{
		$collection = new TraitItemCollection([new SwappableItem(1, true), new SwappableItem(2, false), new SwappableItem(3, true)]);

		[$swapped, $rest] = $collection->splitBySwapped();

		self::assertInstanceOf(TraitItemCollection::class, $swapped);
		self::assertInstanceOf(TraitItemCollection::class, $rest);
		self::assertCount(2, $swapped);
		self::assertCount(1, $rest);
	}

	#[Test]
	public function manual_filter_returns_subtype(): void
	{
		$collection = new ManualItemCollection([new SwappableItem(1, true), new SwappableItem(2, false)]);

		$swapped = $collection->onlySwapped();

		self::assertInstanceOf(ManualItemCollection::class, $swapped);
		self::assertCount(1, $swapped);
	}

	#[Test]
	public function manual_ordering_and_partition_return_subtype(): void
	{
		$collection = new ManualItemCollection([new SwappableItem(1, true), new SwappableItem(2, false)]);

		$sorted = $collection->sortedItems();
		[$swapped, $rest] = $collection->splitBySwapped();

		self::assertInstanceOf(ManualItemCollection::class, $sorted);
		self::assertInstanceOf(ManualItemCollection::class, $swapped);
		self::assertInstanceOf(ManualItemCollection::class, $rest);
	}

	#[Test]
	public function trait_transform_narrows_element_type(): void
	{
		$collection = new TraitItemCollection([new SwappableItem(1, true), new SwappableItem(2, false)]);

		$ids = $collection->toIds();
		$swappedIds = $collection->swappedIds();
		$withNegatives = $collection->idsWithNegatives();
		$flattened = $collection->flattened();

		// The shape changed, so the static type narrows to the base ImmutableSet<int>
		// (asserted by PHPStan via toIds()'s @return). At runtime the result is a plain
		// base set too: the subtype constructor (which enforces the SwappableItem
		// invariant) is never re-entered with transformed elements.
		self::assertInstanceOf(ImmutableSet::class, $ids);
		self::assertNotInstanceOf(TraitItemCollection::class, $ids);
		self::assertNotInstanceOf(TraitItemCollection::class, $swappedIds);
		self::assertNotInstanceOf(TraitItemCollection::class, $withNegatives);
		self::assertNotInstanceOf(TraitItemCollection::class, $flattened);
		self::assertEqualsCanonicalizing([1, 2], $ids->toArray());
		self::assertEqualsCanonicalizing([1], $swappedIds->toArray());
		self::assertEqualsCanonicalizing([1, -1, 2, -2], $withNegatives->toArray());
		self::assertEqualsCanonicalizing($collection->toArray(), $flattened->toArray());
	}

	#[Test]
	public function trait_group_by_with_value_transform_returns_base_type(): void
	{
		$collection = new TraitItemCollection([new SwappableItem(1, true), new SwappableItem(2, false)]);

		$groups = $collection->groupBy(static fn (SwappableItem $i): string => $i->swapped ? 'y' : 'n');
		$ids = $collection->groupBy(static fn (SwappableItem $i): string => $i->swapped ? 'y' : 'n', static fn (SwappableItem $i): int => $i->id);

		self::assertInstanceOf(TraitItemCollection::class, $groups['y']);
		self::assertNotInstanceOf(TraitItemCollection::class, $ids['y']);
		self::assertSame([1], $ids['y']->toArray());
	}

	#[Test]
	public function trait_filter_instance_of_returns_base_type(): void
	{
		$collection = new TraitItemCollection([new SwappableItem(1), new SwappableItem(2)]);

		$filtered = $collection->filterInstanceOf(SwappableItem::class);

		self::assertNotInstanceOf(TraitItemCollection::class, $filtered);
		self::assertCount(3, $filtered->add(new stdClass()));
	}

	#[Test]
	public function manual_transform_returns_base_type(): void
	{
		$collection = new ManualItemCollection([new SwappableItem(1), new SwappableItem(2)]);

		$ids = $collection->toIds();

		self::assertNotInstanceOf(ManualItemCollection::class, $ids);
		self::assertEqualsCanonicalizing([1, 2], $ids->toArray());
	}
}
