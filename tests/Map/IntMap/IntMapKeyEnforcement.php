<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\IntMap;

use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Map\HashMap\ImmutableHashMap;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\MutableMap;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\intMapOf;
use function Noctud\Collection\mutableIntMapOf;

trait IntMapKeyEnforcement
{
	#[Test]
	public function put_accepts_int_key(): void
	{
		$original = $this->mapOf([]);
		$map = $original->put(123, 'value');

		$this->assertSame('value', $map->get(123));

		if ($map instanceof MutableMap) {
			$this->assertSame($original, $map);
		} else {
			$this->assertNotSame($original, $map);
		}
	}

	#[Test]
	public function put_rejects_string_key(): void
	{
		$map = $this->mapOf([]);

		if ($map instanceof MutableMap) {
			$this->expectException(InvalidKeyTypeException::class);
			$this->expectExceptionMessage('IntMap requires int keys, got string');
			/** @phpstan-ignore argument.type */
			$map->put('abc', 'value');
		} else {
			// Immutable maps fall back to HashMap for non-int keys
			/** @phpstan-ignore argument.type */
			$result = $map->put('abc', 'value');
			$this->assertInstanceOf(ImmutableHashMap::class, $result);
			$this->assertSame('value', $result['abc']);
		}
	}

	#[Test]
	public function putIfAbsent_keeps_existing_key(): void
	{
		$map = $this->mapOf([1 => 'a']);
		$result = $map->putIfAbsent(1, 'b');

		$this->assertSame($map, $result);
		$this->assertSame('a', $result->get(1));
	}

	#[Test]
	public function putIfAbsent_rejects_string_key(): void
	{
		$map = $this->mapOf([1 => 'a']);

		if ($map instanceof MutableMap) {
			$this->expectException(InvalidKeyTypeException::class);
			$this->expectExceptionMessage('IntMap requires int keys, got string');
			/** @phpstan-ignore argument.type */
			$map->putIfAbsent('abc', 'value');
		} else {
			// Immutable maps fall back to HashMap for non-int keys, like put()
			/** @phpstan-ignore argument.type */
			$result = $map->putIfAbsent('abc', 'value');
			$this->assertInstanceOf(ImmutableHashMap::class, $result);
			$this->assertSame([1 => 'a', 'abc' => 'value'], $result->toArray());
		}
	}

	#[Test]
	public function getOrNull_with_string_key_returns_null(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		/** @phpstan-ignore argument.type */
		$this->assertNull($map->getOrNull('1'));
		/** @phpstan-ignore argument.type */
		$this->assertNull($map->getOrNull('2'));
	}

	#[Test]
	public function get_with_string_key_throws(): void
	{
		$map = $this->mapOf([1 => 'a']);

		$this->expectException(NoSuchElementException::class);
		/** @phpstan-ignore argument.type */
		$map->get('1');
	}

	#[Test]
	public function containsKey_with_string_key_returns_false(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		/** @phpstan-ignore argument.type */
		$this->assertFalse($map->containsKey('1'));
		/** @phpstan-ignore argument.type */
		$this->assertFalse($map->containsKey('2'));
	}

	#[Test]
	public function remove_with_string_key_does_nothing(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b']);

		if ($map instanceof MutableMap) {
			/** @phpstan-ignore argument.type */
			$map->remove('1');
			$this->assertSame(2, $map->count());
		} else {
			/** @phpstan-ignore argument.type */
			$result = $map->remove('1');
			$this->assertSame(2, $result->count());
		}
	}

	#[Test]
	public function negative_int_key(): void
	{
		$map = $this->mapOf([-1 => 'a', -100 => 'b']);

		$this->assertTrue($map->containsKey(-1));
		$this->assertTrue($map->containsKey(-100));
		$this->assertSame('a', $map->get(-1));
		$this->assertSame('b', $map->get(-100));

		// Key type should be int
		foreach ($map as $k => $v) {
			$this->assertIsInt($k); // @phpstan-ignore method.alreadyNarrowedType
		}
	}

