<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Error;
use Noctud\Collection\Exception\UnsupportedOperationException;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\MutableMap;
use PHPUnit\Framework\Attributes\Test;

trait MapRemove
{
    #[Test]
    public function remove(): void
    {
        $map = $this->mapOf([
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => null,
            'e' => 5,
        ]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->remove('b');
            $this->assertTrue($result->changed);

            $result = $tracked->remove('x');
            $this->assertFalse($result->changed);

            unset($tracked['c']);

            $result = $tracked->removeNullValues();
            $this->assertTrue($result->changed);

            $this->assertSame([
                'a' => 1,
                'e' => 5,
            ], $map->toArray());
        } else {
            $map = $map->remove('b');
            $map = $map->remove('x');
            $this->expectException(UnsupportedOperationException::class);
            unset($map['c']);
            $map = $map->remove('c');
            $map = $map->removeNullValues();

            $this->assertSame([
                'a' => 1,
                'e' => 5,
            ], $map->toArray());
        }
    }

    #[Test]
    public function removeIf(): void
    {
        $map = $this->mapOf([
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'e' => 5,
            'f' => 6,
            'g' => 7,
            'h' => 8,
            'j' => 9,
        ]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->removeIf(fn ($v, $k) => $v === 2);
            $this->assertTrue($result->changed);

            $result = $tracked->removeIfKey(fn ($k) => $k === 'c');
            $this->assertTrue($result->changed);

            $result = $tracked->removeIfValue(fn ($v) => $v === 4);
            $this->assertTrue($result->changed);

            $result = $tracked->removeIf(fn ($v, $k) => $v > 10);
            $this->assertFalse($result->changed);

            $result = $tracked->removeIfKey(fn ($k) => $k === 'x');
            $this->assertFalse($result->changed);

            $result = $tracked->removeIfValue(fn ($v) => $v === 30);
            $this->assertFalse($result->changed);
        } else {
            $map = $map->removeIf(fn ($v, $k) => $v === 2);
            $map = $map->removeIfKey(fn ($k) => $k === 'c');
            $map = $map->removeIfValue(fn ($v) => $v === 4);
            $map = $map->removeIf(fn ($v, $k) => $v > 10);
            $map = $map->removeIfKey(fn ($k) => $k === 'x');
            $map = $map->removeIfValue(fn ($v) => $v === 30);
        }

        $this->assertSame([
            'a' => 1,
            'e' => 5,
            'f' => 6,
            'g' => 7,
            'h' => 8,
            'j' => 9,
        ], $map->toArray());
    }

    #[Test]
    public function removeFirst_removes_first_entry(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->removeFirst();
            $this->assertTrue($result->changed);
            $this->assertSame(['b' => 2, 'c' => 3], $map->toArray());
        } else {
            $new = $map->removeFirst();
            $this->assertSame(['b' => 2, 'c' => 3], $new->toArray());
            $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $map->toArray());
        }
    }

    #[Test]
    public function removeLast_removes_last_entry(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->removeLast();
            $this->assertTrue($result->changed);
            $this->assertSame(['a' => 1, 'b' => 2], $map->toArray());
        } else {
            $new = $map->removeLast();
            $this->assertSame(['a' => 1, 'b' => 2], $new->toArray());
            $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $map->toArray());
        }
    }

    #[Test]
    public function removeFirst_on_empty_is_noop(): void
    {
        $map = $this->mapOf([]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->removeFirst();
            $this->assertFalse($result->changed);
            $this->assertSame(0, $map->count());
        } else {
            $new = $map->removeFirst();
            $this->assertSame($map, $new);
        }
    }

    #[Test]
    public function removeLast_on_empty_is_noop(): void
    {
        $map = $this->mapOf([]);

        if ($map instanceof MutableMap) {
            $tracked = $map->tracked();
            $result = $tracked->removeLast();
            $this->assertFalse($result->changed);
            $this->assertSame(0, $map->count());
        } else {
            $new = $map->removeLast();
            $this->assertSame($map, $new);
        }
    }

    #[Test]
    public function clear(): void
    {
        $map = $this->mapOf([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]);

        if ($map instanceof ImmutableMap) {
            $this->expectException(Error::class);
        }

        $map->clear(); /** @phpstan-ignore-line */

        if ($map instanceof MutableMap) {
            $this->assertSame([], $map->toArray());
        } else {
            $this->assertNotSame([], $map->toArray());
        }

        // Clear an empty map
        if ($map instanceof MutableMap) {
            $map = $map->clear();
            $this->assertSame([], $map->toArray());
        }
    }
}
