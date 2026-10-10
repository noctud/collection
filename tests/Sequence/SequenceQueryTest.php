<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Sequence;

use Generator;
use Noctud\Collection\Exception\NonReplayableSourceException;
use Noctud\Collection\Sequence\Sequence;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function Noctud\Collection\listOf;
use function Noctud\Collection\sequenceOf;

final class SequenceQueryTest extends TestCase
{
	#[Test]
	public function isEmpty_and_isNotEmpty_answer_on_the_first_element(): void
	{
		$this->assertTrue(sequenceOf([])->isEmpty());
		$this->assertFalse(sequenceOf([])->isNotEmpty());

		$this->assertFalse(sequenceOf([1, 2])->isEmpty());
		$this->assertTrue(sequenceOf([1, 2])->isNotEmpty());

		// Emptied by a filter rather than empty at the source: the realistic way a pipeline ends
		// up with nothing.
		$this->assertTrue(sequenceOf([1, 2])->filter(static fn (int $v): bool => $v > 9)->isEmpty());
	}

	#[Test]
	public function isEmpty_is_false_on_a_sequence_of_one_null(): void
	{
		// A sequence holding null is not an empty sequence - the distinction lastOrNull() cannot
		// make, isEmpty() can.
		$this->assertFalse(sequenceOf([null])->isEmpty());
	}

	#[Test]
	public function contains_uses_strict_comparison(): void
	{
		$this->assertTrue(sequenceOf([1, 2, 3])->contains(2));
		$this->assertFalse(sequenceOf([1, 2, 3])->contains(9));

		// '2' is not 2, and only a sequence that could hold either type can even be asked.
		/** @var Sequence<int|string> $mixed */
		$mixed = sequenceOf([1, 2, 3]);
		$this->assertFalse($mixed->contains('2'));
	}

	#[Test]
	public function containsAll_answers_in_a_single_pass(): void
	{
		$this->assertTrue(sequenceOf([1, 2, 3])->containsAll([3, 1]));
		$this->assertFalse(sequenceOf([1, 2, 3])->containsAll([1, 9]));

		// Nothing to look for is trivially satisfied, and pulls nothing.
		$this->assertTrue(sequenceOf([1, 2, 3])->containsAll([]));
	}

	#[Test]
	public function containsAll_ignores_repeats_among_the_wanted_values(): void
	{
		// The question is whether 1 is there, not whether it is there twice - the same answer the
		// eager side gives, which the parity assertion pins.
		$this->assertTrue(sequenceOf([1, 2])->containsAll([1, 1]));
		$this->assertSame(listOf([1, 2])->containsAll([1, 1]), sequenceOf([1, 2])->containsAll([1, 1]));
	}

	#[Test]
	public function containsAll_with_many_wanted_values_compares_strictly(): void
	{
		// Enough wanted values for them to be indexed by hash, repeats included.
		$data = range(0, 299);
		$this->assertTrue(sequenceOf($data)->containsAll([...$data, ...$data]));

		// '299' and 299.0 are not 299, and only a sequence that could hold them can even be asked.
		/** @var Sequence<int|string|float> $mixed */
		$mixed = sequenceOf($data);
		$this->assertFalse($mixed->containsAll([...range(0, 298), '299']));
		$this->assertFalse($mixed->containsAll([...range(0, 298), 299.0]));
	}

	#[Test]
	public function containsAll_walks_a_one_shot_source_once(): void
	{
		$sequence = sequenceOf((static function (): Generator {
			yield 1;
			yield 2;
			yield 3;
		})());

		// One lookup per value would need one pass per value, which this source cannot give.
		$this->assertTrue($sequence->containsAll([1, 3]));
	}

	#[Test]
	public function all_any_and_none_on_an_empty_sequence(): void
	{
		$never = static fn (int $v): bool => false;

		$this->assertTrue(sequenceOf([])->all($never));
		$this->assertFalse(sequenceOf([])->any($never));
		$this->assertTrue(sequenceOf([])->none($never));
	}

	#[Test]
	public function all_any_and_none_answer_the_predicate(): void
	{
		$even = static fn (int $v): bool => $v % 2 === 0;

		$this->assertTrue(sequenceOf([2, 4])->all($even));
		$this->assertFalse(sequenceOf([2, 3])->all($even));

		$this->assertTrue(sequenceOf([1, 2])->any($even));
		$this->assertFalse(sequenceOf([1, 3])->any($even));

		$this->assertTrue(sequenceOf([1, 3])->none($even));
		$this->assertFalse(sequenceOf([1, 2])->none($even));
	}

	#[Test]
	public function predicates_receive_the_positional_index_of_their_own_stage(): void
	{
		$seen = [];
		$sequence = sequenceOf([10, 20, 30])->filter(static fn (int $v): bool => $v > 10);

		$sequence->all(static function (int $v, int $i) use (&$seen): bool {
			$seen[] = [$i, $v];

			return true;
		});

		// The filter reindexes, so 20 is at 0 here rather than at its source position.
		$this->assertSame([[0, 20], [1, 30]], $seen);
	}

