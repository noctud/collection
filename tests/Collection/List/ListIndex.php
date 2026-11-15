<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List;

use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\List\ListInterface;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

trait ListIndex
{
    #[Test]
    public function indexOf_finds_first_occurrence(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a', 'b', 'c', 'b']);

        $this->assertSame(1, $list->indexOf('b'));
        $this->assertSame(0, $list->indexOf('a'));
        $this->assertSame(2, $list->indexOf('c'));
    }

    #[Test]
    public function indexOf_returns_negative_when_not_found(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a', 'b']);

        $this->assertSame(-1, $list->indexOf('z'));
    }

    #[Test]
    public function indexOf_uses_strict_comparison(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf([0, 1, 2]);

        $this->assertSame(-1, $list->indexOf(false));
        $this->assertSame(-1, $list->indexOf(null));
        $this->assertSame(0, $list->indexOf(0));
    }

    #[Test]
    public function indexOf_with_objects(): void
    {
        $obj = new stdClass();
        /** @var ListInterface $list */
        $list = $this->collectionOf([$obj, new stdClass()]);

        $this->assertSame(0, $list->indexOf($obj));
        $this->assertSame(-1, $list->indexOf(new stdClass()));
    }

    #[Test]
    public function lastIndexOf_finds_last_occurrence(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a', 'b', 'c', 'b', 'd']);

        $this->assertSame(3, $list->lastIndexOf('b'));
        $this->assertSame(0, $list->lastIndexOf('a'));
    }

    #[Test]
    public function lastIndexOf_returns_negative_when_not_found(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a']);

        $this->assertSame(-1, $list->lastIndexOf('z'));
    }

    #[Test]
    public function indexOfFirst_by_predicate(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf([1, 2, 3, 4, 5]);

        $this->assertSame(1, $list->indexOfFirst(fn (int $v) => $v % 2 === 0));
        $this->assertSame(0, $list->indexOfFirst(fn ($v) => $v > 0));
    }

    #[Test]
    public function indexOfFirst_returns_negative_when_none_match(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf([1, 3, 5]);

        $this->assertSame(-1, $list->indexOfFirst(fn (int $v) => $v % 2 === 0));
    }

    #[Test]
    public function indexOfFirst_on_empty(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf([]);

        $this->assertSame(-1, $list->indexOfFirst(fn ($v) => true));
    }

    #[Test]
    public function indexOfLast_by_predicate(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf([1, 2, 3, 4, 5]);

        $this->assertSame(3, $list->indexOfLast(fn (int $v) => $v % 2 === 0));
        $this->assertSame(4, $list->indexOfLast(fn ($v) => $v > 0));
    }

    #[Test]
    public function indexOfLast_returns_negative_when_none_match(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf([1, 3, 5]);

        $this->assertSame(-1, $list->indexOfLast(fn (int $v) => $v % 2 === 0));
    }

    #[Test]
    public function indexOfLast_on_empty(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf([]);

        $this->assertSame(-1, $list->indexOfLast(fn ($v) => true));
    }

    #[Test]
    public function slice_returns_sublist(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf([1, 2, 3, 4, 5]);

        $this->assertSame([2, 3], $list->slice(1, 3)->toArray());
        $this->assertSame([1, 2, 3, 4, 5], $list->slice(0, 5)->toArray());
        $this->assertSame([1], $list->slice(0, 1)->toArray());
    }

    #[Test]
    public function slice_empty_range(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf([1, 2, 3]);

        $this->assertSame([], $list->slice(1, 1)->toArray());
    }

    #[Test]
    public function slice_throws_when_to_exceeds_size(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf([1, 2, 3]);

        $this->expectException(IndexOutOfBoundsException::class);
        $result = $list->slice(0, 10); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }
}
