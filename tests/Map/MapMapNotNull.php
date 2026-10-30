<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use PHPUnit\Framework\Attributes\Test;

trait MapMapNotNull
{
    #[Test]
    public function mapNotNull_filters_null_results(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
        $result = $map->mapNotNull(fn (int $v, string $k) => $v > 1 ? "$k=$v" : null);
        $this->assertSame(['b=2', 'c=3'], $result->toArray());
    }

    #[Test]
    public function mapNotNull_empty_map(): void
    {
        $map = $this->mapOf([]);
        $result = $map->mapNotNull(fn ($v) => $v);
        $this->assertSame([], $result->toArray());
    }

    #[Test]
    public function mapNotNull_all_null(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $result = $map->mapNotNull(fn () => null);
        $this->assertSame([], $result->toArray());
    }

    #[Test]
    public function mapNotNull_none_null(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $result = $map->mapNotNull(fn (int $v, string $k) => "$k=$v");
        $this->assertSame(['a=1', 'b=2'], $result->toArray());
    }

    #[Test]
    public function mapNotNull_preserves_order(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
        $result = $map->mapNotNull(fn (int $v) => $v % 2 === 0 ? $v * 10 : null);
        $this->assertSame([20, 40], $result->toArray());
    }

    #[Test]
    public function mapNotNull_keeps_zero_and_empty_string(): void
    {
        $map = $this->mapOf(['a' => 'keep', 'b' => 'drop', 'c' => 'keep']);
        $result = $map->mapNotNull(fn (string $v) => match ($v) {
            'keep' => 0,
            'drop' => null,
            default => $v,
        });
        $this->assertSame([0, 0], $result->toArray());
    }
}
