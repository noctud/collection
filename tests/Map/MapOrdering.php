<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Noctud\Collection\Map\MapEntry;
use Noctud\Collection\Map\MutableMap;
use PHPUnit\Framework\Attributes\Test;

trait MapOrdering
{
	#[Test]
	public function sortedByKey(): void
	{
		$map = $this->mapOfPairs([['1', 4], ['b', 2], ['a', 3], ['c', 1]]);

		$ascending = $map->sortedByKey();
		$this->assertSame(['1' => 4, 'a' => 3, 'b' => 2, 'c' => 1], $ascending->toArray());
		$this->assertSame('1', $ascending->keys->first());
		$this->assertSame(4, $ascending->values->first());
		$this->assertSame('c', $ascending->keys->last());
		$this->assertSame(1, $ascending->values->last());

		$descending = $map->sortedByKeyDesc();
		$this->assertSame(['c' => 1, 'b' => 2, 'a' => 3, '1' => 4], $descending->toArray());
		$this->assertSame('c', $descending->keys->first());
		$this->assertSame(1, $descending->values->first());
		$this->assertSame('1', $descending->keys->last());
		$this->assertSame(4, $descending->values->last());

		if ($map instanceof MutableMap) {
			$result = $map->sortByKey();
			$this->assertSame($map, $result);
			$this->assertSame(['1' => 4, 'a' => 3, 'b' => 2, 'c' => 1], $map->toArray());

			$result = $map->sortByKeyDesc();
			$this->assertSame($map, $result);
			$this->assertSame(['c' => 1, 'b' => 2, 'a' => 3, '1' => 4], $map->toArray());

			$map2 = $this->mapOfPairs([['1', 4], ['b', 2], ['a', 3], ['c', 1]]);
			if ($map2 instanceof MutableMap) {
				$tracked = $map2->tracked();
				$this->assertTrue($tracked->sortByKey()->changed);
				$this->assertSame(['1' => 4, 'a' => 3, 'b' => 2, 'c' => 1], $map2->toArray());

				$this->assertTrue($tracked->sortByKeyDesc()->changed);
				$this->assertSame(['c' => 1, 'b' => 2, 'a' => 3, '1' => 4], $map2->toArray());

				$map2->clear()->put('a', 1);
				$tracked2 = $map2->tracked();
				$this->assertFalse($tracked2->sortByKey()->changed);
				$this->assertFalse($tracked2->sortByKeyDesc()->changed);
			}
		}
	}

	#[Test]
	public function sortedByValue(): void
	{
		$map = $this->mapOfPairs([['x', 2], ['y', 3], ['z', 1], ['1', 4]]);

		$ascending = $map->sortedByValue();
		$this->assertSame(['z' => 1, 'x' => 2, 'y' => 3, '1' => 4], $ascending->toArray());
		$this->assertSame('z', $ascending->keys->first());
		$this->assertSame(1, $ascending->values->first());
		$this->assertSame('1', $ascending->keys->last());
		$this->assertSame(4, $ascending->values->last());

		$descending = $map->sortedByValueDesc();
		$this->assertSame(['1' => 4, 'y' => 3, 'x' => 2, 'z' => 1], $descending->toArray());
		$this->assertSame('1', $descending->keys->first());
		$this->assertSame(4, $descending->values->first());
		$this->assertSame('z', $descending->keys->last());
		$this->assertSame(1, $descending->values->last());

		if ($map instanceof MutableMap) {
			$result = $map->sortByValue();
			$this->assertSame($map, $result);
			$this->assertSame(['z' => 1, 'x' => 2, 'y' => 3, '1' => 4], $map->toArray());

			$result = $map->sortByValueDesc();
			$this->assertSame($map, $result);
			$this->assertSame(['1' => 4, 'y' => 3, 'x' => 2, 'z' => 1], $map->toArray());

			$map2 = $this->mapOfPairs([['x', 2], ['y', 3], ['z', 1], ['1', 4]]);
			if ($map2 instanceof MutableMap) {
				$tracked = $map2->tracked();
				$this->assertTrue($tracked->sortByValue()->changed);
				$this->assertSame(['z' => 1, 'x' => 2, 'y' => 3, '1' => 4], $map2->toArray());

				$this->assertTrue($tracked->sortByValueDesc()->changed);
				$this->assertSame(['1' => 4, 'y' => 3, 'x' => 2, 'z' => 1], $map2->toArray());

				$map2->clear()->put('a', 1);
				$tracked2 = $map2->tracked();
				$this->assertFalse($tracked2->sortByValue()->changed);
				$this->assertFalse($tracked2->sortByValueDesc()->changed);
			}
		}
	}

