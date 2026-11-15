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
use Noctud\Collection\Exception\NonReplayableSourceException;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Sequence\GeneratorSequence;
use Noctud\Collection\Tests\Sequence\Fixture\GeneratorAggregate;
use Noctud\Collection\Tests\Sequence\Fixture\SharedIteratorAggregate;
use NoRewindIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SplStack;
use WeakReference;
use function Noctud\Collection\listOf;
use function Noctud\Collection\mutableListOf;
use function Noctud\Collection\sequenceOf;
use function gc_collect_cycles;

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

        $this->expectException(NonReplayableSourceException::class);
        $this->expectExceptionMessageIsOrContains(
            'The sequence\'s source returned the same iterator instance again - a source closure or an IteratorAggregate must produce a fresh iterator on each pass.',
        );

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
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
    public function a_consumed_iterator_is_not_retained_by_the_sequence(): void
    {
        $weak = null;
        $sequence = sequenceOf(static function () use (&$weak): Generator {
            $generator = (static function (): Generator {
                yield 1;
            })();
            $weak = WeakReference::create($generator);

            return $generator;
        });

        $this->assertSame([1], $sequence->toArray());

        gc_collect_cycles();

        // The identity guard holds the previous pass's cursor weakly, so nothing keeps a
        // consumed iterator - nor the handle or cursor behind it - alive.
        $this->assertNull($weak?->get());
        $this->assertSame([1], $sequence->toArray());
    }

    #[Test]
    public function replayed_pass_starts_from_fresh_intermediate_state(): void
    {
        // distinct's seen-set, dropWhile's dropping flag, dropFirst's counter and
        // zipWithNext's previous element are all built inside the factory closure, so the
        // second pass rebuilds them instead of resuming where the first one stopped.
        $sequence = sequenceOf([1, 1, 2, 3, 3, 4])
            ->distinct()
            ->dropWhile(static fn (int $v): bool => $v < 2)
            ->dropFirst()
            ->zipWithNext();

        $this->assertSame([[3, 4]], $sequence->toArray());
        $this->assertSame([[3, 4]], $sequence->toArray());
    }

    #[Test]
    public function replayed_pass_restarts_the_positional_counters(): void
    {
        $taking = sequenceOf(['a', 'b', 'c'])->takeWhile(static fn (string $v, int $i): bool => $i < 2);
        $dropping = sequenceOf(['a', 'b', 'c'])->dropWhile(static fn (string $v, int $i): bool => $i < 2);

        $this->assertSame(['a', 'b'], $taking->toArray());
        $this->assertSame(['a', 'b'], $taking->toArray());

        $this->assertSame(['c'], $dropping->toArray());
        $this->assertSame(['c'], $dropping->toArray());
    }

    #[Test]
    public function zip_replays_when_both_sides_are_replayable(): void
    {
        $sequence = sequenceOf([1, 2, 3])->zip(['a', 'b']);

        $this->assertSame([[1, 'a'], [2, 'b']], $sequence->toArray());
        $this->assertSame([[1, 'a'], [2, 'b']], $sequence->toArray());
    }

    #[Test]
    public function zip_replays_over_an_iterator_the_lockstep_rewinds(): void
    {
        // Replayability is read off what cursor() does: these are rewound, so a second pass
        // starts them over. Classifying every raw Iterator as one-shot used to refuse this.
        $sequence = sequenceOf([1, 2])->zip(new ArrayIterator(['a', 'b']));

        $this->assertSame([[1, 'a'], [2, 'b']], $sequence->toArray());
        $this->assertSame([[1, 'a'], [2, 'b']], $sequence->toArray());

        $stack = new SplStack();
        $stack->push('a');
        $stack->push('b');
        $overStack = sequenceOf([1, 2])->zip($stack);

        $this->assertSame([[1, 'b'], [2, 'a']], $overStack->toArray());
        $this->assertSame([[1, 'b'], [2, 'a']], $overStack->toArray());
    }

    #[Test]
    public function zip_against_a_no_rewind_iterator_throws_on_second_pass(): void
    {
        $sequence = sequenceOf([1, 2])->zip(new NoRewindIterator(new ArrayIterator(['a', 'b', 'c', 'd'])));

        $this->assertSame([[1, 'a'], [2, 'b']], $sequence->toArray());

        // It swallows the rewind, so a second pass would resume at 'c' rather than start over.
        $this->expectException(NonReplayableSourceException::class);

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function zip_reports_an_other_side_that_cannot_be_rewound(): void
    {
        $started = (static function (): Generator {
            yield 'a';
            yield 'b';
        })();
        $started->current();
        $started->next();

        // An aggregate handing back a cursor already in flight: the rewind fails, and PHP's own
        // "Cannot rewind a generator that was already run" is relabelled rather than leaked.
        try {
            $_ = sequenceOf([1, 2])->zip(new SharedIteratorAggregate($started))->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
            $this->fail('Expected the failed rewind to be reported.');
        } catch (NonReplayableSourceException $e) {
            $this->assertStringContainsString('could not be rewound', $e->getMessage());
            $this->assertInstanceOf(Exception::class, $e->getPrevious());
        }
    }

    #[Test]
    public function zip_replays_when_a_pass_paired_nothing(): void
    {
        $other = (static function (): Generator {
            yield 'a';
            yield 'b';
        })();
        $sequence = sequenceOf([])->zip($other);

        $this->assertSame([], $sequence->toArray());

        // Pairing nothing left the other side where it was, so the one-shot guard has nothing
        // to complain about: it counts a consumed cursor, not an attempted pass.
        $this->assertSame([], $sequence->toArray());
        $this->assertSame('a', $other->current());
    }

    #[Test]
    public function zip_replays_when_this_side_was_emptied_by_a_previous_stage(): void
    {
        $other = (static function (): Generator {
            yield 'a';
            yield 'b';
        })();
        $sequence = sequenceOf([1, 2])->takeFirst(0)->zip($other);

        $this->assertSame([], $sequence->toArray());
        $this->assertSame([], $sequence->toArray());
        $this->assertSame('a', $other->current());
    }

    #[Test]
    public function zip_against_a_raw_iterator_throws_on_second_pass(): void
    {
        $other = (static function (): Generator {
            yield 'a';
            yield 'b';
            yield 'c';
        })();
        $sequence = sequenceOf([1, 2])->zip($other);

        $this->assertSame([[1, 'a'], [2, 'b']], $sequence->toArray());

        // Without the guard the other side would resume where it stopped and the second pass
        // would silently pair 1 with 'c' - the failure mode the contract exists to prevent.
        // The message names the zipped iterable, not this sequence, which is replayable here.
        $this->expectException(NonReplayableSourceException::class);
        $this->expectExceptionMessageIsOrContains(
            'The iterable passed to zip() is a non-replayable cursor and has already been consumed. Zip an array or an IteratorAggregate to iterate the result more than once.',
        );

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function zip_against_a_one_shot_sequence_throws_on_second_pass(): void
    {
        $generator = (static function (): Generator {
            yield 'a';
            yield 'b';
        })();
        $sequence = sequenceOf([1, 2])->zip(sequenceOf($generator));

        $this->assertSame([[1, 'a'], [2, 'b']], $sequence->toArray());

        // The other side is a producer here, so its own guard is the one that fires.
        $this->expectException(NonReplayableSourceException::class);

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function zip_resumes_an_already_started_generator(): void
    {
        $other = (static function (): Generator {
            yield 'a';
            yield 'b';
            yield 'c';
        })();
        $other->current();
        $other->next();

        // A Generator is never rewound: it is always positioned, so valid() === false means
        // exhausted and not "not started". Consuming the head of a stream and zipping the rest
        // is therefore supported, as it is in Kotlin.
        $this->assertSame([[1, 'b'], [2, 'c']], sequenceOf([1, 2])->zip($other)->toArray());
    }

    #[Test]
    public function zip_restarts_an_already_advanced_iterator(): void
    {
        $other = new ArrayIterator(['a', 'b', 'c']);
        $other->next();

        // The flip side of rewinding everything that is not a Generator: an advanced cursor
        // cannot be told apart from a fresh one, so it restarts rather than resuming.
        $this->assertSame([[1, 'a'], [2, 'b']], sequenceOf([1, 2])->zip($other)->toArray());
    }

    #[Test]
    public function zip_resumes_an_advanced_iterator_wrapped_in_a_no_rewind_iterator(): void
    {
        $other = new ArrayIterator(['a', 'b', 'c']);
        $other->next();

        // NoRewindIterator::rewind() is a no-op, which is how a caller who does mean to resume
        // a non-Generator cursor says so: only they can know that it is mid-stream.
        $sequence = sequenceOf([1, 2])->zip(new NoRewindIterator($other));

        $this->assertSame([[1, 'b'], [2, 'c']], $sequence->toArray());
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

        self::assertSame([1], $sequence->toArray());

        $this->expectException(NonReplayableSourceException::class);
        $this->expectExceptionMessageIsOrContains(
            'This sequence is backed by a non-replayable source and has already been iterated. Create a new sequence from a fresh source to iterate again.',
        );

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function raw_iterator_source_is_one_shot_even_if_rewindable(): void
    {
        $sequence = sequenceOf(new ArrayIterator([1, 2]));

        $this->assertSame([1, 2], $sequence->toArray());

        $this->expectException(NonReplayableSourceException::class);

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
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

        $this->assertSame([1], $sequence->toArray());

        $this->expectException(NonReplayableSourceException::class);
        $this->expectExceptionMessageIsOrContains(
            'The sequence\'s source returned the same iterator instance again - a source closure or an IteratorAggregate must produce a fresh iterator on each pass.',
        );

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function closure_returning_an_aggregate_holding_on_to_its_iterator_throws_on_second_pass(): void
    {
        $generator = (static function (): Generator {
            yield 1;
        })();
        $aggregate = new SharedIteratorAggregate($generator);
        $sequence = sequenceOf(static fn (): SharedIteratorAggregate => $aggregate);

        $this->assertSame([1], $sequence->toArray());

        $this->expectException(NonReplayableSourceException::class);
        $this->expectExceptionMessageIsOrContains(
            'The sequence\'s source returned the same iterator instance again - a source closure or an IteratorAggregate must produce a fresh iterator on each pass.',
        );

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
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

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function closure_returning_a_non_iterable_throws_at_consumption(): void
    {
        /** @phpstan-ignore argument.type, argument.templateType */
        $sequence = sequenceOf(static fn (): int => 42);

        $this->expectException(InvalidSequenceSourceException::class);
        $this->expectExceptionMessageIsOrContains('The sequence\'s source closure must return an iterable, got int.');

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
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

        $this->expectException(NonReplayableSourceException::class);

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
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

        $this->expectException(NonReplayableSourceException::class);

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function constrained_once_array_source_throws_on_second_pass(): void
    {
        $sequence = sequenceOf([1, 2, 3], constrainOnce: true);

        $this->assertSame([1, 2, 3], $sequence->toArray());

        $this->expectException(NonReplayableSourceException::class);
        $this->expectExceptionMessageIsOrContains(
            'This sequence was constrained to a single pass and has already been iterated. Create a new sequence to iterate again.',
        );

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function constrained_once_collection_source_throws_on_second_pass(): void
    {
        $sequence = sequenceOf(listOf([1, 2, 3]), constrainOnce: true);

        $this->assertSame([1, 2, 3], $sequence->toArray());

        $this->expectException(NonReplayableSourceException::class);

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function constrained_once_closure_source_is_never_reinvoked(): void
    {
        $invocations = 0;
        $sequence = sequenceOf(
            static function () use (&$invocations): array {
                $invocations++;

                return [1, 2];
            },
            constrainOnce: true,
        );

        $this->assertSame([1, 2], $sequence->toArray());
        $this->assertSame(1, $invocations);

        try {
            $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
            $this->fail('The second pass should have thrown.');
        } catch (NonReplayableSourceException) {
            $this->assertSame(1, $invocations);
        }
    }

    #[Test]
    public function constrained_once_throws_through_a_chained_operation(): void
    {
        $sequence = sequenceOf([1, 2, 3], constrainOnce: true)->map(static fn (int $value): int => $value * 2);

        $this->assertSame([2, 4, 6], $sequence->toArray());

        $this->expectException(NonReplayableSourceException::class);

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function constrained_once_throws_at_getIterator_not_at_first_advance(): void
    {
        $sequence = sequenceOf([1, 2, 3], constrainOnce: true);

        $sequence->getIterator();

        $this->expectException(NonReplayableSourceException::class);

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function second_pass_throws_at_getIterator_not_at_first_advance(): void
    {
        $generator = (static function (): Generator {
            yield 1;
        })();
        $sequence = new GeneratorSequence($generator);

        $sequence->getIterator();

        $this->expectException(NonReplayableSourceException::class);

        $_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }
}
