<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\IntMap;

use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\IntMap\ImmutableIntMap;
use Noctud\Collection\Map\MutableMap;
use PHPUnit\Framework\Attributes\Test;

trait IntMapBasics
{
	#[Test]
	public function count_of_items(): void
	{
		$empty = $this->mapOf([]);
		$this->assertSame(0, $empty->count());
		$this->assertSame(0, count($empty));
		$this->assertTrue($empty->isEmpty());

		$one = $this->mapOf([1 => 'value']);
		$this->assertSame(1, $one->count());
		$this->assertSame(1, count($one));
		$this->assertTrue($one->isNotEmpty());

		$multiple = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);
		$this->assertSame(3, $multiple->count());
		$this->assertSame(3, count($multiple));
		$this->assertTrue($multiple->isNotEmpty());
	}

	#[Test]
	public function loop(): void
	{
		$empty = $this->mapOf([]);
		foreach ($empty as $element) {
			$this->fail('Loop executed on empty map');
		}

		$one = $this->mapOf([1 => 'v']);
		foreach ($one as $k => $v) {
			$this->assertSame(1, $k);
			$this->assertSame('v', $v);
		}

		$multiple = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);
		$expected = [1 => 'a', 2 => 'b', 3 => 'c'];
		foreach ($multiple as $k => $v) {
			$this->assertSame($expected[$k], $v);
		}
	}

	#[Test]
	public function contains_key(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		$this->assertTrue($map->containsKey(1));
		$this->assertTrue($map->containsKey(2));
		$this->assertFalse($map->containsKey(3));
	}

	#[Test]
	public function contains_value(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		$this->assertTrue($map->containsValue('a'));
		$this->assertTrue($map->containsValue('b'));
		$this->assertFalse($map->containsValue('c'));
	}

	#[Test]
	public function get(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		$this->assertSame('a', $map->get(1));
		$this->assertSame('b', $map->get(2));

		$this->expectException(NoSuchElementException::class);
		$map->get(3);
	}

	#[Test]
	public function getOrNull(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		$this->assertSame('a', $map->getOrNull(1));
		$this->assertNull($map->getOrNull(3));
	}

	#[Test]
	public function getOrDefault(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		$this->assertSame('a', $map->getOrDefault(1, 'default'));
		$this->assertSame('default', $map->getOrDefault(3, 'default'));
	}

	#[Test]
	public function keys_property(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$keys = $map->keys;
		$this->assertSame([1, 2, 3], $keys->toArray());
	}

	#[Test]
	public function values_property(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$values = $map->values;
		$this->assertSame(['a', 'b', 'c'], $values->toArray());
	}

	#[Test]
	public function entries_property(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		$entries = $map->entries;
		$this->assertSame(2, $entries->count());
	}

	#[Test]
	public function first_entry(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$first = $map->entries->first();
		$this->assertSame(1, $first->key);
		$this->assertSame('a', $first->value);
	}

	#[Test]
	public function first_entry_on_empty(): void
	{
		$map = $this->mapOf([]);

		$this->assertNull($map->entries->firstOrNull());

		$this->expectException(NoSuchElementException::class);
		$map->entries->first();
	}

	#[Test]
	public function last_entry(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$last = $map->entries->last();
		$this->assertSame(3, $last->key);
		$this->assertSame('c', $last->value);
	}

	#[Test]
	public function last_entry_on_empty(): void
	{
		$map = $this->mapOf([]);

		$this->assertNull($map->entries->lastOrNull());

		$this->expectException(NoSuchElementException::class);
		$map->entries->last();
	}

	#[Test]
	public function filter(): void
	{
		$map = $this->mapOf([1 => 10, 2 => 20, 3 => 30, 4 => 40]);

		$filtered = $map->filter(fn (int $v) => $v > 20);

		$this->assertSame([3 => 30, 4 => 40], $filtered->toArray());
	}

	#[Test]
	public function filter_keys(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$filtered = $map->filterKeys(fn (int $k) => $k < 3);

		$this->assertSame([1 => 'a', 2 => 'b'], $filtered->toArray());
	}

	#[Test]
	public function filter_values(): void
	{
		$map = $this->mapOf([1 => 10, 2 => 20, 3 => 30]);

		$filtered = $map->filterValues(fn (int $v) => $v % 20 === 0);

		$this->assertSame([2 => 20], $filtered->toArray());
	}

	#[Test]
	public function filter_values_not_null(): void
	{
		/** @var MutableMap<int, int|null>|ImmutableMap<int, int|null> $map */
		$map = $this->mapOf([1 => 10, 2 => null, 3 => 30, 4 => null]);

		$filtered = $map->filterValuesNotNull();

		$this->assertSame([1 => 10, 3 => 30], $filtered->toArray());
	}

	#[Test]
	public function map_values(): void
	{
		$map = $this->mapOf([1 => 10, 2 => 20, 3 => 30]);

		$mapped = $map->mapValues(fn (int $v) => $v * 2);

		// Only the values change, so the result keeps its key-typed store
		$this->assertInstanceOf(ImmutableIntMap::class, $mapped);
		$this->assertSame([1 => 20, 2 => 40, 3 => 60], $mapped->toArray());
	}

	#[Test]
	public function map_values_not_null(): void
	{
		$map = $this->mapOf([1 => 10, 2 => null, 3 => 30]);

		$mapped = $map->mapValuesNotNull(fn (?int $v) => $v === null ? null : $v * 2);

		$this->assertInstanceOf(ImmutableIntMap::class, $mapped);
		$this->assertSame([1 => 20, 3 => 60], $mapped->toArray());
	}

	#[Test]
	public function sorted_by_key(): void
	{
		$map = $this->mapOf([3 => 'c', 1 => 'a', 2 => 'b']);

		$sorted = $map->sortedByKey();

		$this->assertSame([1 => 'a', 2 => 'b', 3 => 'c'], $sorted->toArray());
	}

	#[Test]
	public function sorted_by_key_descending(): void
	{
		$map = $this->mapOf([1 => 'a', 3 => 'c', 2 => 'b']);

		$sorted = $map->sortedByKeyDesc();

		$this->assertSame([3 => 'c', 2 => 'b', 1 => 'a'], $sorted->toArray());
	}

	#[Test]
	public function sorted_by_value(): void
	{
		$map = $this->mapOf([1 => 30, 2 => 10, 3 => 20]);

		$sorted = $map->sortedByValue();

		$this->assertSame([2 => 10, 3 => 20, 1 => 30], $sorted->toArray());
	}

	#[Test]
	public function sorted_by_value_descending(): void
	{
		$map = $this->mapOf([1 => 10, 2 => 30, 3 => 20]);

		$sorted = $map->sortedByValueDesc();

		$this->assertSame([2 => 30, 3 => 20, 1 => 10], $sorted->toArray());
	}

	#[Test]
	public function reversed(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$reversed = $map->reversed();

		$this->assertSame([3 => 'c', 2 => 'b', 1 => 'a'], $reversed->toArray());
	}

	#[Test]
	public function any_quantifier(): void
	{
		$map = $this->mapOf([1 => 10, 2 => 20, 3 => 30]);

		$this->assertTrue($map->any(fn (int $v) => $v > 20));
		$this->assertFalse($map->any(fn (int $v) => $v > 100));
	}

	#[Test]
	public function all_quantifier(): void
	{
		$map = $this->mapOf([1 => 10, 2 => 20, 3 => 30]);

		$this->assertTrue($map->all(fn (int $v) => $v > 0));
		$this->assertFalse($map->all(fn (int $v) => $v > 20));
	}

	#[Test]
	public function none_quantifier(): void
	{
		$map = $this->mapOf([1 => 10, 2 => 20, 3 => 30]);

		$this->assertTrue($map->none(fn (int $v) => $v > 100));
		$this->assertFalse($map->none(fn (int $v) => $v > 20));
	}

	#[Test]
	public function put(): void
	{
		$original = $this->mapOf([1 => 'a']);
		$map = $original->put(2, 'b');

		// Both: result contains new value
		$this->assertTrue($map->containsKey(2));
		$this->assertSame('b', $map->get(2));

		if ($map instanceof MutableMap) {
			$this->assertSame($original, $map);

			$tracked = $map->tracked();
			$result = $tracked->put(3, 'c');
			$this->assertTrue($result->changed);

			$result = $tracked->put(1, 'a'); // Same value
			$this->assertFalse($result->changed);

			$result = $tracked->put(1, 'x'); // Different value
			$this->assertTrue($result->changed);
		} else {
			$this->assertNotSame($original, $map);
			$this->assertFalse($original->containsKey(2));
		}
	}

	#[Test]
	public function remove(): void
	{
		$original = $this->mapOf([1 => 'a', 2 => 'b']);
		$map = $original->remove(1);

		// Both: result doesn't have the key
		$this->assertFalse($map->containsKey(1));
		$this->assertTrue($map->containsKey(2));

		if ($map instanceof MutableMap) {
			$this->assertSame($original, $map);

			$tracked = $map->tracked();
			$result = $tracked->remove(2);
			$this->assertTrue($result->changed);

			$result = $tracked->remove(999);
			$this->assertFalse($result->changed);
		} else {
			$this->assertNotSame($original, $map);
			$this->assertTrue($original->containsKey(1));
		}
	}

	#[Test]
	public function clear(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		if ($map instanceof MutableMap) {
			$tracked = $map->tracked();
			$result = $tracked->clear();
			$this->assertTrue($map->isEmpty());
			$this->assertTrue($result->changed);

			$result = $tracked->clear();
			$this->assertFalse($result->changed);
		} else {
			// ImmutableMap doesn't have clear()
			$this->assertSame(2, $map->count());
		}
	}

	#[Test]
	public function putAll(): void
	{
		$original = $this->mapOf([1 => 'a']);
		$map = $original->putAll([2 => 'b', 3 => 'c']);

		$this->assertSame([1 => 'a', 2 => 'b', 3 => 'c'], $map->toArray());

		if ($map instanceof MutableMap) {
			$this->assertSame($original, $map);

			$tracked = $map->tracked();
			$result = $tracked->putAll([4 => 'd']);
			$this->assertTrue($result->changed);
		} else {
			$this->assertNotSame($original, $map);
			$this->assertSame([1 => 'a'], $original->toArray());
		}
	}

	#[Test]
	public function forEach_action(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		$collected = [];
		$map->forEach(function (string $v, int $k) use (&$collected) {
			$collected[$k] = $v;
		});

		$this->assertSame([1 => 'a', 2 => 'b'], $collected);
	}

	#[Test]
	public function instances(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		if ($map instanceof MutableMap) {
			$this->assertSame($map, $map->put(3, 'c'));
			$this->assertSame($map, $map->putAll([4 => 'd']));
			$this->assertSame($map, $map->putAllPairs([[5, 'e']]));
			$this->assertSame($map, $map->remove(1));
			$this->assertSame($map, $map->removeIf(fn ($v, $k) => $v === '0'));
			$this->assertSame($map, $map->removeIfKey(fn ($k) => $k === 0));
			$this->assertSame($map, $map->removeIfValue(fn ($v) => $v === '0'));
			$this->assertSame($map, $map->removeNullValues());
		} else {
			$this->assertNotSame($map, $map->put(3, 'c'));
			$this->assertNotSame($map, $map->putAll([4 => 'd']));
			$this->assertNotSame($map, $map->putAllPairs([[5, 'e']]));
			$this->assertNotSame($map, $map->remove(1));
			$this->assertNotSame($map, $map->removeIf(fn ($v, $k) => $v === '0'));
			$this->assertNotSame($map, $map->removeIfKey(fn ($k) => $k === 0));
			$this->assertNotSame($map, $map->removeIfValue(fn ($v) => $v === '0'));
			$this->assertNotSame($map, $map->removeNullValues());
		}
	}
}
