<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\IntMap\Unit;

use Closure;
use Noctud\Collection\Map\HashMap\ImmutableHashMap;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Tests\Map\IntMap\Case\AbstractIntMapTestCase;
use Noctud\Collection\Tests\Map\MapChangedWithNull;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\mutableIntMapOf;

final class MutableIntMapTest extends AbstractIntMapTestCase
{
    use MapChangedWithNull;

    protected function sampleKey(): int
    {
        return 123;
    }

    /**
     * @template V
     * @param iterable<int,V>|Closure():iterable<int,V> $data
     * @return MutableMap<int,V>
     */
    public function mapOf(iterable|Closure $data): MutableMap
    {
        return mutableIntMapOf($data);
    }

    /**
     * @template V
     * @param iterable<int,V>|Closure():iterable<int,V> $data
     * @return MutableMap<int,V>
     */
    public function enumerableOf(iterable|Closure $data): MutableMap
    {
        return $this->mapOf($data);
    }

    #[Test]
    public function map_keys_returns_hashmap(): void
    {
        $map = $this->mapOf([1 => 'a', 2 => 'b']);

        // mapKeys signature is (V, K) -> NK
        $mapped = $map->mapKeys(fn (string $v, int $k) => "key_$k");

        // mapKeys should return ImmutableHashMap since keys may change type
        $this->assertInstanceOf(ImmutableHashMap::class, $mapped);
    }

    #[Test]
    public function flip_returns_hashmap(): void
    {
        /** @var MutableMap<int, string> $map */
        $map = $this->mapOf([1 => 'x', 2 => 'y']);

        $flipped = $map->flip();

        // flip should return ImmutableHashMap since values become keys (any type)
        $this->assertInstanceOf(ImmutableHashMap::class, $flipped);
    }

    #[Test]
    public function getOrPut_returns_existing_value_without_calling_closure(): void
    {
        $map = $this->mapOf([1 => 'a', 2 => 'b']);
        $called = false;

        $result = $map->getOrPut(1, function () use (&$called) {
            $called = true;
            return 'x';
        });

        $this->assertSame('a', $result);
        $this->assertFalse($called);
    }

    #[Test]
    public function getOrPut_computes_and_stores_when_key_missing(): void
    {
        $map = $this->mapOf([1 => 'a']);

        $result = $map->getOrPut(2, fn () => 'computed');

        $this->assertSame('computed', $result);
        $this->assertTrue($map->containsKey(2));
        $this->assertSame('computed', $map->get(2));
    }
}
