<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Sequence;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function Noctud\Collection\listOf;
use function Noctud\Collection\sequenceOf;

final class SequenceTransformTest extends TestCase
{
	#[Test]
	public function filter_keeps_matching_elements_reindexed(): void
	{
		$this->assertSame([2, 4], sequenceOf([1, 2, 3, 4])->filter(static fn (int $v): bool => $v % 2 === 0)->toArray());
	}

	#[Test]
	public function filter_matches_its_collection_counterpart(): void
	{
		$data = [1, 2, 3, 4, 5];
		$predicate = static fn (int $v): bool => $v % 2 === 1;

		$this->assertSame(
			listOf($data)->filter($predicate)->toArray(),
			sequenceOf($data)->filter($predicate)->toArray(),
		);
	}

	#[Test]
	public function map_transforms_each_element(): void
	{
		$this->assertSame(
			['1', '2'],
			sequenceOf([1, 2])
				->map(static fn (int $v): string => (string) $v)
				->toArray()
		);
	}

	#[Test]
	public function map_matches_its_collection_counterpart(): void
	{
		$data = [1, 2, 3];
		$transform = static fn (int $v): int => $v * $v;

		$this->assertSame(
			listOf($data)->map($transform)->toArray(),
			sequenceOf($data)->map($transform)->toArray(),
		);
	}

	#[Test]
	public function predicate_receives_positional_index(): void
	{
		$this->assertSame(
			['a', 'c'],
			sequenceOf(['a', 'b', 'c', 'd'])->filter(static fn (string $v, int $i): bool => $i % 2 === 0)->toArray(),
		);
	}

	#[Test]
	public function map_after_filter_receives_reindexed_positions(): void
	{
		$result = sequenceOf(['a', 'b', 'c', 'd'])
			->filter(static fn (string $v): bool => $v !== 'b')
			->map(static fn (string $v, int $i): string => "$i:$v")
			->toArray();

		$this->assertSame(['0:a', '1:c', '2:d'], $result);
	}
}
