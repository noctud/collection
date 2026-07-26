<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Sequence;

use ArrayIterator;
use Exception;
use Generator;
use Noctud\Collection\Exception\InvalidSequenceSourceException;
use Noctud\Collection\Exception\SequenceAlreadyIteratedException;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Sequence\GeneratorSequence;
use Noctud\Collection\Tests\Sequence\Fixture\GeneratorAggregate;
use Noctud\Collection\Tests\Sequence\Fixture\SharedIteratorAggregate;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function Noctud\Collection\listOf;
use function Noctud\Collection\mutableListOf;
use function Noctud\Collection\sequenceOf;

final class SequenceIterationTest extends TestCase
{
	#[Test]
	public function array_source_replays(): void
	{
		$sequence = sequenceOf([1, 2, 3]);

		$this->assertSame([1, 2, 3], $sequence->toArray());
		$this->assertSame([1, 2, 3], $sequence->toArray());
	}

	#[Test]
	public function collection_source_replays(): void
	{
		$sequence = sequenceOf(listOf([1, 2, 3]));

		$this->assertSame([1, 2, 3], $sequence->toArray());
		$this->assertSame([1, 2, 3], $sequence->toArray());
	}

	#[Test]
	public function fresh_closure_source_replays_by_reinvoking_the_producer(): void
	{
		$invocations = 0;
		$sequence = sequenceOf(static function () use (&$invocations): Generator {
			$invocations++;

			yield 1;
			yield 2;
		});

		$this->assertSame([1, 2], $sequence->toArray());
		$this->assertSame([1, 2], $sequence->toArray());
		$this->assertSame(2, $invocations);
	}

	#[Test]
	public function closure_returning_an_array_replays(): void
	{
		$sequence = sequenceOf(static fn (): array => [1, 2]);

		$this->assertSame([1, 2], $sequence->toArray());
		$this->assertSame([1, 2], $sequence->toArray());
	}

	#[Test]
	public function aggregate_source_returning_a_fresh_generator_replays(): void
	{
		$sequence = sequenceOf(new GeneratorAggregate([1, 2, 3]));

		$this->assertSame([1, 2, 3], $sequence->toArray());
		$this->assertSame([1, 2, 3], $sequence->toArray());
	}

	#[Test]
	public function aggregate_source_holding_on_to_its_iterator_throws_on_second_pass(): void
	{
		$generator = (static function (): Generator {
			yield 1;
		})();
		$sequence = sequenceOf(new SharedIteratorAggregate($generator));

		$this->assertSame([1], $sequence->toArray());

		$this->expectException(SequenceAlreadyIteratedException::class);
		$this->expectExceptionMessageIsOrContains(
			'The sequence\'s source returned the same iterator instance again - a source closure or an IteratorAggregate must produce a fresh iterator on each pass.',
		);

		$sequence->toArray();
	}

	#[Test]
	public function aggregate_source_delegating_to_an_inner_collection_replays(): void
	{
		// The same inner aggregate every pass is legitimate: it is itself a producer,
		// re-invoked by the foreach that unwraps it.
		$sequence = sequenceOf(new SharedIteratorAggregate(listOf([1, 2, 3])));

		$this->assertSame([1, 2, 3], $sequence->toArray());
		$this->assertSame([1, 2, 3], $sequence->toArray());
	}

	#[Test]
	public function closure_returning_the_same_collection_replays(): void
	{
		$list = listOf([1, 2, 3]);
		$sequence = sequenceOf(static fn (): ImmutableList => $list);

		$this->assertSame([1, 2, 3], $sequence->toArray());
		$this->assertSame([1, 2, 3], $sequence->toArray());
	}

	#[Test]
	public function replay_sees_live_collection_mutations(): void
	{
		$list = mutableListOf([1, 2]);
		$sequence = sequenceOf($list);

		$this->assertSame([1, 2], $sequence->toArray());

		$list->add(3);

		$this->assertSame([1, 2, 3], $sequence->toArray());
	}