	#[Test]
	public function to_array_preserves_int_keys(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 100 => 'c']);

		$array = $map->toArray();

		$this->assertSame([1 => 'a', 2 => 'b', 100 => 'c'], $array);
	}

	#[Test]
	public function filter_returns_immutable(): void
	{
		$map = $this->mapOf([1 => 10, 2 => 20, 3 => 30]);

		$filtered = $map->filter(fn (int $v) => $v > 10);

		// Transformation methods always return ImmutableMap
		$this->assertInstanceOf(ImmutableMap::class, $filtered);
		$this->assertSame([2 => 20, 3 => 30], $filtered->toArray());
	}

	#[Test]
	public function sorted_returns_immutable(): void
	{
		$map = $this->mapOf([2 => 'b', 1 => 'a', 3 => 'c']);

		$sorted = $map->sortedByKey();

		// Transformation methods always return ImmutableMap
		$this->assertInstanceOf(ImmutableMap::class, $sorted);
		$this->assertSame([1 => 'a', 2 => 'b', 3 => 'c'], $sorted->toArray());
	}

	#[Test]
	public function random_via_keys_view(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$randomKey = $map->keys->random();

		$this->assertContains($randomKey, [1, 2, 3]);
	}

	#[Test]
	public function random_via_values_view(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$randomValue = $map->values->random();

		$this->assertContains($randomValue, ['a', 'b', 'c']);
	}

	#[Test]
	public function random_via_entries_view(): void
	{
		$map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

		$randomEntry = $map->entries->random();

		$this->assertContains($randomEntry->key, [1, 2, 3]);
		$this->assertContains($randomEntry->value, ['a', 'b', 'c']);
	}

	#[Test]
	public function lazy_init_from_generator_with_invalid_key_throws(): void
	{
		$generator = function () {
			yield 'string_key' => 'value'; // Invalid - not an int
		};

		$this->expectException(InvalidKeyTypeException::class);

		// Create lazy map - init not triggered yet
		$map = intMapOf($generator); // @phpstan-ignore argument.type

		// Trigger lazy init - should throw
		$map->count();
	}

	#[Test]
	public function mutable_lazy_init_from_generator_with_invalid_key_throws(): void
	{
		$generator = function () {
			yield 'string_key' => 'value'; // Invalid - not an int
		};

		$this->expectException(InvalidKeyTypeException::class);

		// Create lazy map - init not triggered yet
		$map = mutableIntMapOf($generator); // @phpstan-ignore argument.type

		// Trigger lazy init - should throw
		$map->count();
	}

	#[Test]
	public function array_with_string_key_throws(): void
	{
		$this->expectException(InvalidKeyTypeException::class);

		// Array with string key - init happens immediately, goes through slowerPath
		intMapOf(['abc' => 1]); // @phpstan-ignore argument.type
	}

	#[Test]
	public function random_on_empty_map_returns_null(): void
	{
		$map = $this->mapOf([]);

		$this->assertNull($map->keys->randomOrNull()); // @phpstan-ignore method.alreadyNarrowedType
		$this->assertNull($map->values->randomOrNull()); // @phpstan-ignore method.alreadyNarrowedType
		$this->assertNull($map->entries->randomOrNull()); // @phpstan-ignore method.alreadyNarrowedType
	}

	#[Test]
	public function random_on_empty_map_throws(): void
	{
		$map = $this->mapOf([]);

		$this->expectException(NoSuchElementException::class);
		$map->keys->random();
	}

	#[Test]
	public function put_null_value(): void
	{
		$map = $this->mapOf([1 => 'a']);

		$map = $map->put(1, null);
		$this->assertNull($map->get(1));
		$this->assertTrue($map->containsKey(1));

		$map = $map->put(2, null);
		$this->assertNull($map->get(2));
		$this->assertTrue($map->containsKey(2));
	}

	#[Test]
	public function put_null_value_changed_tracking(): void
	{
		/** @var MutableMap<int, string|null> $map */
		$map = mutableIntMapOf([1 => 'a']);

		$tracked = $map->tracked();

		// Putting null over non-null value should change
		$result = $tracked->put(1, null);
		$this->assertTrue($result->changed);

		// Putting null over null should not change
		$result = $tracked->put(1, null);
		$this->assertFalse($result->changed);

		// Putting null on new key should change
		$result = $tracked->put(2, null);
		$this->assertTrue($result->changed);
	}
}