	#[Test]
	public function sortedBy_sorts_by_key_and_value(): void
	{
		$map = $this->mapOfPairs([['ax', 2], ['b', 10], ['ccc', 3], ['1', 4]]);
		$selector = fn (int $v, string $k) => strlen($k) + $v; // ax:2->4, b:10->11, ccc:3->6, '1':4->5

		$ascending = $map->sortedBy($selector);
		$this->assertSame(['ax' => 2, '1' => 4, 'ccc' => 3, 'b' => 10], $ascending->toArray());
		$this->assertSame('ax', $ascending->keys->first());
		$this->assertSame(2, $ascending->values->first());
		$this->assertSame('b', $ascending->keys->last());
		$this->assertSame(10, $ascending->values->last());

		$descending = $map->sortedByDesc($selector);
		$this->assertSame(['b' => 10, 'ccc' => 3, '1' => 4, 'ax' => 2], $descending->toArray());
		$this->assertSame('b', $descending->keys->first());
		$this->assertSame(10, $descending->values->first());
		$this->assertSame('ax', $descending->keys->last());
		$this->assertSame(2, $descending->values->last());

		$mutable = self::asMutableMap($map);
		if ($mutable !== null) {
			$result = $mutable->sortBy($selector);
			$this->assertSame($mutable, $result);
			$this->assertSame(['ax' => 2, '1' => 4, 'ccc' => 3, 'b' => 10], $mutable->toArray());

			$result = $mutable->sortByDesc($selector);
			$this->assertSame($mutable, $result);
			$this->assertSame(['b' => 10, 'ccc' => 3, '1' => 4, 'ax' => 2], $mutable->toArray());

			$mutable2 = self::asMutableMap($this->mapOfPairs([['ax', 2], ['b', 10], ['ccc', 3], ['1', 4]]));
			if ($mutable2 !== null) {
				$tracked = $mutable2->tracked();
				$this->assertTrue($tracked->sortBy($selector)->changed);
				$this->assertSame(['ax' => 2, '1' => 4, 'ccc' => 3, 'b' => 10], $mutable2->toArray());

				$this->assertTrue($tracked->sortByDesc($selector)->changed);
				$this->assertSame(['b' => 10, 'ccc' => 3, '1' => 4, 'ax' => 2], $mutable2->toArray());

				$mutable2->clear()->put('a', 1);
				$tracked2 = $mutable2->tracked();
				$this->assertFalse($tracked2->sortBy($selector)->changed);
				$this->assertFalse($tracked2->sortByDesc($selector)->changed);
			}
		}
	}

	#[Test]
	public function sortedByKey_and_sortedByValue_keep_equal_entries_in_their_order(): void
	{
		// strlen(...) takes a single argument: the selector must be handed the key or the value alone.
		$map = $this->mapOfPairs([['aa', 'bb'], ['b', 'c'], ['cc', 'dd'], ['d', 'e']]);

		$this->assertSame(['b', 'd', 'aa', 'cc'], $map->sortedByKey(strlen(...))->keys->toArray());
		$this->assertSame(['aa', 'cc', 'b', 'd'], $map->sortedByKeyDesc(strlen(...))->keys->toArray());
		$this->assertSame(['b', 'd', 'aa', 'cc'], $map->sortedByValue(strlen(...))->keys->toArray());
		$this->assertSame(['aa', 'cc', 'b', 'd'], $map->sortedByValueDesc(strlen(...))->keys->toArray());

		$mutable = self::asMutableMap($map);
		if ($mutable !== null) {
			$this->assertSame(['b', 'd', 'aa', 'cc'], $mutable->sortByKey(strlen(...))->keys->toArray());
			$this->assertSame(['aa', 'cc', 'b', 'd'], $mutable->sortByValueDesc(strlen(...))->keys->toArray());
			$this->assertSame(['b', 'd', 'aa', 'cc'], $mutable->tracked()->sortByValue(strlen(...))->keys->toArray());
		}
	}

	#[Test]
	public function sortedBy_keeps_equal_entries_in_their_order(): void
	{
		$map = $this->mapOfPairs([['a', 1], ['b', 2], ['c', 1], ['d', 2]]);
		$selector = static fn (int $v): int => $v;

		$this->assertSame(['a' => 1, 'c' => 1, 'b' => 2, 'd' => 2], $map->sortedBy($selector)->toArray());
		$this->assertSame(['b' => 2, 'd' => 2, 'a' => 1, 'c' => 1], $map->sortedByDesc($selector)->toArray());

		$mutable = self::asMutableMap($map);
		if ($mutable !== null) {
			$this->assertSame(['a' => 1, 'c' => 1, 'b' => 2, 'd' => 2], $mutable->sortBy($selector)->toArray());
			$this->assertSame(['b' => 2, 'd' => 2, 'a' => 1, 'c' => 1], $mutable->sortByDesc($selector)->toArray());
		}
	}

