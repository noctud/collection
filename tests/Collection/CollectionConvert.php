<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Sequence\Sequence;
use Noctud\Collection\Set\ImmutableSet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use function array_is_list;

trait CollectionConvert
{
    #[Test]
    public function toArray_returns_indexed_array(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $this->assertSame(['a', 'b', 'c'], $collection->toArray());
    }

    #[Test]
    public function toArray_on_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertSame([], $collection->toArray());
    }

    #[Test]
    #[DataProvider('shouldReturnListProvider')]
    public function toArray_returns_list(array $data): void
    {
        $collection = $this->collectionOf($data);
        $this->assertTrue(array_is_list($collection->toArray()));
    }

    public static function shouldReturnListProvider(): iterable
    {
        yield 'with list' => [['a', 'b', 'c']];
        yield 'with associative array' => [['a' => 1, 'b' => 2, 'c' => 3]];
    }

    #[Test]
    public function jsonSerialize_matches_toArray(): void
    {
        $collection = $this->collectionOf([1, 2, 3]);
        $this->assertSame($collection->toArray(), $collection->jsonSerialize());
    }

    #[Test]
    public function jsonSerialize_on_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertSame([], $collection->jsonSerialize());
    }

    #[Test]
    public function count_returns_total_elements(): void
    {
        $collection = $this->collectionOf([1, 2, 3, 4, 5, 6]);
        $this->assertSame(6, $collection->count());
    }

    #[Test]
    public function count_on_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertSame(0, $collection->count());
    }

    #[Test]
    public function toList_preserves_order(): void
    {
        $collection = $this->collectionOf([3, 1, 2]);
        $list = $collection->toList();

        $this->assertSame([3, 1, 2], $list->toArray());
        $this->assertInstanceOf(ImmutableList::class, $list);
    }

    #[Test]
    public function toList_on_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertSame([], $collection->toList()->toArray());
    }

    #[Test]
    public function toSet_removes_duplicates(): void
    {
        $collection = $this->collectionOf([1, 2, 2, 3, 1]);
        $set = $collection->toSet();

        $this->assertSame([1, 2, 3], $set->toArray());
        $this->assertInstanceOf(ImmutableSet::class, $set);
    }

    #[Test]
    public function toSet_on_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertSame([], $collection->toSet()->toArray());
    }

    #[Test]
    public function asSequence_preserves_order(): void
    {
        $sequence = $this->collectionOf([3, 1, 2])->asSequence();

        $this->assertInstanceOf(Sequence::class, $sequence);
        $this->assertSame([3, 1, 2], $sequence->toArray());
    }

    #[Test]
    public function asSequence_on_empty(): void
    {
        $this->assertSame([], $this->collectionOf([])->asSequence()->toArray());
    }

    #[Test]
    public function asSequence_does_not_read_the_collection_before_a_terminal_operation(): void
    {
        $reads = 0;
        $collection = $this->collectionOf(function () use (&$reads): array {
            $reads++;

            return [1, 2, 3];
        });

        // view collections read their source as soon as they are built, so what is under
        // test is that asSequence() and the chained operation add no read of their own
        $readsOnceBuilt = $reads;
        $sequence = $collection->asSequence()->map(static fn (int $value): int => $value * 2);
        $this->assertSame($readsOnceBuilt, $reads);

        $this->assertSame([2, 4, 6], $sequence->toArray());
        $this->assertSame(1, $reads);
    }

    #[Test]
    public function asSequence_can_be_iterated_several_times(): void
    {
        $sequence = $this->collectionOf([1, 2, 3])->asSequence();

        $this->assertSame([1, 2, 3], $sequence->toArray());
        $this->assertSame([1, 2, 3], $sequence->toArray());
    }

    #[Test]
    public function asSequence_stays_lazy_and_short_circuits(): void
    {
        $mapped = 0;
        $first = $this->collectionOf([1, 2, 3])
            ->asSequence()
            ->map(function (int $value) use (&$mapped): int {
                $mapped++;

                return $value * 2;
            })
            ->first();

        $this->assertSame(2, $first);
        $this->assertSame(1, $mapped);
    }

    #[Test]
    public function debug_info(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);

        $debugInfo = $collection->__debugInfo(); /** @phpstan-ignore-line */
        $this->assertSame(['a', 'b', 'c'], $debugInfo);
    }
}
