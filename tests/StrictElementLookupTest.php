<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests;

use Generator;
use Noctud\Collection\StrictElementLookup;
use Noctud\Collection\Tests\Collection\Fixture\HashableUser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class StrictElementLookupTest extends TestCase
{
	/**
	 * Both strategies must answer the same: scanning with in_array() and looking up hash buckets.
	 * Past 128 elements and lookups, the elements are indexed.
	 *
	 * @return Generator<string, array{int, int}>
	 */
	public static function modeProvider(): Generator
	{
		yield 'scanned' => [0, 1];
		yield 'indexed' => [200, PHP_INT_MAX];
	}

	/**
	 * @param list<mixed> $elements
	 */
	private static function lookupOf(array $elements, int $padding, int $expectedLookups): StrictElementLookup
	{
		return new StrictElementLookup([...$elements, ...range(1000, 1000 + $padding - 1)], $expectedLookups);
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function scalars_match_only_identical_values(int $padding, int $expectedLookups): void
	{
		$lookup = self::lookupOf([1, 'a', true, null], $padding, $expectedLookups);

		$this->assertTrue($lookup->contains(1));
		$this->assertTrue($lookup->contains('a'));
		$this->assertTrue($lookup->contains(true));
		$this->assertTrue($lookup->contains(null));
		$this->assertFalse($lookup->contains('1'));
		$this->assertFalse($lookup->contains(1.0));
		$this->assertFalse($lookup->contains(false));
		$this->assertFalse($lookup->contains(2));
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function floats_match_only_identical_values(int $padding, int $expectedLookups): void
	{
		$lookup = self::lookupOf([0.3, -0.0, NAN], $padding, $expectedLookups);

		$this->assertTrue($lookup->contains(0.3));
		$this->assertTrue($lookup->contains(0.0), '0.0 === -0.0');
		$this->assertFalse($lookup->contains(0.1 + 0.2), 'same hash as 0.3, but not identical');
		$this->assertFalse($lookup->contains(NAN), 'NAN is never identical to itself');
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function arrays_match_only_identical_values(int $padding, int $expectedLookups): void
	{
		$lookup = self::lookupOf([[1, 2], [0.0]], $padding, $expectedLookups);

		$this->assertTrue($lookup->contains([1, 2]));
		$this->assertTrue($lookup->contains([-0.0]), '[0.0] === [-0.0] although their serializations differ');
		$this->assertFalse($lookup->contains([2, 1]));
		$this->assertFalse($lookup->contains(['1', '2']));
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function objects_match_only_the_same_instance(int $padding, int $expectedLookups): void
	{
		$object = new stdClass();
		$user = new HashableUser('1');
		$lookup = self::lookupOf([$object, $user], $padding, $expectedLookups);

		$this->assertTrue($lookup->contains($object));
		$this->assertTrue($lookup->contains($user));
		$this->assertFalse($lookup->contains(new stdClass()));
		$this->assertFalse($lookup->contains(new HashableUser('1')), 'same identity(), but another instance');
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function predicate_answers_like_contains(int $padding, int $expectedLookups): void
	{
		$lookup = self::lookupOf([1, [0.0]], $padding, $expectedLookups);

		$this->assertTrue($lookup->predicate()(1));
		$this->assertTrue($lookup->predicate()([-0.0]));
		$this->assertFalse($lookup->predicate()('1'));
		$this->assertFalse($lookup->predicate(negate: true)(1));
		$this->assertTrue($lookup->predicate(negate: true)('1'));
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function unhashable_elements_are_looked_up_without_error(int $padding, int $expectedLookups): void
	{
		$closed = fopen('php://memory', 'r');
		$this->assertNotFalse($closed);
		fclose($closed);

		$lookup = self::lookupOf([1], $padding, $expectedLookups);
		$this->assertFalse($lookup->contains($closed));

		$lookup->remove($closed);
		$this->assertTrue($lookup->contains(1));
	}

	#[Test]
	public function unhashable_elements_are_scanned(): void
	{
		$closed = fopen('php://memory', 'r');
		$this->assertNotFalse($closed);
		fclose($closed);

		$lookup = new StrictElementLookup([$closed, ...range(1, 200)], PHP_INT_MAX);

		$this->assertTrue($lookup->contains($closed));
		$this->assertTrue($lookup->contains(200));
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function remove_drops_every_identical_element(int $padding, int $expectedLookups): void
	{
		$lookup = self::lookupOf([1, '1', 1, 2], $padding, $expectedLookups);

		$lookup->remove(1);
		$this->assertFalse($lookup->contains(1));
		$this->assertTrue($lookup->contains('1'));

		$lookup->remove('1');
		$lookup->remove(3);
		$lookup->remove(2);
		$this->assertFalse($lookup->contains(2));

		foreach (range(1000, 1000 + $padding - 1) as $element) {
			$this->assertFalse($lookup->isEmpty());
			$lookup->remove($element);
		}

		$this->assertTrue($lookup->isEmpty());
	}

	#[Test]
	public function remove_on_an_index_empties_it(): void
	{
		$elements = range(1, 300);
		$lookup = new StrictElementLookup([...$elements, ...$elements], PHP_INT_MAX);

		foreach ($elements as $element) {
			$this->assertFalse($lookup->isEmpty());
			$lookup->remove($element);
		}

		$this->assertTrue($lookup->isEmpty());
	}

	#[Test]
	public function accepts_any_iterable(): void
	{
		$generator = (static function (): Generator {
			yield 'a' => 1;
			yield 'b' => 2;
		})();

		$lookup = new StrictElementLookup($generator, 1);

		$this->assertTrue($lookup->contains(2));
		$this->assertTrue(new StrictElementLookup(['x' => 1], 1)->contains(1));
		$this->assertTrue(new StrictElementLookup([], 1)->isEmpty());
	}
}
