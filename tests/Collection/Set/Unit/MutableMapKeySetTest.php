<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set\Unit;

use Closure;
use Noctud\Collection\Map\KeyCollisionStrategy;
use Noctud\Collection\Set\Set;
use Noctud\Collection\Tests\Collection\Set\Case\AbstractSetTestCase;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use function Noctud\Collection\mutableMapOf;
use function Noctud\Collection\setOf;

final class MutableMapKeySetTest extends AbstractSetTestCase
{
	/**
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return Set<E>
	 */
	public function collectionOf(iterable|Closure $data): Set
	{
		return mutableMapOf($data)->flip(KeyCollisionStrategy::KeepLast)->keys;
	}

	/**
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return Set<E>
	 */
	public function enumerableOf(iterable|Closure $data): Set
	{
		return $this->collectionOf($data);
	}

	// MapKeySet items are map keys — null is not a valid map key, so tests using null elements are overridden

	#[Test]
	public function contains(): void
	{
		$object = new stdClass();
		$collection = $this->collectionOf([1, 'a', $object, false]);

		$this->assertTrue($collection->contains(1));
		$this->assertTrue($collection->contains('a'));
		$this->assertTrue($collection->contains($object));
		$this->assertTrue($collection->contains(false));

		$this->assertFalse($collection->contains(2));
		$this->assertFalse($collection->contains('b'));
		$this->assertFalse($collection->contains(true));
		$this->assertFalse($collection->contains(new stdClass()));
	}

	#[Test]
	public function contains_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertFalse($collection->contains(1));
	}

	#[Test]
	public function filterNotNull(): void
	{
		// MapKeySet never contains null — filterNotNull returns all elements
		$collection = $this->collectionOf([1, 2, 3]);
		$filtered = $collection->filterNotNull();
		$this->assertSame([1, 2, 3], $filtered->toArray());
	}

	#[Test]
	public function filterNotNull_when_all_null(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain null (null is not a valid map key)');
	}

	#[Test]
	public function filterNotNull_preserves_falsy_values(): void
	{
		$collection = $this->collectionOf([0, false, '']);
		$filtered = $collection->filterNotNull();
		$this->assertSame([0, false, ''], $filtered->toArray());
	}

	#[Test]
	public function mapNotNull_with_nullable_property(): void
	{
		$collection = $this->collectionOf(['a', 'b', 'c']);
		$result = $collection->mapNotNull(fn ($v) => strtoupper($v));
		$this->assertSame(['A', 'B', 'C'], $result->toArray());
	}

	// MapKeySet items are map keys — arrays are not valid map keys, so tests using array elements are skipped

	#[Test]
	public function flatten_nested_arrays(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function flatten_mixed_with_non_iterables(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function sum_with_selector(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function sum_of_floats_with_selector(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function avg_with_selector(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function avgOrNull_with_selector(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function min_with_selector(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function minOrNull_with_selector(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function max_with_selector(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function maxOrNull_with_selector(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function minOf_returns_selector_value(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function minOf_with_single_element(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function minOfOrNull_returns_value(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function maxOf_returns_selector_value(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function maxOf_with_single_element(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function maxOfOrNull_returns_value(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function sortedBy_with_numeric_selector(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function unzip_pairs(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function unzip_single_pair(): void
	{
		$this->markTestSkipped('MapKeySet cannot contain arrays (arrays are not valid map keys)');
	}

	#[Test]
	public function random(): void
	{
		$object = new stdClass();
		$multiple = $this->collectionOf(['a', 'b', 'a', $object, false, 1]);

		foreach (setOf($multiple) as $expected) {
			do {
				$random = $multiple->random();
			} while ($random !== $expected);

			$this->assertSame($expected, $random);
		}
	}
}
