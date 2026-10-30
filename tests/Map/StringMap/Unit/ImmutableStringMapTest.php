<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\StringMap\Unit;

use Closure;
use Noctud\Collection\Map\HashMap\ImmutableHashMap;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\StringMap\ImmutableStringMap;
use Noctud\Collection\Tests\Map\StringMap\Case\AbstractStringMapTestCase;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use function Noctud\Collection\stringMapOf;
use function strlen;

final class ImmutableStringMapTest extends AbstractStringMapTestCase
{
    /**
     * @template V
     * @param iterable<string,V>|Closure():iterable<string,V> $data
     * @return ImmutableMap<string,V>
     */
    public function mapOf(iterable|Closure $data): ImmutableMap
    {
        return stringMapOf($data);
    }

    /**
     * @template V
     * @param iterable<string,V>|Closure():iterable<string,V> $data
     * @return ImmutableMap<string,V>
     */
    public function enumerableOf(iterable|Closure $data): ImmutableMap
    {
        return $this->mapOf($data);
    }

    #[Test]
    public function map_keys_returns_hashmap(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        // mapKeys signature is (V, K) -> NK
        $mapped = $map->mapKeys(fn (int $v, string $k) => strlen($k));

        // mapKeys should return a HashMap since keys may change type
        $this->assertInstanceOf(ImmutableHashMap::class, $mapped);
    }

    #[Test]
    public function flip_returns_hashmap(): void
    {
        /** @var ImmutableMap<string, string> $map */
        $map = $this->mapOf(['a' => 'x', 'b' => 'y']);

        $flipped = $map->flip();

        // flip should return a HashMap since values become keys (any type)
        $this->assertInstanceOf(ImmutableHashMap::class, $flipped);
    }

    #[Test]
    public function put_returns_same_type(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $newMap = $map->put('c', 3);

        $this->assertInstanceOf(ImmutableStringMap::class, $newMap);
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $newMap->toArray());
    }

    #[Test]
    public function remove_returns_same_type(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $newMap = $map->remove('b');

        $this->assertInstanceOf(ImmutableStringMap::class, $newMap);
        $this->assertSame(['a' => 1, 'c' => 3], $newMap->toArray());
    }

    #[Test]
    public function put_with_non_string_key_returns_hashmap(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $newMap = $map->put(123, 3);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
        $this->assertSame(1, $newMap['a']);
        $this->assertSame(2, $newMap['b']);
        $this->assertSame(3, $newMap[123]);
    }

    #[Test]
    public function put_all_with_non_string_key_returns_hashmap(): void
    {
        $map = $this->mapOf(['a' => 1]);

        $newMap = $map->putAll([123 => 2, 456 => 3]);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
        $this->assertSame(1, $newMap['a']);
        $this->assertSame(2, $newMap[123]);
        $this->assertSame(3, $newMap[456]);
    }

    #[Test]
    public function put_all_with_string_keys_returns_string_map(): void
    {
        $map = $this->mapOf(['a' => 1]);

        $newMap = $map->putAll(['b' => 2, 'c' => 3]);

        $this->assertInstanceOf(ImmutableStringMap::class, $newMap);
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $newMap->toArray());
    }

    #[Test]
    public function put_all_pairs_with_non_string_key_returns_hashmap(): void
    {
        $map = $this->mapOf(['a' => 1]);

        $newMap = $map->putAllPairs([[123, 2], ['b', 3]]);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
        $this->assertSame(1, $newMap['a']);
        $this->assertSame(2, $newMap[123]);
        $this->assertSame(3, $newMap['b']);
    }

    #[Test]
    public function put_all_pairs_with_string_keys_returns_string_map(): void
    {
        $map = $this->mapOf(['a' => 1]);

        $newMap = $map->putAllPairs([['b', 2], ['c', 3]]);

        $this->assertInstanceOf(ImmutableStringMap::class, $newMap);
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $newMap->toArray());
    }

    #[Test]
    public function put_all_with_generator_and_non_string_key_returns_hashmap(): void
    {
        $map = $this->mapOf(['a' => 1]);

        $generator = (function () {
            yield 'b' => 2;
            yield 123 => 3;
        })();

        $newMap = $map->putAll($generator);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
    }

    #[Test]
    public function put_all_with_generator_and_object_key_returns_hashmap(): void
    {
        $map = $this->mapOf(['a' => 1]);

        $obj = new stdClass();
        $generator = (function () use ($obj) {
            yield $obj => 2;
            yield 'b' => 3;
        })();

        $newMap = $map->putAll($generator);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
        $this->assertSame(1, $newMap['a']);
        $this->assertSame(2, $newMap[$obj]);
        $this->assertSame(3, $newMap['b']);
    }

    #[Test]
    public function put_all_pairs_with_generator_and_non_string_key_returns_hashmap(): void
    {
        $map = $this->mapOf(['a' => 1]);

        $generator = (function () {
            yield ['b', 2];
            yield [123, 3];
        })();

        $newMap = $map->putAllPairs($generator);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
    }

    #[Test]
    public function putFirst_with_string_key_returns_string_map(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $newMap = $map->putFirst('z', 99);

        $this->assertInstanceOf(ImmutableStringMap::class, $newMap);
        $this->assertSame([['z', 99], ['a', 1], ['b', 2]], $newMap->toPairs());
    }

    #[Test]
    public function putFirst_with_non_string_key_returns_hashmap(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $newMap = $map->putFirst(123, 3);

        $this->assertInstanceOf(ImmutableHashMap::class, $newMap);
        $this->assertSame(3, $newMap->count());
        $this->assertSame(3, $newMap[123]);
        $this->assertSame(1, $newMap['a']);
        $this->assertSame(2, $newMap['b']);
    }

    #[Test]
    public function putFirst_moves_existing_string_key_to_front(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $newMap = $map->putFirst('c', 99);

        $this->assertInstanceOf(ImmutableStringMap::class, $newMap);
        $this->assertSame([['c', 99], ['a', 1], ['b', 2]], $newMap->toPairs());
    }
}