	#[Test]
	public function sortedWith_sorts_with_comparator(): void
	{
		$map = $this->mapOfPairs([['ax', 2], ['b', 10], ['ccc', 3], ['1', 4]]);

		$withKeys = $map->sortedWithKey(fn (string $k1, string $k2) => strlen($k1) <=> strlen($k2));
		$this->assertSame(['b' => 10, '1' => 4, 'ax' => 2, 'ccc' => 3], $withKeys->toArray());

		$withValues = $map->sortedWithValue(fn (int $v1, int $v2) => $v1 <=> $v2);
		$this->assertSame(['ax' => 2, 'ccc' => 3, '1' => 4, 'b' => 10], $withValues->toArray());

		$withEntries = $map->sortedWith(fn (MapEntry $e1, MapEntry $e2) => ($e1->value + strlen($e1->key)) <=> ($e2->value + strlen($e2->key)));
		$this->assertSame(['ax' => 2, '1' => 4, 'ccc' => 3, 'b' => 10], $withEntries->toArray());

		$mutable = self::asMutableMap($map);
		if ($mutable !== null) {
			$result = $mutable->sortWithKey(fn (string $k1, string $k2) => strlen($k1) <=> strlen($k2));
			$this->assertSame($mutable, $result);
			$this->assertSame(['b' => 10, '1' => 4, 'ax' => 2, 'ccc' => 3], $mutable->toArray());

			$result = $mutable->sortWithValue(fn (int $v1, int $v2) => $v1 <=> $v2);
			$this->assertSame($mutable, $result);
			$this->assertSame(['ax' => 2, 'ccc' => 3, '1' => 4, 'b' => 10], $mutable->toArray());

			$result = $mutable->sortWith(fn (MapEntry $e1, MapEntry $e2) => ($e1->value + strlen($e1->key)) <=> ($e2->value + strlen($e2->key)));
			$this->assertSame($mutable, $result);
			$this->assertSame(['ax' => 2, '1' => 4, 'ccc' => 3, 'b' => 10], $mutable->toArray());

			$mutable2 = self::asMutableMap($this->mapOfPairs([['ax', 2], ['b', 10], ['ccc', 3], ['1', 4]]));
			if ($mutable2 !== null) {
				$tracked = $mutable2->tracked();
				$this->assertTrue($tracked->sortWithKey(fn (string $k1, string $k2) => strlen($k1) <=> strlen($k2))->changed);
				$this->assertSame(['b' => 10, '1' => 4, 'ax' => 2, 'ccc' => 3], $mutable2->toArray());

				$this->assertTrue($tracked->sortWithValue(fn (int $v1, int $v2) => $v1 <=> $v2)->changed);
				$this->assertSame(['ax' => 2, 'ccc' => 3, '1' => 4, 'b' => 10], $mutable2->toArray());

				$this->assertTrue($tracked->sortWith(fn (MapEntry $e1, MapEntry $e2) => ($e1->value + strlen($e1->key)) <=> ($e2->value + strlen($e2->key)))->changed);
				$this->assertSame(['ax' => 2, '1' => 4, 'ccc' => 3, 'b' => 10], $mutable2->toArray());

				$mutable2->clear()->put('a', 1);
				$tracked2 = $mutable2->tracked();
				$this->assertFalse($tracked2->sortWithKey(fn ($k1, $k2) => $k1 <=> $k2)->changed);
				$this->assertFalse($tracked2->sortWithValue(fn ($v1, $v2) => $v1 <=> $v2)->changed);
				$this->assertFalse($tracked2->sortWith(fn (MapEntry $e1, MapEntry $e2) => ($e1->value + strlen($e1->key)) <=> ($e2->value + strlen($e2->key)))->changed);
			}
		}
	}

	#[Test]
	public function sortedByValue_with_floats(): void
	{
		$map = $this->mapOf(['a' => 3.14, 'b' => 1.1, 'c' => 2.7]);

		$ascending = $map->sortedByValue();
		$this->assertSame(['b' => 1.1, 'c' => 2.7, 'a' => 3.14], $ascending->toArray());

		$descending = $map->sortedByValueDesc();
		$this->assertSame(['a' => 3.14, 'c' => 2.7, 'b' => 1.1], $descending->toArray());
	}

	#[Test]
	public function sortedByValue_with_strings(): void
	{
		$map = $this->mapOf(['x' => 'banana', 'y' => 'apple', 'z' => 'cherry']);

		$ascending = $map->sortedByValue();
		$this->assertSame(['y' => 'apple', 'x' => 'banana', 'z' => 'cherry'], $ascending->toArray());

		$descending = $map->sortedByValueDesc();
		$this->assertSame(['z' => 'cherry', 'x' => 'banana', 'y' => 'apple'], $descending->toArray());
	}

