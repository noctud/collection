<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Noctud\Collection\Map\MutableMap;
use PHPUnit\Framework\Attributes\Test;

trait MapInstance
{
    #[Test]
    public function instances(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        if ($map instanceof MutableMap) {
            $this->assertSame($map, $map->put('c', 3));
            $this->assertSame($map, $map->putAll(['d' => 4, 'e' => 5]));
            $this->assertSame($map, $map->putAllPairs([['f', 6], ['g', 7]]));

            $this->assertSame($map, $map->remove('a'));
            $this->assertSame($map, $map->removeIf(fn ($v, $k) => $v === 0));
            $this->assertSame($map, $map->removeIfKey(fn ($k) => $k === '0'));
            $this->assertSame($map, $map->removeIfValue(fn ($v) => $v === 0));
            $this->assertSame($map, $map->removeNullValues());

            $this->assertSame($map, $map->sortBy(fn ($v, $k) => $v));
            $this->assertSame($map, $map->sortByKey());
            $this->assertSame($map, $map->sortByKeyDesc());
            $this->assertSame($map, $map->sortByValue());
            $this->assertSame($map, $map->sortByValueDesc());

            $this->assertSame($map, $map->clear());
        } else {
            $this->assertNotSame($map, $map->put('c', 3));
            $this->assertNotSame($map, $map->putAll(['d' => 4, 'e' => 5]));
            $this->assertNotSame($map, $map->putAllPairs([['f', 6], ['g', 7]]));

            $this->assertNotSame($map, $map->remove('a'));
            $this->assertNotSame($map, $map->removeIf(fn ($v, $k) => $v === 0));
            $this->assertNotSame($map, $map->removeIfKey(fn ($k) => $k === '0'));
            $this->assertNotSame($map, $map->removeIfValue(fn ($v) => $v === 0));
            $this->assertNotSame($map, $map->removeNullValues());
        }
    }
}
