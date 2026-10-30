<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use PHPUnit\Framework\Attributes\Test;

trait CollectionCallbackIndex
{
    #[Test]
    public function find_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $indices = [];
        $collection->find(function ($v, $i) use (&$indices) {
            $indices[] = $i;
            return $v === 'b';
        });
        $this->assertSame([0, 1], $indices);
    }

    #[Test]
    public function findLast_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $indices = [];
        $collection->findLast(function ($v, $i) use (&$indices) {
            $indices[] = $i;
            return $v === 'b';
        });
        $this->assertSame([0, 1, 2], $indices);
    }

    #[Test]
    public function expect_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $indices = [];
        $collection->expect(function ($v, $i) use (&$indices) {
            $indices[] = $i;
            return $v === 'b';
        });
        $this->assertSame([0, 1], $indices);
    }

    #[Test]
    public function expectLast_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $indices = [];
        $collection->expectLast(function ($v, $i) use (&$indices) {
            $indices[] = $i;
            return $v === 'b';
        });
        $this->assertSame([0, 1, 2], $indices);
    }

    #[Test]
    public function countWhere_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $indices = [];
        $collection->countWhere(function ($v, $i) use (&$indices) {
            $indices[] = $i;
            return true;
        });
        $this->assertSame([0, 1, 2], $indices);
    }

    #[Test]
    public function all_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $indices = [];
        $collection->all(function ($v, $i) use (&$indices) {
            $indices[] = $i;
            return true;
        });
        $this->assertSame([0, 1, 2], $indices);
    }

    #[Test]
    public function any_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $indices = [];
        $collection->any(function ($v, $i) use (&$indices) {
            $indices[] = $i;
            return $v === 'c';
        });
        $this->assertSame([0, 1, 2], $indices);
    }

    #[Test]
    public function none_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $indices = [];
        $collection->none(function ($v, $i) use (&$indices) {
            $indices[] = $i;
            return false;
        });
        $this->assertSame([0, 1, 2], $indices);
    }

    #[Test]
    public function filter_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c', 'd']);
        $result = $collection->filter(fn ($v, $i) => $i % 2 === 0);
        $this->assertSame(['a', 'c'], $result->toArray());
    }

    #[Test]
    public function forEach_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $pairs = [];
        $collection->forEach(function ($v, $i) use (&$pairs) {
            $pairs[] = "$i:$v";
        });
        $this->assertSame(['0:a', '1:b', '2:c'], $pairs);
    }

    #[Test]
    public function map_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $mapped = $collection->map(fn ($v, $i) => "$i:$v");
        $this->assertSame(['0:a', '1:b', '2:c'], $mapped->toArray());
    }

    #[Test]
    public function flatMap_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b']);
        $result = $collection->flatMap(fn ($v, $i) => ["$i:$v"]);
        $this->assertSame(['0:a', '1:b'], $result->toArray());
    }

    #[Test]
    public function takeWhile_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c', 'd']);
        $result = $collection->takeWhile(fn ($v, $i) => $i < 2);
        $this->assertSame(['a', 'b'], $result->toArray());
    }

    #[Test]
    public function dropWhile_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c', 'd']);
        $result = $collection->dropWhile(fn ($v, $i) => $i < 2);
        $this->assertSame(['c', 'd'], $result->toArray());
    }

    #[Test]
    public function takeLastWhile_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c', 'd']);
        $result = $collection->takeLastWhile(fn ($v, $i) => $i >= 2);
        $this->assertSame(['c', 'd'], $result->toArray());
    }

    #[Test]
    public function dropLastWhile_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c', 'd']);
        $result = $collection->dropLastWhile(fn ($v, $i) => $i >= 2);
        $this->assertSame(['a', 'b'], $result->toArray());
    }

    #[Test]
    public function partition_predicate_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c', 'd']);
        [$first, $second] = $collection->partition(fn ($v, $i) => $i < 2);
        $this->assertSame(['a', 'b'], $first->toArray());
        $this->assertSame(['c', 'd'], $second->toArray());
    }

    #[Test]
    public function toMap_keyMapper_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $map = $collection->toMap(fn ($v, $i) => $i);

        $this->assertSame('a', $map->get(0));
        $this->assertSame('b', $map->get(1));
        $this->assertSame('c', $map->get(2));
    }

    #[Test]
    public function toMap_valueMapper_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $map = $collection->toMap(fn ($v, $i) => $v, fn ($v, $i) => $i);

        $this->assertSame(0, $map->get('a'));
        $this->assertSame(1, $map->get('b'));
        $this->assertSame(2, $map->get('c'));
    }
}
