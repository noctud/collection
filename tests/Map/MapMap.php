<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use PHPUnit\Framework\Attributes\Test;
use function strtoupper;

trait MapMap
{
    #[Test]
    public function map_transforms_entries_to_list(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $list = $map->map(fn ($v, $k) => $k . '=' . $v);
        $this->assertSame(['a=1', 'b=2'], $list->toArray());
    }

    #[Test]
    public function mapKeys_transforms_keys(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $out = $map->mapKeys(fn (int $v, string $k) => strtoupper($k))->toArray();
        $this->assertSame(['A' => 1, 'B' => 2], $out);
    }

    #[Test]
    public function mapValues_transforms_values(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $out = $map->mapValues(fn (int $v, string $k) => $v * 10)->toArray();
        $this->assertSame(['a' => 10, 'b' => 20], $out);
    }

    #[Test]
    public function mapValuesNotNull_excludes_null_results(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
        $result = $map->mapValuesNotNull(fn (int $v) => $v > 1 ? $v * 10 : null);
        $this->assertSame(['b' => 20, 'c' => 30], $result->toArray());
    }

    #[Test]
    public function mapValuesNotNull_on_empty_map(): void
    {
        $map = $this->mapOf([]);
        $result = $map->mapValuesNotNull(fn ($v) => $v);
        $this->assertSame([], $result->toArray());
    }

    #[Test]
    public function mapValuesNotNull_all_null(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $result = $map->mapValuesNotNull(fn () => null);
        $this->assertSame([], $result->toArray());
    }

    #[Test]
    public function mapValuesNotNull_no_nulls(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $result = $map->mapValuesNotNull(fn (int $v) => $v * 10);
        $this->assertSame(['a' => 10, 'b' => 20], $result->toArray());
    }

    #[Test]
    public function mapValuesNotNull_preserves_keys(): void
    {
        $map = $this->mapOfPairs([['01', 'a'], ['1', 'b'], ['2', null]]);
        $result = $map->mapValuesNotNull(fn ($v) => $v);
        $this->assertSame(['01' => 'a', '1' => 'b'], $result->toArray());
    }

    #[Test]
    public function mapValuesNotNull_receives_key(): void
    {
        $map = $this->mapOf(['x' => 10, 'y' => 20]);
        $result = $map->mapValuesNotNull(fn (int $v, string $k) => $k === 'x' ? $v . '_' . $k : null);
        $this->assertSame(['x' => '10_x'], $result->toArray());
    }

    #[Test]
    public function mapValuesNotNull_keeps_zero_and_empty_string(): void
    {
        $map = $this->mapOf(['a' => 'keep', 'b' => 'drop', 'c' => 'keep']);
        $result = $map->mapValuesNotNull(fn (string $v) => match ($v) {
            'keep' => 0,
            'drop' => null,
            default => $v,
        });
        $this->assertSame(['a' => 0, 'c' => 0], $result->toArray());
    }
}
