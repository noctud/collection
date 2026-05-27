<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List\Extending;

use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that extending a List self-preserves the subtype at runtime.
 *
 * The fixtures' `: self` domain methods are the compile-time narrowing assertions,
 * since tests/ is analysed by PHPStan at level 9.
 */
final class ListExtendingTest extends TestCase
{
	#[Test]
	public function trait_filter_returns_subtype(): void
	{
		$list = new LineItems([new SwappableItem(1, true), new SwappableItem(2, false)]);

		$swapped = $list->onlySwapped();

		self::assertInstanceOf(LineItems::class, $swapped);
		self::assertCount(1, $swapped);
	}

	#[Test]
	public function trait_ordering_returns_subtype(): void
	{
		$list = new LineItems([new SwappableItem(3), new SwappableItem(1), new SwappableItem(2)]);

		$sorted = $list->sortedById();

		self::assertInstanceOf(LineItems::class, $sorted);
		self::assertSame([1, 2, 3], array_map(static fn (SwappableItem $i): int => $i->id, $sorted->toArray()));
	}

	#[Test]
	public function trait_mutation_returns_subtype(): void
	{
		$list = new LineItems([new SwappableItem(1)]);

		$appended = $list->append(new SwappableItem(2));
		$replaced = $appended->replaceAt(0, new SwappableItem(9));

		self::assertInstanceOf(LineItems::class, $appended);
		self::assertInstanceOf(LineItems::class, $replaced);
		self::assertCount(2, $appended);
		self::assertSame(9, $replaced->first()->id);
	}

	#[Test]
	public function trait_slice_and_take_return_subtype(): void
	{
		$list = new LineItems([new SwappableItem(1), new SwappableItem(2), new SwappableItem(3), new SwappableItem(4)]);

		$firstTwo = $list->firstTwo();
		$middle = $list->middle();

		self::assertInstanceOf(LineItems::class, $firstTwo);
		self::assertInstanceOf(LineItems::class, $middle);
		self::assertSame([1, 2], array_map(static fn (SwappableItem $i): int => $i->id, $firstTwo->toArray()));
		self::assertSame([2, 3], array_map(static fn (SwappableItem $i): int => $i->id, $middle->toArray()));
	}

	#[Test]
	public function trait_partition_returns_pair_of_subtype(): void
	{
		$list = new LineItems([new SwappableItem(1, true), new SwappableItem(2, false), new SwappableItem(3, true)]);

		[$swapped, $rest] = $list->splitBySwapped();

		self::assertInstanceOf(LineItems::class, $swapped);
		self::assertInstanceOf(LineItems::class, $rest);
		self::assertCount(2, $swapped);
		self::assertCount(1, $rest);
	}

	#[Test]
	public function manual_filter_and_sorted_return_subtype(): void
	{
		$list = new ManualLineItems([new SwappableItem(2, true), new SwappableItem(1, false)]);

		$swapped = $list->onlySwapped();
		$sorted = $list->sortedItems();

		self::assertInstanceOf(ManualLineItems::class, $swapped);
		self::assertInstanceOf(ManualLineItems::class, $sorted);
		self::assertCount(1, $swapped);
	}

	#[Test]
	public function trait_transform_narrows_element_type(): void
	{
		$list = new LineItems([new SwappableItem(1, true), new SwappableItem(2, false)]);

		$ids = $list->toIds();

		// The shape changed, so the static type narrows to the base ImmutableList<int>
		// (asserted by PHPStan via toIds()'s @return). At runtime the SelfPreserving
		// factory still builds `new static`, so the object remains an ImmutableList.
		self::assertInstanceOf(ImmutableList::class, $ids);
		self::assertSame([1, 2], $ids->toArray());
		self::assertSame([1], $list->swappedIds()->toArray());
		self::assertSame([1, -1, 2, -2], $list->idsWithNegatives()->toArray());
	}
}
