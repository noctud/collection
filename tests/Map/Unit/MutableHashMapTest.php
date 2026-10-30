<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\Unit;

use Closure;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Tests\Map\Case\AbstractMapTestCase;
use Noctud\Collection\Tests\Map\MapChangedWithNull;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\mutableMapOf;
use function Noctud\Collection\mutableMapOfPairs;

final class MutableHashMapTest extends AbstractMapTestCase
{
    use MapChangedWithNull;

    protected function sampleKey(): string
    {
        return 'test_key';
    }

    /**
     * @template K of string|int|bool|float|object
     * @template V
     * @param iterable<K,V>|Closure():iterable<K,V> $data
     * @return MutableMap<K,V>
     */
    public function mapOf(iterable|Closure $data): MutableMap
    {
        return mutableMapOf($data);
    }

    /**
     * @template K of string|int|bool|float|object
     * @template V
     * @param iterable<array{0:K,1:V}>|Closure():iterable<array{0:K,1:V}> $data
     * @return MutableMap<K,V>
     */
    public function mapOfPairs(iterable|Closure $data): MutableMap
    {
        return mutableMapOfPairs($data);
    }

    /**
     * @template K of string|int|bool|float|object
     * @template V
     * @param iterable<K,V>|Closure():iterable<K,V> $data
     * @return MutableMap<K,V>
     */
    public function enumerableOf(iterable|Closure $data): MutableMap
    {
        return $this->mapOf($data);
    }

    #[Test]
    public function getOrPut_returns_existing_value_without_calling_closure(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $called = false;

        $result = $map->getOrPut('a', function () use (&$called) {
            $called = true;
            return 99;
        });

        $this->assertSame(1, $result);
        $this->assertFalse($called);
    }

    #[Test]
    public function getOrPut_computes_and_stores_when_key_missing(): void
    {
        $map = $this->mapOf(['a' => 1]);

        $result = $map->getOrPut('b', fn () => 42);

        $this->assertSame(42, $result);
        $this->assertTrue($map->containsKey('b'));
        $this->assertSame(42, $map->get('b'));
    }

    #[Test]
    public function getOrPut_preserves_null_value(): void
    {
        /** @var MutableMap<string, string|null> $map */
        $map = $this->mapOf(['a' => null, 'z' => 'exists']);

        // Key exists with null value — should return null, not call closure
        $result = $map->getOrPut('a', fn () => 'computed');
        $this->assertNull($result);

        // Key missing — should compute, store, and return
        $result = $map->getOrPut('b', fn () => null);
        $this->assertNull($result);
        $this->assertTrue($map->containsKey('b'));
    }

    #[Test]
    public function getOrPut_second_call_returns_stored_value(): void
    {
        /** @var MutableMap<string, string> $map */
        $map = $this->mapOf([]);

        $result = $map->getOrPut('x', fn () => 'first');
        $this->assertSame('first', $result);
        $result = $map->getOrPut('x', fn () => 'second');

        $this->assertSame('first', $result);
    }
}
