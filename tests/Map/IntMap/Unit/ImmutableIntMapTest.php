<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\IntMap\Unit;

use Closure;
use Noctud\Collection\Map\HashMap\ImmutableHashMap;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\IntMap\ImmutableIntMap;
use Noctud\Collection\Tests\Map\IntMap\Case\AbstractIntMapTestCase;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\intMapOf;
use stdClass;

final class ImmutableIntMapTest extends AbstractIntMapTestCase
{
    /**
     * @template V
     * @param iterable<int,V>|Closure():iterable<int,V> $data
     * @return ImmutableMap<int,V>
     */
    public function mapOf(iterable|Closure $data): ImmutableMap
    {
        return intMapOf($data);
    }

    /**
     * @template V
     * @param iterable<int,V>|Closure():iterable<int,V> $data
     * @return ImmutableMap<int,V>
     */
    public function enumerableOf(iterable|Closure $data): ImmutableMap
    {
        return $this->mapOf($data);
    }

    #[Test]
    public function map_keys_returns_hashmap(): void
    {
        $map = $this->mapOf([1 => 'a', 2 => 'b']);

        // mapKeys signature is (V, K) -> NK
        $mapped = $map->mapKeys(fn (string $v, int $k) => "key_$k");

        // mapKeys should return a HashMap since keys may change type
        $this->assertInstanceOf(ImmutableHashMap::class, $mapped);
    }

    #[Test]
    public function flip_returns_hashmap(): void
    {
        /** @var ImmutableMap<int, string> $map */
        $map = $this->mapOf([1 => 'x', 2 => 'y']);

        $flipped = $map->flip();

        // flip should return a HashMap since values become keys (any type)
        $this->assertInstanceOf(ImmutableHashMap::class, $flipped);
    }

    #[Test]
    public function put_returns_same_type(): void
    {
        $map = $this->mapOf([1 => 'a', 2 => 'b']);

        $newMap = $map->put(3, 'c');

        $this->assertInstanceOf(ImmutableIntMap::class, $newMap);
        $this->assertSame([1 => 'a', 2 => 'b', 3 => 'c'], $newMap->toArray());
    }

    #[Test]
    public function remove_returns_same_type(): void
    {
        $map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

        $newMap = $map->remove(2);

        $this->assertInstanceOf(ImmutableIntMap::class, $newMap);
        $this->assertSame([1 => 'a', 3 => 'c'], $newMap->toArray());
    }

    #[Test]
    public function put_with_non_int_key_returns_hashmap(): void
    {
        $map = $this->mapOf([1 => 'a', 2 => 'b']);

        $newMap = $map->put('foo', 'c');

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
        $this->assertSame('a', $newMap[1]);
        $this->assertSame('b', $newMap[2]);
        $this->assertSame('c', $newMap['foo']);
    }

    #[Test]
    public function putFirst_with_int_key_returns_int_map(): void
    {
        $map = $this->mapOf([1 => 'a', 2 => 'b']);

        $newMap = $map->putFirst(0, 'z');

        $this->assertInstanceOf(ImmutableIntMap::class, $newMap);
        $this->assertSame([[0, 'z'], [1, 'a'], [2, 'b']], $newMap->toPairs());
    }

    #[Test]
    public function putFirst_with_non_int_key_returns_hashmap(): void
    {
        $map = $this->mapOf([1 => 'a', 2 => 'b']);

        $newMap = $map->putFirst('foo', 'c');

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
        $this->assertSame('c', $newMap['foo']);
        $this->assertSame('a', $newMap[1]);
        $this->assertSame('b', $newMap[2]);
    }

    #[Test]
    public function putFirst_moves_existing_int_key_to_front(): void
    {
        $map = $this->mapOf([1 => 'a', 2 => 'b', 3 => 'c']);

        $newMap = $map->putFirst(3, 'x');

        $this->assertInstanceOf(ImmutableIntMap::class, $newMap);
        $this->assertSame([[3, 'x'], [1, 'a'], [2, 'b']], $newMap->toPairs());
    }

    #[Test]
    public function put_all_with_non_int_key_returns_hashmap(): void
    {
        $map = $this->mapOf([1 => 'a']);

        $newMap = $map->putAll(['foo' => 'b', 'bar' => 'c']);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
        $this->assertSame('a', $newMap[1]);
        $this->assertSame('b', $newMap['foo']);
        $this->assertSame('c', $newMap['bar']);
    }

    #[Test]
    public function put_all_with_int_keys_returns_int_map(): void
    {
        $map = $this->mapOf([1 => 'a']);

        $newMap = $map->putAll([2 => 'b', 3 => 'c']);

        $this->assertInstanceOf(ImmutableIntMap::class, $newMap);
        $this->assertSame([1 => 'a', 2 => 'b', 3 => 'c'], $newMap->toArray());
    }

    #[Test]
    public function put_all_pairs_with_non_int_key_returns_hashmap(): void
    {
        $map = $this->mapOf([1 => 'a']);

        $newMap = $map->putAllPairs([['foo', 'b'], [2, 'c']]);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
        $this->assertSame('a', $newMap[1]);
        $this->assertSame('b', $newMap['foo']);
        $this->assertSame('c', $newMap[2]);
    }

    #[Test]
    public function put_all_pairs_with_int_keys_returns_int_map(): void
    {
        $map = $this->mapOf([1 => 'a']);

        $newMap = $map->putAllPairs([[2, 'b'], [3, 'c']]);

        $this->assertInstanceOf(ImmutableIntMap::class, $newMap);
        $this->assertSame([1 => 'a', 2 => 'b', 3 => 'c'], $newMap->toArray());
    }

    #[Test]
    public function put_all_with_generator_and_non_int_key_returns_hashmap(): void
    {
        $map = $this->mapOf([1 => 'a']);

        $generator = (function () {
            yield 2 => 'b';
            yield 'foo' => 'c';
        })();

        $newMap = $map->putAll($generator);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
    }

    #[Test]
    public function put_all_with_generator_and_object_key_returns_hashmap(): void
    {
        $map = $this->mapOf([1 => 'a']);

        $obj = new stdClass();
        $generator = (function () use ($obj) {
            yield $obj => 'b';
            yield 2 => 'c';
        })();

        $newMap = $map->putAll($generator);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
        $this->assertSame('a', $newMap[1]);
        $this->assertSame('b', $newMap[$obj]);
        $this->assertSame('c', $newMap[2]);
    }

    #[Test]
    public function put_all_pairs_with_generator_and_non_int_key_returns_hashmap(): void
    {
        $map = $this->mapOf([1 => 'a']);

        $generator = (function () {
            yield [2, 'b'];
            yield ['foo', 'c'];
        })();

        $newMap = $map->putAllPairs($generator);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
    }
}