	#[Test]
	public function raw_generator_source_throws_on_second_pass(): void
	{
		$generator = (static function (): Generator {
			yield 1;
		})();
		$sequence = sequenceOf($generator);

		$sequence->toArray();

		$this->expectException(SequenceAlreadyIteratedException::class);
		$this->expectExceptionMessageIsOrContains(
			'This sequence is backed by a non-replayable source and has already been iterated. Create a new sequence from a fresh source to iterate again.',
		);

		$sequence->toArray();
	}

	#[Test]
	public function raw_iterator_source_is_one_shot_even_if_rewindable(): void
	{
		$sequence = sequenceOf(new ArrayIterator([1, 2]));

		$this->assertSame([1, 2], $sequence->toArray());

		$this->expectException(SequenceAlreadyIteratedException::class);

		$sequence->toArray();
	}

	#[Test]
	public function closure_returning_the_same_generator_throws_on_second_pass(): void
	{
		$generator = (static function (): Generator {
			yield 1;
		})();
		// By-ref capture keeps this a regular closure: an arrow fn whose body evaluates
		// to a Generator trips phpstan's return.void check (generator-function confusion).
		$sequence = sequenceOf(static function () use (&$generator): Generator {
			return $generator;
		});

		$sequence->toArray();

		$this->expectException(SequenceAlreadyIteratedException::class);
		$this->expectExceptionMessageIsOrContains(
			'The sequence\'s source returned the same iterator instance again - a source closure or an IteratorAggregate must produce a fresh iterator on each pass.',
		);

		$sequence->toArray();
	}

	#[Test]
	public function producer_cycling_between_iterators_slips_past_the_guard_and_leaks_the_raw_php_error(): void
	{
		$first = (static function (): Generator {
			yield 1;
		})();
		$second = (static function (): Generator {
			yield 2;
		})();
		$n = 0;
		$sequence = sequenceOf(static function () use ($first, $second, &$n): Generator {
			return $n++ % 2 === 0 ? $first : $second;
		});

		$this->assertSame([1], $sequence->toArray());
		$this->assertSame([2], $sequence->toArray());

		$this->expectException(Exception::class);
		$this->expectExceptionMessageIsOrContains('Cannot traverse an already closed generator');

		$sequence->toArray();
	}

	#[Test]
	public function closure_returning_a_non_iterable_throws_at_consumption(): void
	{
		/** @phpstan-ignore argument.type, argument.templateType */
		$sequence = sequenceOf(static fn (): int => 42);

		$this->expectException(InvalidSequenceSourceException::class);
		$this->expectExceptionMessageIsOrContains('The sequence\'s source closure must return an iterable, got int.');

		$sequence->toArray();
	}

	#[Test]
	public function toSet_consumes_a_pass_of_a_one_shot_source(): void
	{
		$generator = (static function (): Generator {
			yield 1;
			yield 2;
			yield 1;
		})();
		$sequence = sequenceOf($generator);

		$this->assertSame([1, 2], $sequence->toSet()->toArray());

		$this->expectException(SequenceAlreadyIteratedException::class);

		$sequence->toArray();
	}

	#[Test]
	public function partially_consumed_one_shot_source_throws_on_second_pass(): void
	{
		$generator = (static function (): Generator {
			yield 1;
			yield 2;
			yield 3;
		})();
		$sequence = sequenceOf($generator);

		$first = null;
		foreach ($sequence as $value) {
			$first = $value;

			break;
		}

		$this->assertSame(1, $first);

		$this->expectException(SequenceAlreadyIteratedException::class);

		$sequence->toArray();
	}

	#[Test]
	public function second_pass_throws_at_getIterator_not_at_first_advance(): void
	{
		$generator = (static function (): Generator {
			yield 1;
		})();
		$sequence = new GeneratorSequence($generator);

		$sequence->getIterator();

		$this->expectException(SequenceAlreadyIteratedException::class);

		$sequence->getIterator();
	}
}
