<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Error;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\ImmutableCollection;
use Noctud\Collection\Map\SimpleMapEntry;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\MutableCollection;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\MutableSet;
use PHPUnit\Framework\Attributes\Test;

trait MapKeysValuesEntries
{
    #[Test]
    public function keys_values_entries_are_read_only(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['a', 'b', 'c'], $map->keys->toArray());
        $this->assertSame([1, 2, 3], $map->values->toArray());
        $this->assertEquals([
            new SimpleMapEntry('a', 1),
            new SimpleMapEntry('b', 2),
            new SimpleMapEntry('c', 3),
        ], $map->entries->toArray());

        $this->assertFalse(
            $map->keys instanceof MutableSet || $map->keys instanceof ImmutableSet,
            'Keys should not be real MutableSet or ImmutableSet'
        );

        $this->assertFalse(
            $map->values instanceof MutableCollection || $map->values instanceof ImmutableCollection,
            'Values should not be real MutableCollection or ImmutableCollection'
        );

        $this->assertFalse(
            $map->entries instanceof MutableSet || $map->entries instanceof ImmutableSet,
            'Entries should not be real MutableSet or ImmutableSet'
        );

        $this->expectException(Error::class);
        $map->entries->add(new SimpleMapEntry('z', 1)); /** @phpstan-ignore-line */

        $this->expectException(Error::class);
        $map->keys->add(9); /** @phpstan-ignore-line */