	#[Test]
	public function sorted_empty_map(): void
	{
		$map = $this->mapOf([]);

		$this->assertSame([], $map->sortedByKey()->toArray());
		$this->assertSame([], $map->sortedByKeyDesc()->toArray());
		$this->assertSame([], $map->sortedByValue()->toArray());
		$this->assertSame([], $map->sortedByValueDesc()->toArray());
	}

	#[Test]
	public function sorted_single_entry_map(): void
	{
		$map = $this->mapOf(['a' => 1]);

		$this->assertSame(['a' => 1], $map->sortedByKey()->toArray());
		$this->assertSame(['a' => 1], $map->sortedByKeyDesc()->toArray());
		$this->assertSame(['a' => 1], $map->sortedByValue()->toArray());
		$this->assertSame(['a' => 1], $map->sortedByValueDesc()->toArray());
	}

	#[Test]
	public function sortedByValue_with_negative_numbers(): void
	{
		$map = $this->mapOf(['a' => -3, 'b' => 5, 'c' => -1, 'd' => 0]);

		$this->assertSame(['a' => -3, 'c' => -1, 'd' => 0, 'b' => 5], $map->sortedByValue()->toArray());
		$this->assertSame(['b' => 5, 'd' => 0, 'c' => -1, 'a' => -3], $map->sortedByValueDesc()->toArray());
	}

	#[Test]
	public function shuffled_preserves_entries(): void
	{
		$map = $this->mapOfPairs([['a', 1], ['b', 2], ['c', 3], ['d', 4], ['e', 5]]);

		$shuffled = $map->shuffled();
		$this->assertSame(5, $shuffled->count());
		$this->assertSame([1, 2, 3, 4, 5], $shuffled->sortedByValue()->values->toArray());
		$this->assertSame(['a', 'b', 'c', 'd', 'e'], $shuffled->sortedByKey()->keys->toArray());

		if ($map instanceof MutableMap) {
			$this->assertNotSame($map, $shuffled);
		}
	}

	#[Test]
	public function shuffled_empty(): void
	{
		$map = $this->mapOf([]);
		$this->assertSame([], $map->shuffled()->toArray());
	}

	#[Test]
	public function shuffled_single_entry(): void
	{
		$map = $this->mapOf(['a' => 1]);
		$this->assertSame(['a' => 1], $map->shuffled()->toArray());
	}

	#[Test]
	public function shuffle_mutable_in_place(): void
	{
		$map = $this->mapOfPairs([['a', 1], ['b', 2], ['c', 3], ['d', 4], ['e', 5]]);

		if ($map instanceof MutableMap) {
			$result = $map->shuffle();
			$this->assertSame($map, $result);
			$this->assertSame(5, $map->count());
			$this->assertSame([1, 2, 3, 4, 5], $map->sortedByValue()->values->toArray());

			$map2 = $this->mapOfPairs([['a', 1], ['b', 2], ['c', 3], ['d', 4], ['e', 5]]);
			if ($map2 instanceof MutableMap) {
				$tracked = $map2->tracked();
				do {
					$result = $tracked->shuffle();
				} while (!$result->changed);
				$this->assertSame($tracked, $result);
				$this->assertSame(5, $map2->count());
				$this->assertSame([1, 2, 3, 4, 5], $map2->sortedByValue()->values->toArray());
				$this->assertTrue($result->changed);
			}
		} else {
			$this->assertSame(5, $map->count());
		}
	}

	#[Test]
	public function reverse(): void
	{
		$map = $this->mapOfPairs([['1', 4], ['b', 2], ['a', 3], ['c', 1]]);

		$reversed = $map->reversed();
		$this->assertSame(['c' => 1, 'a' => 3, 'b' => 2, '1' => 4], $reversed->toArray());
		$this->assertSame('c', $reversed->keys->first());
		$this->assertSame(1, $reversed->values->first());
		$this->assertSame('1', $reversed->keys->last());
		$this->assertSame(4, $reversed->values->last());

		if ($map instanceof MutableMap) {
			$result = $map->reverse();
			$this->assertSame($map, $result);
			$this->assertSame(['c' => 1, 'a' => 3, 'b' => 2, '1' => 4], $map->toArray());

			$map2 = $this->mapOfPairs([['1', 4], ['b', 2], ['a', 3], ['c', 1]]);
			if ($map2 instanceof MutableMap) {
				$tracked = $map2->tracked();
				$this->assertTrue($tracked->reverse()->changed);
				$this->assertSame(['c' => 1, 'a' => 3, 'b' => 2, '1' => 4], $map2->toArray());

				$map2->clear()->put('a', 1);
				$tracked2 = $map2->tracked();
				$this->assertFalse($tracked2->reverse()->changed);
			}
		}
	}
}
