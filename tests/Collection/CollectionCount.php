<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use PHPUnit\Framework\Attributes\Test;

trait CollectionCount
{
    #[Test]
    public function count_without_predicate(): void
    {
        $collection = $this->collectionOf([1, 2, 3, 4, 5]);

        $this->assertSame(5, $collection->count());
    }

    #[Test]
    public function countWhere_with_predicate(): void
    {
        $collection = $this->collectionOf([1, 2, 3, 4, 5]);

        $this->assertSame(2, $collection->countWhere(fn ($v) => $v % 2 === 0));
    }

    #[Test]
    public function countWhere_no_match(): void
    {
        $collection = $this->collectionOf([1, 3, 5]);

        $this->assertSame(0, $collection->countWhere(fn ($v) => $v % 2 === 0));
    }

    #[Test]
    public function countWhere_empty(): void
    {
        $collection = $this->collectionOf([]);

        $this->assertSame(0, $collection->countWhere(fn ($v) => true));
    }

    #[Test]
    public function countWhere_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c', 'd']);
        $indices = [];

        $collection->countWhere(function ($v, $i) use (&$indices) {
            $indices[] = $i;
            return true;
        });

        $this->assertSame([0, 1, 2, 3], $indices);
    }
}