        $this->expectException(Error::class);
        $map->values->add(9); /** @phpstan-ignore-line */
    }

    #[Test]
    public function keys_values_entries_are_in_sync(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
        $refToKeys = $map->keys;
        $refToValues = $map->values;
        $refToEntries = $map->entries;

        $map = $map->put('d', 4)->remove('b');

        // Ensure keys and values are updated
        $this->assertSame(['a', 'c', 'd'], $map->keys->toArray());
        $this->assertSame([1, 3, 4], $map->values->toArray());
        $this->assertEquals([
            new SimpleMapEntry('a', 1),
            new SimpleMapEntry('c', 3),
            new SimpleMapEntry('d', 4),
        ], $map->entries->toArray());

        // Ensure the keys and values are in sync for MutableMap and immutable for ImmutableMap
        if ($map instanceof MutableMap) {
            $this->assertSame(['a', 'c', 'd'], $refToKeys->toArray());
            $this->assertSame([1, 3, 4], $refToValues->toArray());
            $this->assertEquals([
                new SimpleMapEntry('a', 1),
                new SimpleMapEntry('c', 3),
                new SimpleMapEntry('d', 4),
            ], $refToEntries->toArray());
        } else {
            $this->assertSame(['a', 'b', 'c'], $refToKeys->toArray());
            $this->assertSame([1, 2, 3], $refToValues->toArray());
            $this->assertEquals([
                new SimpleMapEntry('a', 1),
                new SimpleMapEntry('b', 2),
                new SimpleMapEntry('c', 3),
            ], $refToEntries->toArray());
        }
    }

    #[Test]
    public function keys_isEmpty_and_count(): void
    {
        $emptyMap = $this->mapOf([]);
        $this->assertTrue($emptyMap->keys->isEmpty());
        $this->assertSame(0, $emptyMap->keys->count());

        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $this->assertFalse($map->keys->isEmpty());
        $this->assertSame(2, $map->keys->count());
    }

    #[Test]
    public function keys_contains(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertTrue($map->keys->contains('a'));
        $this->assertTrue($map->keys->contains('c'));
        $this->assertFalse($map->keys->contains('x'));
        $this->assertFalse($map->keys->contains(1)); /** @phpstan-ignore-line */
    }

    #[Test]
    public function values_isEmpty_and_count(): void
    {
        $emptyMap = $this->mapOf([]);
        $this->assertTrue($emptyMap->values->isEmpty());
        $this->assertSame(0, $emptyMap->values->count());

        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $this->assertFalse($map->values->isEmpty());
        $this->assertSame(2, $map->values->count());
    }

    #[Test]
    public function values_contains(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertTrue($map->values->contains(1));
        $this->assertTrue($map->values->contains(3));
        $this->assertFalse($map->values->contains(99));
        $this->assertFalse($map->values->contains('1')); /** @phpstan-ignore-line */
    }

    #[Test]
    public function entries_isEmpty_and_count(): void
    {
        $emptyMap = $this->mapOf([]);
        $this->assertTrue($emptyMap->entries->isEmpty());
        $this->assertSame(0, $emptyMap->entries->count());

        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $this->assertFalse($map->entries->isEmpty());
        $this->assertSame(2, $map->entries->count());
    }

    #[Test]
    public function entries_contains(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $this->assertTrue($map->entries->contains(new SimpleMapEntry('a', 1)));
        $this->assertTrue($map->entries->contains(new SimpleMapEntry('b', 2)));
        $this->assertFalse($map->entries->contains(new SimpleMapEntry('a', 2)));
        $this->assertFalse($map->entries->contains(new SimpleMapEntry('c', 1)));
        $this->assertFalse($map->entries->contains('not an entry')); /** @phpstan-ignore-line */
    }

    #[Test]
    public function entries_first_last(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $first = $map->entries->first();
        $this->assertEquals(new SimpleMapEntry('a', 1), $first);

        $last = $map->entries->last();
        $this->assertEquals(new SimpleMapEntry('c', 3), $last);
    }

    #[Test]
    public function entries_first_last_on_empty(): void
    {
        $map = $this->mapOf([]);

        $this->assertNull($map->entries->firstOrNull());
        $this->assertNull($map->entries->lastOrNull());

        $this->expectException(NoSuchElementException::class);
        $map->entries->first();
    }

    #[Test]
    public function entries_toArray(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $arr = $map->entries->toArray();
        $this->assertCount(2, $arr);
        $this->assertEquals(new SimpleMapEntry('a', 1), $arr[0]);
        $this->assertEquals(new SimpleMapEntry('b', 2), $arr[1]);
    }

    #[Test]
    public function keys_values_random(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $randomKey = $map->keys->random();
        $this->assertContains($randomKey, ['a', 'b', 'c']);

        $randomValue = $map->values->random();
        $this->assertContains($randomValue, [1, 2, 3]);

        $randomEntry = $map->entries->random();
        $this->assertContains($randomEntry->key, ['a', 'b', 'c']);
    }

    #[Test]
    public function keys_values_random_on_empty(): void
    {
        $map = $this->mapOf([]);

        $this->assertNull($map->keys->randomOrNull()); /** @phpstan-ignore-line */
        $this->assertNull($map->values->randomOrNull()); /** @phpstan-ignore-line */
        $this->assertNull($map->entries->randomOrNull()); /** @phpstan-ignore-line */
    }

    #[Test]
    public function keys_values_entries_transformations_return_immutable(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertInstanceOf(ImmutableCollection::class, $map->keys->toList());
        $this->assertInstanceOf(ImmutableMap::class, $map->values->groupBy(fn ($v) => $v));
        $this->assertInstanceOf(ImmutableCollection::class, $map->entries->toList());
    }

    #[Test]
    public function entries_intersect_with_another_map(): void
    {
        $map1 = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
        $map2 = $this->mapOf(['b' => 2, 'c' => 99, 'd' => 4]);

        $result = $map1->entries->intersect($map2->entries);

        $this->assertInstanceOf(ImmutableSet::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals([new SimpleMapEntry('b', 2)], $result->toArray());
    }

    #[Test]
    public function entries_union_with_another_map(): void
    {
        $map1 = $this->mapOf(['a' => 1, 'b' => 2]);
        $map2 = $this->mapOf(['b' => 2, 'c' => 3]);

        $result = $map1->entries->union($map2->entries);

        $this->assertInstanceOf(ImmutableSet::class, $result);
        $this->assertCount(3, $result);
        $this->assertEquals([
            new SimpleMapEntry('a', 1),
            new SimpleMapEntry('b', 2),
            new SimpleMapEntry('c', 3),
        ], $result->toArray());
    }

    #[Test]
    public function entries_subtract_from_another_map(): void
    {
        $map1 = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
        $map2 = $this->mapOf(['b' => 2, 'd' => 4]);

        $result = $map1->entries->subtract($map2->entries);

        $this->assertInstanceOf(ImmutableSet::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals([
            new SimpleMapEntry('a', 1),
            new SimpleMapEntry('c', 3),
        ], $result->toArray());
    }

    #[Test]
    public function entries_intersect_same_key_different_value(): void
    {
        $map1 = $this->mapOf(['a' => 1, 'b' => 2]);
        $map2 = $this->mapOf(['a' => 99, 'b' => 2]);

        $result = $map1->entries->intersect($map2->entries);

        $this->assertCount(1, $result);
        $this->assertEquals([new SimpleMapEntry('b', 2)], $result->toArray());
    }

    #[Test]
    public function entries_distinct_deduplicates_by_key_and_value(): void
    {
        $map1 = $this->mapOf(['a' => 1, 'b' => 2]);
        $map2 = $this->mapOf(['a' => 1, 'c' => 3]);

        $combined = $map1->entries->toList()->addAll($map2->entries);
        $distinct = $combined->distinct();

        $this->assertCount(3, $distinct);
        $this->assertEquals([
            new SimpleMapEntry('a', 1),
            new SimpleMapEntry('b', 2),
            new SimpleMapEntry('c', 3),
        ], $distinct->toArray());
    }
}
