<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Sequence;

use Generator;
use Noctud\Collection\Exception\NonReplayableSourceException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function Noctud\Collection\sequenceOf;

final class SequenceLazinessTest extends TestCase
{
	#[Test]
	public function nothing_runs_before_terminal(): void
	{
		$invocations = 0;
		$predicateCalls = 0;
		$transformCalls = 0;

		$sequence = sequenceOf(static function () use (&$invocations): array {
			$invocations++;

			return [1, 2, 3];
		})
			->filter(static function (int $v) use (&$predicateCalls): bool {
				$predicateCalls++;

				return $v > 1;
			})
			->map(static function (int $v) use (&$transformCalls): int {
				$transformCalls++;

				return $v * 10;
			});

		$this->assertSame(0, $invocations);
		$this->assertSame(0, $predicateCalls);
		$this->assertSame(0, $transformCalls);

		$this->assertSame([20, 30], $sequence->toArray());

		$this->assertSame(1, $invocations);
		$this->assertSame(3, $predicateCalls);
		$this->assertSame(2, $transformCalls);
	}

	#[Test]
	public function filter_map_fusion_processes_elements_one_by_one(): void
	{
		$log = [];

		$result = sequenceOf([1, 2, 3, 4, 5, 6])
			->filter(static function (int $v) use (&$log): bool {
				$log[] = "f:$v";

				return $v % 2 === 0;
			})
			->map(static function (int $v) use (&$log): int {
				$log[] = "m:$v";

				return $v * 10;
			})
			->toArray();

		$this->assertSame([20, 40, 60], $result);
		$this->assertSame(
			['f:1', 'f:2', 'm:2', 'f:3', 'f:4', 'm:4', 'f:5', 'f:6', 'm:6'],
			$log,
		);
	}

	#[Test]
	public function onEach_is_lazy_and_observes_the_values_of_its_own_stage(): void
	{
		$log = [];
		$sequence = sequenceOf([1, 2, 3])
			->onEach(static function (int $v) use (&$log): void {
				$log[] = "a:$v";
			})
			->map(static fn (int $v): int => $v * 10)
			->onEach(static function (int $v) use (&$log): void {
				$log[] = "b:$v";
			});

		$this->assertSame([], $log);

		$this->assertSame([10, 20, 30], $sequence->toArray());
		$this->assertSame(['a:1', 'b:10', 'a:2', 'b:20', 'a:3', 'b:30'], $log);
	}

	#[Test]
	public function forEach_runs_the_action_once_per_element(): void
	{
		$seen = [];
		sequenceOf(['a', 'b', 'c'])->forEach(static function (string $v, int $i) use (&$seen): void {
			$seen[] = "$i:$v";
		});

		$this->assertSame(['0:a', '1:b', '2:c'], $seen);
	}

	#[Test]
	public function forEach_is_the_terminal_that_makes_a_lazy_pipeline_run(): void
	{
		$mapped = [];
		$sequence = sequenceOf([1, 2, 3])->map(static function (int $v) use (&$mapped): int {
			$mapped[] = $v;

			return $v * 2;
		});

		// Building the chain runs nothing - onEach would have left it just as cold.
		$this->assertSame([], $mapped);

		$doubled = [];
		$sequence->forEach(static function (int $v) use (&$doubled): void {
			$doubled[] = $v;
		});

		$this->assertSame([1, 2, 3], $mapped);
		$this->assertSame([2, 4, 6], $doubled);
	}

	#[Test]
	public function forEach_receives_the_positions_of_its_own_stage(): void
	{
		$seen = [];
		sequenceOf(['a', 'b', 'c'])
			->filter(static fn (string $v): bool => $v !== 'a')
			->forEach(static function (string $v, int $i) use (&$seen): void {
				$seen[] = "$i:$v";
			});

		// The filter reindexed: the positions are this stage's, not the source's.
		$this->assertSame(['0:b', '1:c'], $seen);
	}

	#[Test]
	public function takeFirst_pulls_exactly_n_elements_from_the_source(): void
	{
		$pulled = [];
		$sequence = sequenceOf(static function () use (&$pulled): Generator {
			foreach ([1, 2, 3, 4, 5] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		});

		$this->assertSame([1, 2], $sequence->takeFirst(2)->toArray());
		$this->assertSame([1, 2], $pulled);
	}

	#[Test]
	public function takeFirst_only_maps_what_it_takes(): void
	{
		$transformCalls = 0;

		$result = sequenceOf([1, 2, 3, 4, 5])
			->map(static function (int $v) use (&$transformCalls): int {
				$transformCalls++;

				return $v * 10;
			})
			->takeFirst(2)
			->toArray();

		$this->assertSame([10, 20], $result);
		$this->assertSame(2, $transformCalls);
	}

	#[Test]
	public function takeWhile_stops_pulling_at_the_first_failing_element(): void
	{
		$pulled = [];
		$sequence = sequenceOf(static function () use (&$pulled): Generator {
			foreach ([1, 2, 3, 4, 5] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		});

		$this->assertSame([1, 2], $sequence->takeWhile(static fn (int $v): bool => $v < 3)->toArray());

		// 3 is pulled and tested, then nothing further: the predicate has to see the element
		// that ends the run.
		$this->assertSame([1, 2, 3], $pulled);
	}

	#[Test]
	public function zip_pulls_the_other_side_lazily(): void
	{
		$pulled = [];
		$other = (static function () use (&$pulled): Generator {
			foreach (['a', 'b', 'c', 'd'] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		})();

		$this->assertSame([[1, 'a'], [2, 'b']], sequenceOf([1, 2])->zip($other)->toArray());

		// Never buffered, and not pulled once past the shorter side either.
		$this->assertSame(['a', 'b'], $pulled);
	}

	#[Test]
	public function zip_does_not_pull_the_other_side_when_this_one_is_empty(): void
	{
		$pulled = [];
		$other = (static function () use (&$pulled): Generator {
			foreach (['a', 'b'] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		})();

		$this->assertSame([], sequenceOf([])->zip($other)->toArray());

		// The other side is only positioned once this one is known to hold an element, so an
		// empty side spares it even the single pull that positioning costs.
		$this->assertSame([], $pulled);
	}

	#[Test]
	public function chained_pipeline_replays_through_replayable_root(): void
	{
		$sequence = sequenceOf([1, 2, 3])
			->filter(static fn (int $v): bool => $v > 1)
			->map(static fn (int $v): int => $v * 10);

		$this->assertSame([20, 30], $sequence->toArray());
		$this->assertSame([20, 30], $sequence->toArray());
	}

	#[Test]
	public function chained_pipeline_throws_through_one_shot_root(): void
	{
		$generator = (static function (): Generator {
			yield 1;
			yield 2;
		})();
		$sequence = sequenceOf($generator)
			->filter(static fn (int $v): bool => $v > 0)
			->map(static fn (int $v): int => $v * 10);

		$this->assertSame([10, 20], $sequence->toArray());

		$this->expectException(NonReplayableSourceException::class);

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$_ = $sequence->toArray();
	}
}
