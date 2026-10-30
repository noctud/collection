<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Sequence;

use Generator;
use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\Exception\NonReplayableSourceException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Exception\UnsupportedOperationException;
use Noctud\Collection\Collection;
use Noctud\Collection\Sequence\Sequence;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use function Noctud\Collection\listOf;
use function Noctud\Collection\sequenceOf;
use function strlen;
use function strtoupper;

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
        $selector = static function (): int {
            throw new NoSuchElementException('from the selector');
        };

        foreach (['minOrNull', 'maxOrNull', 'minOfOrNull', 'maxOfOrNull'] as $method) {
            try {
                sequenceOf([1, 2])->{$method}($selector);
                $this->fail("{$method}() should have propagated the exception");
            } catch (NoSuchElementException $e) {
                $this->assertStringContainsString('from the selector', $e->getMessage());
            }
        }
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
        $add = static fn (int $a, int $b): int => $a + $b;
        $double = static fn (int $v): int => $v * 2;

        $aggregations = [
            'fold' => [0, $add],
            'reduce' => [$add],
            'reduceOrNull' => [$add],
            'sum' => [],
            'avg' => [],
            'avgOrNull' => [],
            'min' => [],
            'max' => [],
            'minOrNull' => [],
            'maxOrNull' => [],
            'minOf' => [$double],
            'maxOf' => [$double],
            'minOfOrNull' => [$double],
            'maxOfOrNull' => [$double],
            'joinToString' => [],
        ];

        foreach ($aggregations as $method => $arguments) {
            $pulled = [];
            $this->loggingSequence($pulled)->{$method}(...$arguments);

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

    #[Test]
    public function groupBy_collects_elements_into_lists_per_key(): void
    {
        $groups = sequenceOf(['a', 'bb', 'cc', 'd'])->groupBy(static fn (string $v): int => strlen($v));

        $this->assertSame(['a', 'd'], $groups->get(1)->toArray());
        $this->assertSame(['bb', 'cc'], $groups->get(2)->toArray());
    }

    #[Test]
    public function groupBy_applies_the_value_transform(): void
    {
        $groups = sequenceOf(['a', 'bb'])->groupBy(
            static fn (string $v): int => strlen($v),
            static fn (string $v): string => strtoupper($v),
        );

        $this->assertSame(['A'], $groups->get(1)->toArray());
        $this->assertSame(['BB'], $groups->get(2)->toArray());
    }

    #[Test]
    public function groupBy_matches_its_collection_counterpart(): void
    {
        $data = ['a', 'bb', 'cc', 'd'];
        $key = static fn (string $v): int => strlen($v);

        $this->assertSame(
            self::unwrapGroups(listOf($data)->groupBy($key)->toArray()),
            self::unwrapGroups(sequenceOf($data)->groupBy($key)->toArray()),
        );
    }

    #[Test]
    public function countBy_counts_elements_per_key(): void
    {
        $counts = sequenceOf(['a', 'bb', 'cc', 'd'])->countBy(static fn (string $v): int => strlen($v));

        $this->assertSame([1 => 2, 2 => 2], $counts->toArray());
    }

    #[Test]
    public function groupBy_with_null_key_throws(): void
    {
        $this->expectException(InvalidKeyTypeException::class);

        // @phpstan-ignore argument.type, argument.templateType
        $_ = sequenceOf(['a', 'b'])->groupBy(static fn (string $v) => null); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function countBy_with_null_key_throws(): void
    {
        $this->expectException(InvalidKeyTypeException::class);

        // @phpstan-ignore argument.type, argument.templateType
        $_ = sequenceOf(['a', 'b'])->countBy(static fn (string $v) => null); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function countBy_matches_its_collection_counterpart(): void
    {
        $data = ['a', 'bb', 'cc', 'd'];
        $key = static fn (string $v): int => strlen($v);

        $this->assertSame(listOf($data)->countBy($key)->toArray(), sequenceOf($data)->countBy($key)->toArray());
    }

    #[Test]
    public function partition_splits_the_matching_elements_from_the_rest(): void
    {
        [$even, $odd] = sequenceOf([1, 2, 3, 4])->partition(static fn (int $v): bool => $v % 2 === 0);

        $this->assertSame([2, 4], $even->toArray());
        $this->assertSame([1, 3], $odd->toArray());
    }

    #[Test]
    public function partition_matches_its_collection_counterpart(): void
    {
        $data = [1, 2, 3, 4, 5];
        $predicate = static fn (int $v): bool => $v > 2;

        [$eagerMatching, $eagerRest] = listOf($data)->partition($predicate);
        [$lazyMatching, $lazyRest] = sequenceOf($data)->partition($predicate);

        $this->assertSame($eagerMatching->toArray(), $lazyMatching->toArray());
        $this->assertSame($eagerRest->toArray(), $lazyRest->toArray());
    }

    #[Test]
    public function unzip_splits_pairs_into_two_lists(): void
    {
        [$first, $second] = sequenceOf([[1, 'a'], [2, 'b']])->unzip();

        $this->assertSame([1, 2], $first->toArray());
        $this->assertSame(['a', 'b'], $second->toArray());
    }

    #[Test]
    public function unzip_is_the_inverse_of_zip(): void
    {
        [$first, $second] = sequenceOf([1, 2, 3])->zip(['a', 'b', 'c'])->unzip();

        $this->assertSame([1, 2, 3], $first->toArray());
        $this->assertSame(['a', 'b', 'c'], $second->toArray());
    }

    #[Test]
    public function unzip_throws_on_an_element_that_is_not_a_pair(): void
    {
        $this->expectException(UnsupportedOperationException::class);

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
        $_ = sequenceOf([[1, 'a'], 'nope'])->unzip();
    }

    #[Test]
    public function unzip_throws_on_an_array_missing_one_side_of_the_pair(): void
    {
        $this->expectException(UnsupportedOperationException::class);

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
        $_ = sequenceOf([[1, 'a'], [2]])->unzip();
    }

    #[Test]
    public function unzip_keeps_a_pair_whose_components_are_null(): void
    {
        // A present null is a value, which is why the guard pairs ?? null with
        // array_key_exists() instead of just testing for null.
        [$first, $second] = sequenceOf([[null, null]])->unzip();

        $this->assertSame([null], $first->toArray());
        $this->assertSame([null], $second->toArray());
    }

    /**
     * @param array<int, Collection<string>> $groups
     * @return array<int, list<string>>
     */
    private static function unwrapGroups(array $groups): array
    {
        $unwrapped = [];
        foreach ($groups as $key => $bucket) {
            $unwrapped[$key] = $bucket->toArray();
        }

        return $unwrapped;
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
