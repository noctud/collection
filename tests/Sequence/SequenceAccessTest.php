<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Sequence;

use Generator;
use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\Exception\NonReplayableSourceException;
use Noctud\Collection\Exception\NoSuchElementException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function Noctud\Collection\listOf;
use function Noctud\Collection\sequenceOf;

final class SequenceAccessTest extends TestCase
{
	#[Test]
	public function first_returns_the_first_element(): void
	{
		$this->assertSame(1, sequenceOf([1, 2, 3])->first());
	}

	#[Test]
	public function first_throws_on_an_empty_sequence(): void
	{
		$this->expectException(NoSuchElementException::class);
		$this->expectExceptionMessageIsOrContains('Sequence is empty');

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = sequenceOf([])->first();
	}

	#[Test]
	public function firstOrNull_returns_null_on_an_empty_sequence(): void
	{
		$this->assertSame(1, sequenceOf([1, 2])->firstOrNull());

		// Emptied by a filter rather than empty at the source: the realistic way a pipeline
		// ends up with nothing, and it keeps a real element type instead of never.
		$this->assertNull(sequenceOf([1, 2])->filter(static fn (int $v): bool => $v > 9)->firstOrNull());
	}

	#[Test]
	public function last_returns_the_last_element(): void
	{
		$this->assertSame(3, sequenceOf([1, 2, 3])->last());
	}

	#[Test]
	public function last_throws_on_an_empty_sequence(): void
	{
		$this->expectException(NoSuchElementException::class);
		$this->expectExceptionMessageIsOrContains('Sequence is empty');

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = sequenceOf([])->last();
	}

	#[Test]
	public function lastOrNull_returns_null_on_an_empty_sequence(): void
	{
		$this->assertSame(3, sequenceOf([1, 2, 3])->lastOrNull());
		$this->assertNull(sequenceOf([1, 2])->filter(static fn (int $v): bool => $v > 9)->lastOrNull());
	}

	#[Test]
	public function lastOrNull_returns_a_trailing_null_element(): void
	{
		// A sequence ending on null is indistinguishable from an empty one through this
		// terminal - the same ambiguity Collection::lastOrNull() has, kept deliberately.
		$this->assertNull(sequenceOf([1, null])->lastOrNull());
	}

	#[Test]
	public function single_returns_the_only_element(): void
	{
		$this->assertSame(1, sequenceOf([1])->single());
	}

	#[Test]
	public function single_throws_on_an_empty_sequence(): void
	{
		$this->expectException(NoSuchElementException::class);
		$this->expectExceptionMessageIsOrContains('Sequence is empty');

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = sequenceOf([])->single();
	}

	#[Test]
	public function single_throws_on_more_than_one_element(): void
	{
		$this->expectException(NoSuchElementException::class);
		$this->expectExceptionMessageIsOrContains('Sequence contains more than one element');

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = sequenceOf([1, 2])->single();
	}

	#[Test]
	public function singleOrNull_returns_null_when_empty_or_ambiguous(): void
	{
		$this->assertSame(1, sequenceOf([1])->singleOrNull());
		$this->assertNull(sequenceOf([1, 2])->filter(static fn (int $v): bool => $v > 9)->singleOrNull());
		$this->assertNull(sequenceOf([1, 2])->singleOrNull());
	}

