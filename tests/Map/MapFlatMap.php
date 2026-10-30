<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use PHPUnit\Framework\Attributes\Test;

trait MapFlatMap
{
    #[Test]
    public function flatMap_transforms_and_flattens(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
        $result = $map->flatMap(fn (int $v, string $k) => [$k, $v]);
        $this->assertSame(['a', 1, 'b', 2, 'c', 3], $result->toArray());
    }

    #[Test]
    public function flatMap_empty_map(): void
    {
        $map = $this->mapOf([]);
        $result = $map->flatMap(fn ($v, $k) => [$v]);
        $this->assertSame([], $result->toArray());
    }

    #[Test]
    public function flatMap_single_element_per_entry(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $result = $map->flatMap(fn (int $v, string $k) => ["$k=$v"]);
        $this->assertSame(['a=1', 'b=2'], $result->toArray());
    }

    #[Test]
    public function flatMap_with_empty_iterables(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
        $result = $map->flatMap(fn (int $v) => $v === 2 ? [] : [$v]);
        $this->assertSame([1, 3], $result->toArray());
    }

    #[Test]
    public function flatMap_preserves_order(): void
    {
        $map = $this->mapOf(['x' => 10, 'y' => 20]);
        $result = $map->flatMap(fn (int $v) => [$v, $v + 1]);
        $this->assertSame([10, 11, 20, 21], $result->toArray());
    }
}
