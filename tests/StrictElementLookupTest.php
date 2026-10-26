<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests;

use Generator;
use LogicException;
use Noctud\Collection\Hashable;
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
	 * Past 128 elements, the elements are indexed once 128 lookups have scanned them.
	 *
	 * @return Generator<string, array{bool}>
	 */
	public static function modeProvider(): Generator
	{
		yield 'scanned' => [false];
		yield 'indexed' => [true];
	}

	/**
	 * @param list<mixed> $elements
	 */
	private static function lookupOf(array $elements, bool $indexed): StrictElementLookup
	{
		if (!$indexed) {
			return new StrictElementLookup($elements);
		}

		$lookup = new StrictElementLookup([...$elements, ...self::padding()]);
		for ($i = 0; $i < 128; $i++) {
			$lookup->contains('absent');
		}

		return $lookup;
	}

	/**
	 * @return list<int>
	 */
	private static function padding(): array
	{
		return range(1000, 1199);
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function scalars_match_only_identical_values(bool $indexed): void
	{
		$lookup = self::lookupOf([1, 'a', true, null], $indexed);

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
	public function floats_match_only_identical_values(bool $indexed): void
	{
		$lookup = self::lookupOf([0.3, -0.0, NAN], $indexed);

		$this->assertTrue($lookup->contains(0.3));
		$this->assertTrue($lookup->contains(0.0), '0.0 === -0.0');
		$this->assertFalse($lookup->contains(0.1 + 0.2), 'same hash as 0.3, but not identical');
		$this->assertFalse($lookup->contains(NAN), 'NAN is never identical to itself');
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function arrays_match_only_identical_values(bool $indexed): void
	{
		$lookup = self::lookupOf([[1, 2], [0.0]], $indexed);

		$this->assertTrue($lookup->contains([1, 2]));
		$this->assertTrue($lookup->contains([-0.0]), '[0.0] === [-0.0] although their serializations differ');
		$this->assertFalse($lookup->contains([2, 1]));
		$this->assertFalse($lookup->contains(['1', '2']));
		$this->assertFalse($lookup->contains('a'), 'shares the bucket of the arrays');
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function objects_match_only_the_same_instance(bool $indexed): void
	{
		$object = new stdClass();
		$user = new HashableUser('1');
		$lookup = self::lookupOf([$object, $user], $indexed);

		$this->assertTrue($lookup->contains($object));
		$this->assertTrue($lookup->contains($user));
		$this->assertFalse($lookup->contains(new stdClass()));
		$this->assertFalse($lookup->contains(new HashableUser('1')), 'same identity(), but another instance');
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function objects_are_not_asked_their_identity(bool $indexed): void
	{
		$entity = new class implements Hashable {
			public function identity(): string
			{
				throw new LogicException('An entity without an id has no identity yet.');
			}
		};
		$lookup = self::lookupOf([$entity], $indexed);

		$this->assertTrue($lookup->contains($entity));
		$lookup->remove($entity);
		$this->assertFalse($lookup->contains($entity));
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function predicate_answers_like_contains(bool $indexed): void
	{
		$lookup = self::lookupOf([1, [0.0]], $indexed);

		$this->assertTrue($lookup->predicate()(1));
		$this->assertTrue($lookup->predicate()([-0.0]));
		$this->assertFalse($lookup->predicate()('1'));
		$this->assertFalse($lookup->predicate(negate: true)(1));
		$this->assertTrue($lookup->predicate(negate: true)('1'));
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function unhashable_elements_are_looked_up_without_error(bool $indexed): void
	{
		$closed = fopen('php://memory', 'r');
		$this->assertNotFalse($closed);
		fclose($closed);

		$lookup = self::lookupOf([1], $indexed);
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

		$lookup = self::lookupOf([$closed], indexed: true);

		$this->assertTrue($lookup->contains($closed));
		$this->assertTrue($lookup->contains(1199));
	}

	#[Test]
	#[DataProvider('modeProvider')]
	public function remove_drops_every_identical_element(bool $indexed): void
	{
		$lookup = self::lookupOf([1, '1', 1, 2], $indexed);

		$lookup->remove(1);
		$this->assertFalse($lookup->contains(1));
		$this->assertTrue($lookup->contains('1'));

		$lookup->remove('1');
		$lookup->remove(3);
		$lookup->remove(2);
		$this->assertFalse($lookup->contains(2));

		foreach ($indexed ? self::padding() : [] as $element) {
			$this->assertFalse($lookup->isEmpty());
			$lookup->remove($element);
		}

		$this->assertTrue($lookup->isEmpty());
	}

	#[Test]
	public function remove_on_an_index_empties_it(): void
	{
		$elements = range(1, 300);
		$lookup = new StrictElementLookup([...$elements, ...$elements]);

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

		$lookup = new StrictElementLookup($generator);

		$this->assertTrue($lookup->contains(2));
		$this->assertTrue(new StrictElementLookup(['x' => 1])->contains(1));
		$this->assertTrue(new StrictElementLookup([])->isEmpty());
	}
}
