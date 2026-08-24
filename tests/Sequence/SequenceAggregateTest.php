<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Sequence;

use Generator;
use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Exception\NonReplayableSourceException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Exception\UnsupportedOperationException;
use Noctud\Collection\Sequence\Sequence;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use function Noctud\Collection\listOf;
use function Noctud\Collection\sequenceOf;

final class SequenceAggregateTest extends TestCase
{
	#[Test]
	public function fold_accumulates_from_the_initial_value(): void
	{
		$this->assertSame(10, sequenceOf([1, 2, 3, 4])->fold(0, static fn (int $acc, int $v): int => $acc + $v));
		$this->assertSame('start', sequenceOf([])->fold('start', static fn (string $acc): string => $acc . '!'));
	}

	#[Test]
	public function reduce_folds_from_the_first_element(): void
	{
		$this->assertSame(24, sequenceOf([1, 2, 3, 4])->reduce(static fn (int $a, int $b): int => $a * $b));
	}

	#[Test]
	public function reduce_throws_on_an_empty_sequence(): void
	{
		$this->expectException(UnsupportedOperationException::class);
		$this->expectExceptionMessageIsOrContains('Cannot reduce empty sequence');

		// phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = sequenceOf([])->reduce(static fn (int $a, int $b): int => $a + $b);
	}

	#[Test]
	public function reduceOrNull_returns_null_on_an_empty_sequence(): void
	{
		$this->assertSame(10, sequenceOf([1, 2, 3, 4])->reduceOrNull(static fn (int $a, int $b): int => $a + $b));

		// Emptied by a filter rather than empty at the source: the realistic way a pipeline ends up
		// with nothing, and it keeps a real element type instead of never.
		$this->assertNull($this->emptied()->reduceOrNull(static fn (int $a, int $b): int => $a + $b));
	}

