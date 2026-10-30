<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use PHPUnit\Framework\Attributes\Test;

trait MapCount
{
    #[Test]
    public function count_without_predicate(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(3, $map->count());
    }

    #[Test]
    public function countWhere_with_predicate(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);

        $this->assertSame(2, $map->countWhere(fn ($v) => $v % 2 === 0));
    }

    #[Test]
    public function countWhere_receives_key(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
        $keys = [];

        $map->countWhere(function ($v, $k) use (&$keys) {
            $keys[] = $k;
            return true;
        });

        $this->assertSame(['a', 'b', 'c'], $keys);
    }

    #[Test]
    public function countWhere_no_match(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 3, 'c' => 5]);

        $this->assertSame(0, $map->countWhere(fn ($v) => $v % 2 === 0));
    }

    #[Test]
    public function countWhere_empty(): void
    {
        $map = $this->mapOf([]);

        $this->assertSame(0, $map->countWhere(fn ($v) => true));
    }
}
