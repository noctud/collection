<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Noctud\Collection\Exception\UnsupportedOperationException;
use Noctud\Collection\Map\MutableMap;
use PHPUnit\Framework\Attributes\Test;

trait MapPut
{
    #[Test]
    public function put(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2])
            ->put('c', 3);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->put('c', 3);
            $this->assertFalse($result->changed); // value already exists with same value
            $result = $tracked->put('c', 4);
            $this->assertTrue($result->changed);

            $this->assertFalse($tracked->put('c', 4)->changed); // same value

            $tracked['b'] = 4;
            $tracked['d'] = 5;
        } else {
            $this->expectException(UnsupportedOperationException::class);
            $map['c'] = 5;

            $map = $map->put('d', 5);
        }

        $this->assertSame(4, $map->get('b'));
        $this->assertSame(4, $map->get('c'));
        $this->assertSame(5, $map->get('d'));
        $this->assertCount(4, $map);
    }

    #[Test]
    public function putFirst_new_key(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $result = $map->putFirst('c', 3);

        $this->assertSame([['c', 3], ['a', 1], ['b', 2]], $result->toPairs());

        if ($result instanceof MutableMap) {
            $this->assertSame($result, $map);
        } else {
            $this->assertNotSame($result, $map);
            $this->assertSame([['a', 1], ['b', 2]], $map->toPairs());
        }
    }

    #[Test]
    public function putFirst_moves_existing_to_front(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
        $result = $map->putFirst('c', 30);

        $this->assertSame([['c', 30], ['a', 1], ['b', 2]], $result->toPairs());
    }

    #[Test]
    public function putFirst_already_first_same_value(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->putFirst('a', 1);
            $this->assertFalse($result->changed);
            $this->assertSame([['a', 1], ['b', 2]], $map->toPairs());
        } else {
            $result = $map->putFirst('a', 1);
            $this->assertSame([['a', 1], ['b', 2]], $result->toPairs());
        }
    }

    #[Test]
    public function putFirst_already_first_different_value(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->putFirst('a', 99);
            $this->assertTrue($result->changed);
            $this->assertSame([['a', 99], ['b', 2]], $map->toPairs());
        } else {
            $result = $map->putFirst('a', 99);
            $this->assertSame([['a', 99], ['b', 2]], $result->toPairs());
        }
    }

    #[Test]
    public function putFirst_existing_not_first(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->putFirst('b', 2);
            $this->assertTrue($result->changed);
            $this->assertSame([['b', 2], ['a', 1], ['c', 3]], $map->toPairs());
        } else {
            $result = $map->putFirst('b', 2);
            $this->assertSame([['b', 2], ['a', 1], ['c', 3]], $result->toPairs());
            $this->assertSame([['a', 1], ['b', 2], ['c', 3]], $map->toPairs());
        }
    }

    #[Test]
    public function putFirst_on_empty_map(): void
    {
        $map = $this->mapOf([]);
        $result = $map->putFirst('a', 1);

        $this->assertSame([['a', 1]], $result->toPairs());
        $this->assertCount(1, $result);
    }

    #[Test]
    public function putFirst_tracked_new_key(): void
    {
        $map = $this->mapOf(['a' => 1]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->putFirst('b', 2);
            $this->assertTrue($result->changed);
            $this->assertSame([['b', 2], ['a', 1]], $map->toPairs());
        } else {
            $result = $map->putFirst('b', 2);
            $this->assertSame([['b', 2], ['a', 1]], $result->toPairs());
        }
    }

    #[Test]
    public function putIfAbsent_adds_when_key_missing(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $result = $map->putIfAbsent('c', 3);

        $this->assertSame(3, $result->get('c'));
        $this->assertCount(3, $result);

        if ($result instanceof MutableMap) {
            $this->assertSame($result, $map);
        } else {
            $this->assertNotSame($result, $map);
            $this->assertCount(2, $map);
        }
    }

    #[Test]
    public function putIfAbsent_noop_when_key_exists(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $result = $map->putIfAbsent('a', 99);

        $this->assertSame(1, $result->get('a'));
        $this->assertCount(2, $result);

        if ($result instanceof MutableMap) {
            $this->assertSame($result, $map);
        } else {
            $this->assertSame($result, $map);
        }
    }

    #[Test]
    public function putIfAbsent_tracked(): void
    {
        $map = $this->mapOf(['a' => 1]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->putIfAbsent('b', 2);
            $this->assertTrue($result->changed);

            $result = $tracked->putIfAbsent('b', 99);
            $this->assertFalse($result->changed);
            $this->assertSame(2, $map->get('b'));
        } else {
            $result = $map->putIfAbsent('b', 2);
            $this->assertSame(2, $result->get('b'));
            $this->assertNotSame($result, $map);
        }
    }

    #[Test]
    public function putIfAbsent_on_empty_map(): void
    {
        $map = $this->mapOf([]);
        $result = $map->putIfAbsent('a', 1);

        $this->assertSame(1, $result->get('a'));
        $this->assertCount(1, $result);
    }

    #[Test]
    public function putAll(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2])
            ->putAll(['c' => 3, 'd' => 4]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->putAll(['e' => 5]);
            $this->assertTrue($result->changed);
            $this->assertFalse($tracked->putAll(['a' => 1])->changed);
        }

        $this->assertSame(3, $map->get('c'));
        $this->assertSame(4, $map->get('d'));
    }

    #[Test]
    public function putAllPairs(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2])
            ->putAllPairs([['c', 3], ['d', 4]]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->putAllPairs([['e', 5]]);
            $this->assertTrue($result->changed);
            $this->assertFalse($tracked->putAllPairs([['a', 1]])->changed);
        }

        $this->assertSame(3, $map->get('c'));
        $this->assertSame(4, $map->get('d'));
    }
}
