<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Sequence;

use Countable;
use JsonSerializable;
use Noctud\Collection\Collection;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Set\ImmutableSet;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function Noctud\Collection\listOf;
use function Noctud\Collection\sequenceOf;

final class SequenceFactoryTest extends TestCase
{
	#[Test]
	public function sequenceOf_defaults_to_an_empty_sequence(): void
	{
		$this->assertSame([], sequenceOf()->toArray());
	}

	#[Test]
	public function sequenceOf_accepts_an_array(): void
	{
		$this->assertSame([1, 2, 3], sequenceOf([1, 2, 3])->toArray());
	}

	#[Test]
	public function sequenceOf_accepts_a_collection(): void
	{
		$this->assertSame([1, 2, 3], sequenceOf(listOf([1, 2, 3]))->toArray());
	}

	#[Test]
	public function sequenceOf_accepts_a_raw_generator(): void
	{
		$generator = (static function () {
			yield 1;
			yield 2;
		})();

		$this->assertSame([1, 2], sequenceOf($generator)->toArray());
	}

	#[Test]
	public function sequenceOf_accepts_a_closure(): void
	{
		$this->assertSame([1, 2, 3], sequenceOf(static fn (): array => [1, 2, 3])->toArray());
	}

	#[Test]
	public function sequence_is_not_collection_countable_or_json_serializable(): void
	{
		$sequence = sequenceOf([1]);

		$this->assertNotInstanceOf(Collection::class, $sequence);
		$this->assertNotInstanceOf(Countable::class, $sequence);
		$this->assertNotInstanceOf(JsonSerializable::class, $sequence);
	}

	#[Test]
	public function closure_source_is_invoked_lazily_at_consumption(): void
	{
		$invocations = 0;
		$sequence = sequenceOf(static function () use (&$invocations): array {
			$invocations++;

			return [1, 2];
		});

		$this->assertSame(0, $invocations);
		$this->assertSame([1, 2], $sequence->toArray());
		$this->assertSame(1, $invocations);
	}

	#[Test]
	public function toArray_returns_a_reindexed_list(): void
	{
		$this->assertSame([1, 2, 3], sequenceOf(['a' => 1, 'b' => 2, 'c' => 3])->toArray());
	}

	#[Test]
	public function toList_returns_an_immutable_list(): void
	{
		$list = sequenceOf([1, 2, 3])->toList();

		$this->assertInstanceOf(ImmutableList::class, $list);
		$this->assertSame([1, 2, 3], $list->toArray());
	}

	#[Test]
	public function toSet_returns_an_immutable_set_with_duplicates_removed(): void
	{
		$set = sequenceOf([1, 2, 2, 3, 1])->toSet();

		$this->assertInstanceOf(ImmutableSet::class, $set);
		$this->assertSame([1, 2, 3], $set->toArray());
	}

	#[Test]
	public function toMap_builds_a_map_from_the_key_selector(): void
	{
		$map = sequenceOf(['a', 'bb', 'ccc'])->toMap(static fn (string $v): int => strlen($v));

		$this->assertInstanceOf(ImmutableMap::class, $map);
		$this->assertSame([1 => 'a', 2 => 'bb', 3 => 'ccc'], $map->toArray());
	}

	#[Test]
	public function toMap_applies_the_value_transform_and_the_positional_index(): void
	{
		$map = sequenceOf(['a', 'b'])->toMap(
			static fn (string $v, int $i): string => "$i:$v",
			static fn (string $v): string => strtoupper($v),
		);

		$this->assertSame(['0:a' => 'A', '1:b' => 'B'], $map->toArray());
	}

	#[Test]
	public function toMap_matches_its_collection_counterpart(): void
	{
		$data = ['a', 'bb', 'ccc'];
		$key = static fn (string $v): int => strlen($v);

		// The one materialization the sequence was missing: going through toList() first
		// would build a whole ImmutableList only to throw it away.
		$this->assertSame(listOf($data)->toMap($key)->toArray(), sequenceOf($data)->toMap($key)->toArray());
	}

	#[Test]
	public function foreach_yields_fresh_positional_keys(): void
	{
		$sequence = sequenceOf(['x' => 'a', 'y' => 'b']);

		$firstPassKeys = [];
		foreach ($sequence as $key => $value) {
			$firstPassKeys[] = $key;
		}

		$secondPassKeys = [];
		foreach ($sequence as $key => $value) {
			$secondPassKeys[] = $key;
		}

		$this->assertSame([0, 1], $firstPassKeys);
		$this->assertSame([0, 1], $secondPassKeys);
	}
}
