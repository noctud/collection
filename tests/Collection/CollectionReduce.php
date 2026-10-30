<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\Exception\UnsupportedOperationException;
use PHPUnit\Framework\Attributes\Test;

trait CollectionReduce
{
    #[Test]
    public function fold_accumulates_value(): void
    {
        $collection = $this->collectionOf([1, 2, 3, 4]);
        $result = $collection->fold(0, fn ($acc, $v) => $acc + $v);
        $this->assertSame(10, $result);
    }

    #[Test]
    public function fold_with_initial_value(): void
    {
        $collection = $this->collectionOf([1, 2, 3]);
        $result = $collection->fold(100, fn ($acc, $v) => $acc + $v);
        $this->assertSame(106, $result);
    }

    #[Test]
    public function fold_on_empty(): void
    {
        $collection = $this->collectionOf([]);
        $result = $collection->fold('start', fn ($acc, $v) => $acc . $v);
        $this->assertSame('start', $result);
    }

    #[Test]
    public function fold_string_concatenation(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $result = $collection->fold('', fn ($acc, $v) => $acc . $v);
        $this->assertSame('abc', $result);
    }

    #[Test]
    public function reduce_combines_elements(): void
    {
        $collection = $this->collectionOf([1, 2, 3, 4]);
        $result = $collection->reduce(fn ($acc, $v) => $acc + $v);
        $this->assertSame(10, $result);
    }

    #[Test]
    public function reduce_single_element(): void
    {
        $collection = $this->collectionOf([42]);
        $result = $collection->reduce(fn ($acc, $v) => $acc + $v);
        $this->assertSame(42, $result);
    }

    #[Test]
    public function reduce_throws_on_empty(): void
    {
        $collection = $this->collectionOf([]);

        $this->expectException(UnsupportedOperationException::class);
        $collection->reduce(fn ($acc, $v) => $acc + $v);
    }

    #[Test]
    public function reduceOrNull_returns_null_on_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertNull($collection->reduceOrNull(fn ($acc, $v) => $acc + $v));
    }

    #[Test]
    public function reduceOrNull_returns_result(): void
    {
        $collection = $this->collectionOf([1, 2, 3]);
        $this->assertSame(6, $collection->reduceOrNull(fn ($acc, $v) => $acc + $v));
    }

    #[Test]
    public function reduceOrNull_propagates_an_UnsupportedOperationException_thrown_by_the_operation(): void
    {
        $collection = $this->collectionOf([1, 2]);

        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessageIsOrContains('from the operation');

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
        $_ = $collection->reduceOrNull(static function (): int {
            throw new UnsupportedOperationException('from the operation');
        });
    }
}
