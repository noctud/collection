<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Sequence;

use Generator;
use Noctud\Collection\Exception\SequenceAlreadyIteratedException;
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

		$this->expectException(SequenceAlreadyIteratedException::class);

		$sequence->toArray();
	}
}
