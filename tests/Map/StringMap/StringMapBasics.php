<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\StringMap;

use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\StringMap\ImmutableStringMap;
use Noctud\Collection\Map\MutableMap;
use PHPUnit\Framework\Attributes\Test;

trait StringMapBasics
{
	#[Test]
	public function count_of_items(): void
	{
		$empty = $this->mapOf([]);
		$this->assertSame(0, $empty->count());
		$this->assertSame(0, count($empty));
		$this->assertTrue($empty->isEmpty());

		$one = $this->mapOf(['a' => 'value']);
		$this->assertSame(1, $one->count());
		$this->assertSame(1, count($one));
		$this->assertTrue($one->isNotEmpty());

		$multiple = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
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

		$one = $this->mapOf(['k' => 'v']);
		foreach ($one as $k => $v) {
			$this->assertSame('k', $k);
			$this->assertSame('v', $v);
		}

		$multiple = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
		$expected = ['a' => 1, 'b' => 2, 'c' => 3];
		foreach ($multiple as $k => $v) {
			$this->assertSame($expected[$k], $v);
		}
	}

	#[Test]
	public function contains_key(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);

		$this->assertTrue($map->containsKey('a'));
		$this->assertTrue($map->containsKey('b'));
		$this->assertFalse($map->containsKey('c'));
	}

	#[Test]
	public function contains_value(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);

		$this->assertTrue($map->containsValue(1));
		$this->assertTrue($map->containsValue(2));
		$this->assertFalse($map->containsValue(3));
	}

	#[Test]
	public function get(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);

		$this->assertSame(1, $map->get('a'));
		$this->assertSame(2, $map->get('b'));

		$this->expectException(NoSuchElementException::class);
		$map->get('c');
	}

	#[Test]
	public function getOrNull(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);

		$this->assertSame(1, $map->getOrNull('a'));
		$this->assertNull($map->getOrNull('c'));
	}

	#[Test]
	public function getOrDefault(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);

		$this->assertSame(1, $map->getOrDefault('a', 99));
		$this->assertSame(99, $map->getOrDefault('c', 99));
	}

	#[Test]
	public function keys_property(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$keys = $map->keys;
		$this->assertSame(['a', 'b', 'c'], $keys->toArray());
	}

	#[Test]
	public function values_property(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$values = $map->values;
		$this->assertSame([1, 2, 3], $values->toArray());
	}

	#[Test]
	public function entries_property(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);

		$entries = $map->entries;
		$this->assertSame(2, $entries->count());
	}

	#[Test]
	public function first_entry(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$first = $map->entries->first();
		$this->assertSame('a', $first->key);
		$this->assertSame(1, $first->value);
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
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$last = $map->entries->last();
		$this->assertSame('c', $last->key);
		$this->assertSame(3, $last->value);
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
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);

		$filtered = $map->filter(fn (int $v) => $v > 2);

		$this->assertSame(['c' => 3, 'd' => 4], $filtered->toArray());
	}

	#[Test]
	public function filter_keys(): void
	{
		$map = $this->mapOf(['aa' => 1, 'ab' => 2, 'bc' => 3]);

		$filtered = $map->filterKeys(fn (string $k) => str_starts_with($k, 'a'));

		$this->assertSame(['aa' => 1, 'ab' => 2], $filtered->toArray());
	}

	#[Test]
	public function filter_values(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$filtered = $map->filterValues(fn (int $v) => $v % 2 === 1);

		$this->assertSame(['a' => 1, 'c' => 3], $filtered->toArray());
	}

	#[Test]
	public function filter_values_not_null(): void
	{
		/** @var MutableMap<string, int|null>|ImmutableMap<string, int|null> $map */
		$map = $this->mapOf(['a' => 1, 'b' => null, 'c' => 3, 'd' => null]);

		$filtered = $map->filterValuesNotNull();

		$this->assertSame(['a' => 1, 'c' => 3], $filtered->toArray());
	}

	#[Test]
	public function map_values(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$mapped = $map->mapValues(fn (int $v) => $v * 10);

		// Only the values change, so the result keeps its key-typed store
		$this->assertInstanceOf(ImmutableStringMap::class, $mapped);
		$this->assertSame(['a' => 10, 'b' => 20, 'c' => 30], $mapped->toArray());
	}

	#[Test]
	public function map_values_not_null(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => null, 'c' => 3]);

		$mapped = $map->mapValuesNotNull(fn (?int $v) => $v === null ? null : $v * 10);

		$this->assertInstanceOf(ImmutableStringMap::class, $mapped);
		$this->assertSame(['a' => 10, 'c' => 30], $mapped->toArray());
	}

	#[Test]
	public function sorted_by_key(): void
	{
		$map = $this->mapOf(['c' => 3, 'a' => 1, 'b' => 2]);

		$sorted = $map->sortedByKey();

		$this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $sorted->toArray());
	}

	#[Test]
	public function sorted_by_key_descending(): void
	{
		$map = $this->mapOf(['a' => 1, 'c' => 3, 'b' => 2]);

		$sorted = $map->sortedByKeyDesc();

		$this->assertSame(['c' => 3, 'b' => 2, 'a' => 1], $sorted->toArray());
	}

	#[Test]
	public function sorted_by_value(): void
	{
		$map = $this->mapOf(['a' => 3, 'b' => 1, 'c' => 2]);

		$sorted = $map->sortedByValue();

		$this->assertSame(['b' => 1, 'c' => 2, 'a' => 3], $sorted->toArray());
	}

	#[Test]
	public function sorted_by_value_descending(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 3, 'c' => 2]);

		$sorted = $map->sortedByValueDesc();

		$this->assertSame(['b' => 3, 'c' => 2, 'a' => 1], $sorted->toArray());
	}

	#[Test]
	public function reversed(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$reversed = $map->reversed();

		$this->assertSame(['c' => 3, 'b' => 2, 'a' => 1], $reversed->toArray());
	}

	#[Test]
	public function any_quantifier(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$this->assertTrue($map->any(fn (int $v) => $v > 2));
		$this->assertFalse($map->any(fn (int $v) => $v > 10));
	}

	#[Test]
	public function all_quantifier(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$this->assertTrue($map->all(fn (int $v) => $v > 0));
		$this->assertFalse($map->all(fn (int $v) => $v > 2));
	}

	#[Test]
	public function none_quantifier(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$this->assertTrue($map->none(fn (int $v) => $v > 10));
		$this->assertFalse($map->none(fn (int $v) => $v > 2));
	}

	#[Test]
	public function put(): void
	{
		$original = $this->mapOf(['a' => 1]);
		$map = $original->put('b', 2);

		// Both: result contains new value
		$this->assertTrue($map->containsKey('b'));
		$this->assertSame(2, $map->get('b'));

		if ($map instanceof MutableMap) {
			$this->assertSame($original, $map);

			$tracked = $map->tracked();
			$result = $tracked->put('c', 3);
			$this->assertTrue($result->changed);

			$result = $tracked->put('a', 1); // Same value
			$this->assertFalse($result->changed);

			$result = $tracked->put('a', 99); // Different value
			$this->assertTrue($result->changed);
		} else {
			$this->assertNotSame($original, $map);
			$this->assertFalse($original->containsKey('b'));
		}
	}

	#[Test]
	public function remove(): void
	{
		$original = $this->mapOf(['a' => 1, 'b' => 2]);
		$map = $original->remove('a');

		// Both: result doesn't have the key
		$this->assertFalse($map->containsKey('a'));
		$this->assertTrue($map->containsKey('b'));

		if ($map instanceof MutableMap) {
			$this->assertSame($original, $map);

			$tracked = $map->tracked();
			$result = $tracked->remove('b');
			$this->assertTrue($result->changed);

			$result = $tracked->remove('nonexistent');
			$this->assertFalse($result->changed);
		} else {
			$this->assertNotSame($original, $map);
			$this->assertTrue($original->containsKey('a'));
		}
	}

	#[Test]
	public function clear(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);

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
		$original = $this->mapOf(['a' => 1]);
		$map = $original->putAll(['b' => 2, 'c' => 3]);

		$this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $map->toArray());

		if ($map instanceof MutableMap) {
			$this->assertSame($original, $map);

			$tracked = $map->tracked();
			$result = $tracked->putAll(['d' => 4]);
			$this->assertTrue($result->changed);
		} else {
			$this->assertNotSame($original, $map);
			$this->assertSame(['a' => 1], $original->toArray());
		}
	}

	#[Test]
	public function forEach_action(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);

		$collected = [];
		$map->forEach(function (int $v, string $k) use (&$collected) {
			$collected[$k] = $v;
		});

		$this->assertSame(['a' => 1, 'b' => 2], $collected);
	}

	#[Test]
	public function instances(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2]);

		if ($map instanceof MutableMap) {
			$this->assertSame($map, $map->put('c', 3));
			$this->assertSame($map, $map->putAll(['d' => 4]));
			$this->assertSame($map, $map->putAllPairs([['e', 5]]));
			$this->assertSame($map, $map->remove('a'));
			$this->assertSame($map, $map->removeIf(fn ($v, $k) => $v === 0));
			$this->assertSame($map, $map->removeIfKey(fn ($k) => $k === '0'));
			$this->assertSame($map, $map->removeIfValue(fn ($v) => $v === 0));
			$this->assertSame($map, $map->removeNullValues());
		} else {
			$this->assertNotSame($map, $map->put('c', 3));
			$this->assertNotSame($map, $map->putAll(['d' => 4]));
			$this->assertNotSame($map, $map->putAllPairs([['e', 5]]));
			$this->assertNotSame($map, $map->remove('a'));
			$this->assertNotSame($map, $map->removeIf(fn ($v, $k) => $v === 0));
			$this->assertNotSame($map, $map->removeIfKey(fn ($k) => $k === '0'));
			$this->assertNotSame($map, $map->removeIfValue(fn ($v) => $v === 0));
			$this->assertNotSame($map, $map->removeNullValues());
		}
	}
}