	#[Test]
	public function reduceOrNull_propagates_an_UnsupportedOperationException_thrown_by_the_operation(): void
	{
		// The operation is user code: the exception it raises is a real error, not this method's
		// answer for an empty sequence.
		$this->expectException(UnsupportedOperationException::class);
		$this->expectExceptionMessageIsOrContains('from the operation');

		// phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = sequenceOf([1, 2])->reduceOrNull(static function (): int {
			throw new UnsupportedOperationException('from the operation');
		});
	}

	#[Test]
	public function sum_adds_elements_or_selector_values(): void
	{
		$this->assertSame(6, sequenceOf([1, 2, 3])->sum());
		$this->assertSame(0, sequenceOf([])->sum());
		$this->assertSame(12, sequenceOf([1, 2, 3])->sum(static fn (int $v): int => $v * 2));
		$this->assertEqualsWithDelta(4.5, sequenceOf([1.5, 3.0])->sum(), 0.0001);
	}

	#[Test]
	public function avg_and_avgOrNull_average_the_sequence(): void
	{
		$this->assertEqualsWithDelta(2.5, sequenceOf([1, 2, 3, 4])->avg(), 0.0001);
		$this->assertEqualsWithDelta(5.0, sequenceOf([1, 2, 3, 4])->avg(static fn (int $v): int => $v * 2), 0.0001);
		$this->assertEqualsWithDelta(2.5, sequenceOf([1, 2, 3, 4])->avgOrNull(), 0.0001);
		$this->assertNull(sequenceOf([])->avgOrNull());
	}

	#[Test]
	public function avg_throws_on_an_empty_sequence(): void
	{
		// The noun is derived from the subject, so a sequence is not told a "collection" is empty.
		$this->expectException(UnsupportedOperationException::class);
		$this->expectExceptionMessageIsOrContains('Cannot compute average of empty sequence');

		// phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = sequenceOf([])->avg();
	}

	#[Test]
	public function min_and_max_return_the_extreme_element(): void
	{
		$data = [3, 1, 4, 1, 5];

		$this->assertSame(1, sequenceOf($data)->min());
		$this->assertSame(5, sequenceOf($data)->max());
		$this->assertSame(1, sequenceOf($data)->minOrNull());
		$this->assertSame(5, sequenceOf($data)->maxOrNull());
	}

	#[Test]
	public function min_and_max_use_the_selector_to_pick_the_element(): void
	{
		$words = ['bbb', 'a', 'cc'];
		$length = static fn (string $v): int => strlen($v);

		// The element comes back, not its selector value - that is what minOf is for.
		$this->assertSame('a', sequenceOf($words)->min($length));
		$this->assertSame('bbb', sequenceOf($words)->max($length));
		$this->assertSame(1, sequenceOf($words)->minOf($length));
		$this->assertSame(3, sequenceOf($words)->maxOf($length));
	}

	#[Test]
	public function the_extremes_throw_on_an_empty_sequence(): void
	{
		foreach (['min', 'max'] as $method) {
			try {
				$this->emptied()->{$method}();
				$this->fail("{$method}() should have thrown");
			} catch (NoSuchElementException $e) {
				$this->assertStringContainsString('Sequence is empty', $e->getMessage());
			}
		}

		foreach (['minOf', 'maxOf'] as $method) {
			try {
				$this->emptied()->{$method}(static fn (int $v): int => $v);
				$this->fail("{$method}() should have thrown");
			} catch (NoSuchElementException $e) {
				$this->assertStringContainsString('Sequence is empty', $e->getMessage());
			}
		}
	}

	#[Test]
	public function the_OrNull_extremes_return_null_on_an_empty_sequence(): void
	{
		$this->assertNull($this->emptied()->minOrNull());
		$this->assertNull($this->emptied()->maxOrNull());
		$this->assertNull($this->emptied()->minOfOrNull(static fn (int $v): int => $v));
		$this->assertNull($this->emptied()->maxOfOrNull(static fn (int $v): int => $v));
	}

	#[Test]
	public function the_OrNull_extremes_propagate_a_NoSuchElementException_thrown_by_the_selector(): void
	{
		// A selector reaching into an empty collection of its own raises this; swallowing it would
		// report the sequence as empty when it is not.
		$this->expectException(NoSuchElementException::class);
		$this->expectExceptionMessageIsOrContains('from the selector');

		// phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = sequenceOf([1, 2])->minOrNull(static function (): int {
			throw new NoSuchElementException('from the selector');
		});
	}

	#[Test]
	public function joinToString_joins_with_separator_prefix_and_postfix(): void
	{
		$this->assertSame('1, 2, 3', sequenceOf([1, 2, 3])->joinToString());
		$this->assertSame('[1-2-3]', sequenceOf([1, 2, 3])->joinToString('-', '[', ']'));
		$this->assertSame('', sequenceOf([])->joinToString());
	}

	#[Test]
	public function joinToString_applies_the_transform_and_the_limit(): void
	{
		$this->assertSame('a1, b2', sequenceOf(['a', 'b'])->joinToString(transform: static fn (string $v, int $i): string => $v . ($i + 1)));
		$this->assertSame('1, 2, …', sequenceOf([1, 2, 3, 4])->joinToString(limit: 2, truncated: '…'));
	}

	#[Test]
	public function joinToString_throws_on_an_unconvertible_element(): void
	{
		$this->expectException(ConversionException::class);

		// phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = sequenceOf([new stdClass()])->joinToString();
	}

	#[Test]
	public function aggregation_matches_its_collection_counterpart(): void
	{
		$data = [3, 1, 4, 1, 5];
		$double = static fn (int $v): int => $v * 2;

		$this->assertSame(listOf($data)->fold(0, static fn (int $a, int $b): int => $a + $b), sequenceOf($data)->fold(0, static fn (int $a, int $b): int => $a + $b));
		$this->assertSame(listOf($data)->reduce(static fn (int $a, int $b): int => $a + $b), sequenceOf($data)->reduce(static fn (int $a, int $b): int => $a + $b));
		$this->assertSame(listOf($data)->sum(), sequenceOf($data)->sum());
		$this->assertSame(listOf($data)->sum($double), sequenceOf($data)->sum($double));
		$this->assertSame(listOf($data)->avg(), sequenceOf($data)->avg());
		$this->assertSame(listOf($data)->min(), sequenceOf($data)->min());
		$this->assertSame(listOf($data)->max(), sequenceOf($data)->max());
		$this->assertSame(listOf($data)->minOf($double), sequenceOf($data)->minOf($double));
		$this->assertSame(listOf($data)->maxOf($double), sequenceOf($data)->maxOf($double));
		$this->assertSame(listOf($data)->joinToString('|'), sequenceOf($data)->joinToString('|'));
	}

	#[Test]
	public function joinToString_stops_pulling_at_the_limit(): void
	{
		$pulled = [];
		$sequence = $this->loggingSequence($pulled);

		$this->assertSame('1, 2, ...', $sequence->joinToString(limit: 2));

		// The third element is what proves the limit was reached; nothing beyond it is pulled.
		$this->assertSame([1, 2, 3], $pulled);
	}

	#[Test]
	public function the_other_aggregations_drain_the_source(): void
	{
		foreach (['sum', 'min', 'max'] as $method) {
			$pulled = [];
			$sequence = $this->loggingSequence($pulled);

			$sequence->{$method}();

			$this->assertSame([1, 2, 3, 4], $pulled, "{$method}() should drain");
		}
	}

	#[Test]
	public function an_aggregation_consumes_a_pass_of_a_one_shot_source(): void
	{
		$sequence = sequenceOf((static function (): Generator {
			yield 1;
			yield 2;
		})());

		$this->assertSame(3, $sequence->sum());

		$this->expectException(NonReplayableSourceException::class);

		$sequence->max();
	}

	#[Test]
	public function aggregation_replays_over_a_replayable_source(): void
	{
		$sequence = sequenceOf([1, 2, 3]);

		$this->assertSame(6, $sequence->sum());
		$this->assertSame(3, $sequence->max());
		$this->assertSame(6, $sequence->sum());
	}

	/**
	 * A sequence emptied by a filter rather than at the source, which keeps its element type int
	 * instead of never - so that a terminal's result stays genuinely uncertain to the analyser.
	 *
	 * @return Sequence<int>
	 */
	private function emptied(): Sequence
	{
		return sequenceOf([1, 2])->filter(static fn (int $v): bool => $v > 9);
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
