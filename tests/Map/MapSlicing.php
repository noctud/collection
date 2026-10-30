<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use PHPUnit\Framework\Attributes\Test;
use Noctud\Collection\Map\ImmutableMap;

trait MapSlicing
{
    #[Test]
    public function takeFirst_returns_first_n_entries(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $map->takeFirst(3)->toArray());
    }

    #[Test]
    public function takeFirst_default_returns_first_entry(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['a' => 1], $map->takeFirst()->toArray());
    }

    #[Test]
    public function takeFirst_more_than_count_returns_all(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->takeFirst(10)->toArray());
    }

    #[Test]
    public function takeFirst_zero_returns_empty(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(0, $map->takeFirst(0)->count());
    }

    #[Test]
    public function takeFirst_negative_returns_empty(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(0, $map->takeFirst(-1)->count());
    }

    #[Test]
    public function takeFirst_on_empty_returns_empty(): void
    {
        $map = $this->mapOf([]);

        $this->assertSame(0, $map->takeFirst(3)->count());
    }

    #[Test]
    public function takeLast_returns_last_n_entries(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $this->assertSame(['c' => 3, 'd' => 4, 'e' => 5], $map->takeLast(3)->toArray());
    }

    #[Test]
    public function takeLast_default_returns_last_entry(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['c' => 3], $map->takeLast()->toArray());
    }

    #[Test]
    public function takeLast_more_than_count_returns_all(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->takeLast(10)->toArray());
    }

    #[Test]
    public function takeLast_zero_returns_empty(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(0, $map->takeLast(0)->count());
    }

    #[Test]
    public function dropFirst_returns_entries_after_n(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $this->assertSame(['c' => 3, 'd' => 4, 'e' => 5], $map->dropFirst(2)->toArray());
    }

    #[Test]
    public function dropFirst_default_drops_first_entry(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['b' => 2, 'c' => 3], $map->dropFirst()->toArray());
    }

    #[Test]
    public function dropFirst_more_than_count_returns_empty(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(0, $map->dropFirst(10)->count());
    }

    #[Test]
    public function dropFirst_zero_returns_all(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->dropFirst(0)->toArray());
    }

    #[Test]
    public function dropFirst_negative_returns_all(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->dropFirst(-1)->toArray());
    }

    #[Test]
    public function dropLast_returns_entries_before_last_n(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $map->dropLast(2)->toArray());
    }

    #[Test]
    public function dropLast_default_drops_last_entry(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->dropLast()->toArray());
    }

    #[Test]
    public function dropLast_more_than_count_returns_empty(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(0, $map->dropLast(10)->count());
    }

    #[Test]
    public function dropLast_zero_returns_all(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->dropLast(0)->toArray());
    }

    #[Test]
    public function takeWhile_takes_entries_while_predicate_true(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->takeWhile(fn ($v) => $v < 3)->toArray());
    }

    #[Test]
    public function takeWhile_stops_at_first_false(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 5, 'c' => 2, 'd' => 3]);

        // Stops at 'b' => 5 (which is >= 3), so only 'a' => 1
        $this->assertSame(['a' => 1], $map->takeWhile(fn ($v) => $v < 3)->toArray());
    }

    #[Test]
    public function takeWhile_all_match_returns_all(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->takeWhile(fn ($v) => $v < 10)->toArray());
    }

    #[Test]
    public function takeWhile_none_match_returns_empty(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(0, $map->takeWhile(fn ($v) => $v > 10)->count());
    }

    #[Test]
    public function takeWhile_receives_key(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->takeWhile(fn ($v, $k) => $k !== 'c')->toArray());
    }

    #[Test]
    public function takeWhile_on_empty_returns_empty(): void
    {
        $map = $this->mapOf([]);

        $this->assertSame(0, $map->takeWhile(fn ($v) => true)->count());
    }

    #[Test]
    public function dropWhile_drops_entries_while_predicate_true(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $this->assertSame(['c' => 3, 'd' => 4, 'e' => 5], $map->dropWhile(fn ($v) => $v < 3)->toArray());
    }

    #[Test]
    public function dropWhile_keeps_rest_after_first_false(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 5, 'c' => 2, 'd' => 3]);

        // Drops 'a' => 1 (< 3), stops at 'b' => 5 (>= 3), keeps 'b','c','d'
        $this->assertSame(['b' => 5, 'c' => 2, 'd' => 3], $map->dropWhile(fn ($v) => $v < 3)->toArray());
    }

    #[Test]
    public function dropWhile_all_match_returns_empty(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(0, $map->dropWhile(fn ($v) => $v < 10)->count());
    }

    #[Test]
    public function dropWhile_none_match_returns_all(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->dropWhile(fn ($v) => $v > 10)->toArray());
    }

    #[Test]
    public function dropWhile_receives_key(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['c' => 3], $map->dropWhile(fn ($v, $k) => $k !== 'c')->toArray());
    }

    #[Test]
    public function dropWhile_on_empty_returns_empty(): void
    {
        $map = $this->mapOf([]);

        $this->assertSame(0, $map->dropWhile(fn ($v) => true)->count());
    }

    #[Test]
    public function takeLastWhile_takes_entries_from_end_while_predicate_true(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $this->assertSame(['d' => 4, 'e' => 5], $map->takeLastWhile(fn ($v) => $v > 3)->toArray());
    }

    #[Test]
    public function takeLastWhile_stops_at_first_false_from_end(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 5, 'c' => 2, 'd' => 3]);

        // From end: 3 matches (> 1), 2 matches (> 1), 5 matches (> 1), 1 doesn't (= 1) → takes last 3
        $this->assertSame(['b' => 5, 'c' => 2, 'd' => 3], $map->takeLastWhile(fn ($v) => $v > 1)->toArray());
    }

    #[Test]
    public function takeLastWhile_all_match_returns_all(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->takeLastWhile(fn ($v) => $v < 10)->toArray());
    }

    #[Test]
    public function takeLastWhile_none_match_returns_empty(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(0, $map->takeLastWhile(fn ($v) => $v > 10)->count());
    }

    #[Test]
    public function takeLastWhile_receives_key(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['b' => 2, 'c' => 3], $map->takeLastWhile(fn ($v, $k) => $k !== 'a')->toArray());
    }

    #[Test]
    public function takeLastWhile_on_empty_returns_empty(): void
    {
        $map = $this->mapOf([]);

        $this->assertSame(0, $map->takeLastWhile(fn ($v) => true)->count());
    }

    #[Test]
    public function dropLastWhile_drops_entries_from_end_while_predicate_true(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $map->dropLastWhile(fn ($v) => $v > 3)->toArray());
    }

    #[Test]
    public function dropLastWhile_keeps_rest_before_first_false_from_end(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 5, 'c' => 2, 'd' => 3]);

        // From end: 3 matches (> 1), 2 matches (> 1), 5 matches (> 1), 1 doesn't (= 1) → keeps only 'a' => 1
        $this->assertSame(['a' => 1], $map->dropLastWhile(fn ($v) => $v > 1)->toArray());
    }

    #[Test]
    public function dropLastWhile_all_match_returns_empty(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(0, $map->dropLastWhile(fn ($v) => $v < 10)->count());
    }

    #[Test]
    public function dropLastWhile_none_match_returns_all(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $map->dropLastWhile(fn ($v) => $v > 10)->toArray());
    }

    #[Test]
    public function dropLastWhile_receives_key(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['a' => 1], $map->dropLastWhile(fn ($v, $k) => $k !== 'a')->toArray());
    }

    #[Test]
    public function dropLastWhile_on_empty_returns_empty(): void
    {
        $map = $this->mapOf([]);

        $this->assertSame(0, $map->dropLastWhile(fn ($v) => true)->count());
    }

    #[Test]
    public function slicing_returns_immutable(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertInstanceOf(ImmutableMap::class, $map->takeFirst(2));
        $this->assertInstanceOf(ImmutableMap::class, $map->takeLast(2));
        $this->assertInstanceOf(ImmutableMap::class, $map->dropFirst(1));
        $this->assertInstanceOf(ImmutableMap::class, $map->dropLast(1));
        $this->assertInstanceOf(ImmutableMap::class, $map->takeWhile(fn ($v) => true));
        $this->assertInstanceOf(ImmutableMap::class, $map->dropWhile(fn ($v) => false));
        $this->assertInstanceOf(ImmutableMap::class, $map->takeLastWhile(fn ($v) => true));
        $this->assertInstanceOf(ImmutableMap::class, $map->dropLastWhile(fn ($v) => false));
    }

    #[Test]
    public function slicing_does_not_modify_original(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $result = $map->takeFirst(1);
        $this->assertSame(1, $result->count());
        $result = $map->takeLast(1);
        $this->assertSame(1, $result->count());
        $result = $map->dropFirst(1);
        $this->assertSame(2, $result->count());
        $result = $map->dropLast(1);
        $this->assertSame(2, $result->count());
        $result = $map->takeWhile(fn ($v) => $v < 2);
        $this->assertSame(1, $result->count());
        $result = $map->dropWhile(fn ($v) => $v < 2);
        $this->assertSame(2, $result->count());
        $result = $map->takeLastWhile(fn ($v) => $v > 2);
        $this->assertSame(1, $result->count());
        $result = $map->dropLastWhile(fn ($v) => $v > 2);
        $this->assertSame(2, $result->count());

        $this->assertSame(3, $map->count());
    }
}