	#[Test]
	public function count_and_countWhere_count_the_elements_of_their_own_stage(): void
	{
		$even = static fn (int $v): bool => $v % 2 === 0;

		$this->assertSame(0, sequenceOf([])->count());
		$this->assertSame(4, sequenceOf([1, 2, 3, 4])->count());
		$this->assertSame(2, sequenceOf([1, 2, 3, 4])->countWhere($even));
		$this->assertSame(2, sequenceOf([1, 2, 3, 4])->filter($even)->count());
	}

	#[Test]
	public function querying_matches_its_collection_counterpart(): void
	{
		$data = [3, 1, 4, 1, 5];
		$even = static fn (int $v): bool => $v % 2 === 0;

		$this->assertSame(listOf($data)->isEmpty(), sequenceOf($data)->isEmpty());
		$this->assertSame(listOf([])->isEmpty(), sequenceOf([])->isEmpty());
		$this->assertSame(listOf($data)->isNotEmpty(), sequenceOf($data)->isNotEmpty());
		$this->assertSame(listOf($data)->contains(4), sequenceOf($data)->contains(4));
		$this->assertSame(listOf($data)->contains(9), sequenceOf($data)->contains(9));
		$this->assertSame(listOf($data)->containsAll([1, 4]), sequenceOf($data)->containsAll([1, 4]));
		$this->assertSame(listOf($data)->containsAll([1, 9]), sequenceOf($data)->containsAll([1, 9]));
		$this->assertSame(listOf($data)->all($even), sequenceOf($data)->all($even));
		$this->assertSame(listOf($data)->any($even), sequenceOf($data)->any($even));
		$this->assertSame(listOf($data)->none($even), sequenceOf($data)->none($even));
		$this->assertSame(listOf($data)->count(), sequenceOf($data)->count());
		$this->assertSame(listOf($data)->countWhere($even), sequenceOf($data)->countWhere($even));
	}

	#[Test]
	public function isEmpty_pulls_exactly_one_element(): void
	{
		$pulled = [];
		$sequence = $this->loggingSequence($pulled);

		$this->assertFalse($sequence->isEmpty());
		$this->assertSame([1], $pulled);
	}

	#[Test]
	public function contains_stops_pulling_at_the_first_match(): void
	{
		$pulled = [];
		$sequence = $this->loggingSequence($pulled);

		$this->assertTrue($sequence->contains(2));
		$this->assertSame([1, 2], $pulled);
	}

	#[Test]
	public function any_stops_pulling_at_the_first_match(): void
	{
		$pulled = [];
		$sequence = $this->loggingSequence($pulled);

		$this->assertTrue($sequence->any(static fn (int $v): bool => $v === 2));
		$this->assertSame([1, 2], $pulled);
	}

	#[Test]
	public function all_stops_pulling_at_the_first_failure(): void
	{
		$pulled = [];
		$sequence = $this->loggingSequence($pulled);

		$this->assertFalse($sequence->all(static fn (int $v): bool => $v < 2));
		$this->assertSame([1, 2], $pulled);
	}

	#[Test]
	public function containsAll_stops_pulling_once_nothing_is_missing(): void
	{
		$pulled = [];
		$sequence = $this->loggingSequence($pulled);

		$this->assertTrue($sequence->containsAll([2, 1]));
		$this->assertSame([1, 2], $pulled);
	}

	#[Test]
	public function count_drains_the_source(): void
	{
		$pulled = [];
		$sequence = $this->loggingSequence($pulled);

		$this->assertSame(4, $sequence->count());
		$this->assertSame([1, 2, 3, 4], $pulled);
	}

	#[Test]
	public function a_querying_terminal_consumes_a_pass_of_a_one_shot_source(): void
	{
		$sequence = sequenceOf((static function (): Generator {
			yield 1;
			yield 2;
		})());

		$this->assertTrue($sequence->contains(1));

		// Even the partial pass contains() stopped short counts as consumed.
		$this->expectException(NonReplayableSourceException::class);

		$sequence->isEmpty();
	}

	#[Test]
	public function querying_replays_over_a_replayable_source(): void
	{
		$sequence = sequenceOf([1, 2, 3]);

		$this->assertSame(3, $sequence->count());
		$this->assertTrue($sequence->contains(1));
		$this->assertSame(3, $sequence->count());
	}

	/**
	 * A one-shot source logging what the terminal pulls out of it.
	 *
	 * @param list<int> $pulled
	 * @return Sequence<int>
	 */
	private function loggingSequence(array &$pulled): Sequence
	{
		return sequenceOf(static function () use (&$pulled): Generator {
			foreach ([1, 2, 3, 4] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		});
	}
}