	#[Test]
	public function singleOrNull_propagates_a_NoSuchElementException_thrown_by_a_stage(): void
	{
		// A mapper failing over some other subject is a real error, not "no single element" here.
		$sequence = sequenceOf([1])->map(static function (): int {
			throw new NoSuchElementException('Raised inside the pipeline');
		});

		$this->expectException(NoSuchElementException::class);
		$this->expectExceptionMessageIsOrContains('Raised inside the pipeline');

		$_ = $sequence->singleOrNull(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function elementAt_returns_the_element_at_that_position(): void
	{
		$this->assertSame('b', sequenceOf(['a', 'b', 'c'])->elementAt(1));
		$this->assertSame('a', sequenceOf(['a', 'b', 'c'])->elementAt(0));
	}

	#[Test]
	public function elementAt_throws_past_the_end(): void
	{
		$this->expectException(IndexOutOfBoundsException::class);
		$this->expectExceptionMessageIsOrContains('Index out of bounds: 3');

		$_ = sequenceOf(['a', 'b', 'c'])->elementAt(3); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function elementAt_throws_on_a_negative_index(): void
	{
		$this->expectException(IndexOutOfBoundsException::class);
		$this->expectExceptionMessageIsOrContains('Cannot use a negative index');

		// @phpstan-ignore argument.type
		$_ = sequenceOf(['a', 'b', 'c'])->elementAt(-1); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function elementAtOrNull_returns_null_past_the_end(): void
	{
		$this->assertSame('b', sequenceOf(['a', 'b', 'c'])->elementAtOrNull(1));
		$this->assertNull(sequenceOf(['a', 'b', 'c'])->elementAtOrNull(3));
	}

	#[Test]
	public function elementAtOrNull_throws_on_a_negative_index(): void
	{
		$this->expectException(IndexOutOfBoundsException::class);
		$this->expectExceptionMessageIsOrContains('Cannot use a negative index');

		// @phpstan-ignore argument.type
		$_ = sequenceOf(['a', 'b', 'c'])->elementAtOrNull(-1); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function elementAt_counts_the_positions_of_its_own_stage(): void
	{
		// Indexes are positional per stage, so a filter renumbers what elementAt() counts.
		$sequence = sequenceOf([1, 2, 3, 4])->filter(static fn (int $v): bool => $v % 2 === 0);

		$this->assertSame(4, $sequence->elementAt(1));
	}

	#[Test]
	public function elementAt_stops_pulling_at_that_position(): void
	{
		$pulled = [];
		$sequence = sequenceOf(static function () use (&$pulled): Generator {
			foreach ([1, 2, 3, 4] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		});

		$this->assertSame(2, $sequence->elementAt(1));
		$this->assertSame([1, 2], $pulled);
	}

	#[Test]
	public function find_returns_the_first_match_or_null(): void
	{
		$this->assertSame(2, sequenceOf([1, 2, 3, 4])->find(static fn (int $v): bool => $v % 2 === 0));
		$this->assertNull(sequenceOf([1, 3])->find(static fn (int $v): bool => $v % 2 === 0));
	}

	#[Test]
	public function find_receives_the_positional_index(): void
	{
		$seen = [];
		$found = sequenceOf(['a', 'b', 'c'])
			->filter(static fn (string $v): bool => $v !== 'a')
			->find(static function (string $v, int $i) use (&$seen): bool {
				$seen[] = "$i:$v";

				return $v === 'c';
			});

		// Indexes are the positions of this stage, not of the source: the filter reindexed.
		$this->assertSame('c', $found);
		$this->assertSame(['0:b', '1:c'], $seen);
	}

	#[Test]
	public function expect_returns_the_first_match(): void
	{
		$this->assertSame(2, sequenceOf([1, 2, 3])->expect(static fn (int $v): bool => $v % 2 === 0));
	}

	#[Test]
	public function expect_throws_when_nothing_matches(): void
	{
		$this->expectException(NoSuchElementException::class);
		$this->expectExceptionMessageIsOrContains('No element matching the predicate was found');

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = sequenceOf([1, 3])->expect(static fn (int $v): bool => $v % 2 === 0);
	}

	#[Test]
	public function findLast_returns_the_last_match_or_null(): void
	{
		$this->assertSame(4, sequenceOf([1, 2, 3, 4])->findLast(static fn (int $v): bool => $v % 2 === 0));
		$this->assertNull(sequenceOf([1, 3])->findLast(static fn (int $v): bool => $v % 2 === 0));
	}

	#[Test]
	public function expectLast_returns_the_last_match(): void
	{
		$this->assertSame(4, sequenceOf([1, 2, 3, 4])->expectLast(static fn (int $v): bool => $v % 2 === 0));
	}

	#[Test]
	public function expectLast_throws_when_nothing_matches(): void
	{
		$this->expectException(NoSuchElementException::class);
		$this->expectExceptionMessageIsOrContains('No element matching the predicate was found');

		// phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = sequenceOf([1, 3])->expectLast(static fn (int $v): bool => $v % 2 === 0);
	}

	#[Test]
	public function element_access_matches_its_collection_counterpart(): void
	{
		$data = [3, 1, 4, 1, 5];
		$even = static fn (int $v): bool => $v % 2 === 0;

		$this->assertSame(listOf($data)->first(), sequenceOf($data)->first());
		$this->assertSame(listOf($data)->firstOrNull(), sequenceOf($data)->firstOrNull());
		$this->assertSame(listOf($data)->last(), sequenceOf($data)->last());
		$this->assertSame(listOf($data)->lastOrNull(), sequenceOf($data)->lastOrNull());
		$this->assertSame(listOf($data)->find($even), sequenceOf($data)->find($even));
		$this->assertSame(listOf($data)->expect($even), sequenceOf($data)->expect($even));
		$this->assertSame(listOf([7])->single(), sequenceOf([7])->single());
		$this->assertSame(listOf($data)->singleOrNull(), sequenceOf($data)->singleOrNull());
		$this->assertSame(listOf($data)->findLast($even), sequenceOf($data)->findLast($even));
		$this->assertSame(listOf($data)->expectLast($even), sequenceOf($data)->expectLast($even));
	}

	#[Test]
	public function first_pulls_exactly_one_element(): void
	{
		$pulled = [];
		$sequence = sequenceOf(static function () use (&$pulled): Generator {
			foreach ([1, 2, 3] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		});

		$this->assertSame(1, $sequence->first());
		$this->assertSame([1], $pulled);
	}

	#[Test]
	public function single_pulls_at_most_two_elements(): void
	{
		$pulled = [];
		$sequence = sequenceOf(static function () use (&$pulled): Generator {
			foreach ([1, 2, 3, 4] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		});

		try {
			// A second element existing is already the answer: nothing beyond it is pulled.
            // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
			$_ = $sequence->single();
		} catch (NoSuchElementException) {
			// expected
		}

		$this->assertSame([1, 2], $pulled);
	}

	#[Test]
	public function find_stops_pulling_at_the_first_match(): void
	{
		$pulled = [];
		$sequence = sequenceOf(static function () use (&$pulled): Generator {
			foreach ([1, 2, 3, 4] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		});

		$this->assertSame(2, $sequence->find(static fn (int $v): bool => $v % 2 === 0));
		$this->assertSame([1, 2], $pulled);
	}

	#[Test]
	public function findLast_drains_the_source_where_find_stops_early(): void
	{
		$pulled = [];
		$sequence = sequenceOf(static function () use (&$pulled): Generator {
			foreach ([1, 2, 3, 4] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		});

		// find() returns at its first match; the *last* match is only known at the end.
		$this->assertSame(2, $sequence->findLast(static fn (int $v): bool => $v < 3));
		$this->assertSame([1, 2, 3, 4], $pulled);
	}

	#[Test]
	public function last_drains_the_source(): void
	{
		$pulled = [];
		$sequence = sequenceOf(static function () use (&$pulled): Generator {
			foreach ([1, 2, 3] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		});

		// No array_key_last to lean on: the last element is only knowable at the end.
		$this->assertSame(3, $sequence->last());
		$this->assertSame([1, 2, 3], $pulled);
	}

	#[Test]
	public function a_terminal_consumes_a_pass_of_a_one_shot_source(): void
	{
		$sequence = sequenceOf((static function (): Generator {
			yield 1;
			yield 2;
		})());

		$this->assertSame(1, $sequence->first());

		// Even a partial pass counts as consumed - there is no resuming from the middle.
		$this->expectException(NonReplayableSourceException::class);

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = $sequence->first();
	}

	#[Test]
	public function element_access_replays_over_a_replayable_source(): void
	{
		$sequence = sequenceOf([1, 2, 3]);

		$this->assertSame(1, $sequence->first());
		$this->assertSame(3, $sequence->last());
		$this->assertSame(1, $sequence->first());
	}
}
