<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\StringMap;

use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Map\HashMap\ImmutableHashMap;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\MutableMap;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\mutableStringMapOf;

trait StringMapKeyEnforcement
{
	#[Test]
	public function put_accepts_string_key(): void
	{
		$original = $this->mapOf([]);
		$map = $original->put('key', 'value');

		$this->assertSame('value', $map->get('key'));

		if ($map instanceof MutableMap) {
			$this->assertSame($original, $map);
		} else {
			$this->assertNotSame($original, $map);
		}
	}

	#[Test]
	public function put_rejects_int_key(): void
	{
		$map = $this->mapOf([]);

		if ($map instanceof MutableMap) {
			$this->expectException(InvalidKeyTypeException::class);
			$this->expectExceptionMessage('StringMap requires string keys, got int');
			/** @phpstan-ignore argument.type */
			$map->put(123, 'value');
		} else {
			// Immutable maps fall back to HashMap for non-string keys
			/** @phpstan-ignore argument.type */
			$result = $map->put(123, 'value');
			$this->assertInstanceOf(ImmutableHashMap::class, $result);
			$this->assertSame('value', $result[123]);
		}
	}

	#[Test]
	public function getOrNull_with_int_key_returns_null(): void
	{
		$map = $this->mapOf(['a' => 'hello', 'b' => 'world']);

		/** @phpstan-ignore argument.type */
		$this->assertNull($map->getOrNull(1));
		/** @phpstan-ignore argument.type */
		$this->assertNull($map->getOrNull(true));
	}

	#[Test]
	public function get_with_int_key_throws(): void
	{
		$map = $this->mapOf(['a' => 'hello']);

		$this->expectException(NoSuchElementException::class);
		/** @phpstan-ignore argument.type */
		$map->get(1);
	}

	#[Test]
	public function containsKey_with_int_key_returns_false(): void
	{
		$map = $this->mapOf(['a' => 'hello', 'b' => 'world']);

		/** @phpstan-ignore argument.type */
		$this->assertFalse($map->containsKey(1));
		/** @phpstan-ignore argument.type */
		$this->assertFalse($map->containsKey(2));
		/** @phpstan-ignore argument.type */
		$this->assertFalse($map->containsKey(true));
	}

	#[Test]
	public function remove_with_int_key_does_nothing(): void
	{
		$map = $this->mapOf(['a' => 'hello', 'b' => 'world']);

		if ($map instanceof MutableMap) {
			/** @phpstan-ignore argument.type */
			$map->remove(1);
			$this->assertSame(2, $map->count());
		} else {
			/** @phpstan-ignore argument.type */
			$result = $map->remove(1);
			$this->assertSame(2, $result->count());
		}
	}

	#[Test]
	public function numeric_string_key_works_with_generator(): void
	{
		// PHP arrays cast numeric string keys to int, so use generator to preserve string keys
		$generator = (function () {
			yield '123' => 'a';
			yield '456' => 'b';
		})();

		$map = $this->mapOf($generator);

		$this->assertTrue($map->containsKey('123'));
		$this->assertTrue($map->containsKey('456'));
		$this->assertSame('a', $map->get('123'));
		$this->assertSame('b', $map->get('456'));

		// Key type should be string
		foreach ($map as $k => $v) {
			$this->assertIsString($k); // @phpstan-ignore method.alreadyNarrowedType
		}
	}

	#[Test]
	public function filter_returns_immutable(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$filtered = $map->filter(fn (int $v) => $v > 1);

		// Transformation methods always return ImmutableMap
		$this->assertInstanceOf(ImmutableMap::class, $filtered);
		$this->assertSame(['b' => 2, 'c' => 3], $filtered->toArray());
	}

	#[Test]
	public function sorted_returns_immutable(): void
	{
		$map = $this->mapOf(['b' => 2, 'a' => 1, 'c' => 3]);

		$sorted = $map->sortedByKey();

		// Transformation methods always return ImmutableMap
		$this->assertInstanceOf(ImmutableMap::class, $sorted);
		$this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $sorted->toArray());
	}

	#[Test]
	public function sortedBy_hands_numeric_keys_as_strings(): void
	{
		// PHP turns the key '1' into an int: the selector must still be given a string.
		$map = $this->mapOf(['b' => 2, 'a' => 1])->put('1', 3);
		$selector = static fn (int $v, string $k): int => $v;

		$this->assertSame(['a' => 1, 'b' => 2, '1' => 3], $map->sortedBy($selector)->toArray());
		$this->assertSame(['1' => 3, 'b' => 2, 'a' => 1], $map->sortedByDesc($selector)->toArray());

		if ($map instanceof MutableMap) {
			$this->assertSame(['a' => 1, 'b' => 2, '1' => 3], $map->sortBy($selector)->toArray());
		}
	}

	#[Test]
	public function keys_view_hands_numeric_keys_as_strings(): void
	{
		$map = $this->mapOf(['b' => 2])->put('1', 3);

		$this->assertSame(['b', '1'], $map->keys->toArray());
		$this->assertSame([2, 3], $map->values->toArray());
	}

	#[Test]
	public function random_via_keys_view(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$randomKey = $map->keys->random();

		$this->assertContains($randomKey, ['a', 'b', 'c']);
	}

	#[Test]
	public function random_via_values_view(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$randomValue = $map->values->random();

		$this->assertContains($randomValue, [1, 2, 3]);
	}

	#[Test]
	public function random_via_entries_view(): void
	{
		$map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

		$randomEntry = $map->entries->random();

		$this->assertContains($randomEntry->key, ['a', 'b', 'c']);
		$this->assertContains($randomEntry->value, [1, 2, 3]);
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
		$map = $this->mapOf(['a' => 'x']);

		$map = $map->put('a', null);
		$this->assertNull($map->get('a'));
		$this->assertTrue($map->containsKey('a'));

		$map = $map->put('b', null);
		$this->assertNull($map->get('b'));
		$this->assertTrue($map->containsKey('b'));
	}

	#[Test]
	public function put_null_value_changed_tracking(): void
	{
		/** @var MutableMap<string, ?string> $map */
		$map = mutableStringMapOf(['a' => 'x']);

		$tracked = $map->tracked();

		// Putting null over non-null value should change
		$result = $tracked->put('a', null);
		$this->assertTrue($result->changed);

		// Putting null over null should not change
		$result = $tracked->put('a', null);
		$this->assertFalse($result->changed);

		// Putting null on new key should change
		$result = $tracked->put('b', null);
		$this->assertTrue($result->changed);
	}
}
