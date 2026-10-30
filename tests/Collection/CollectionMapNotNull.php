<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\Set\Set as SetInterface;
use PHPUnit\Framework\Attributes\Test;
use function strtoupper;

trait CollectionMapNotNull
{
    #[Test]
    public function mapNotNull_excludes_null_results(): void
    {
        $collection = $this->collectionOf([1, 2, 3, 4, 5]);
        $result = $collection->mapNotNull(fn ($v) => $v > 2 ? $v * 10 : null);
        $this->assertSame([30, 40, 50], $result->toArray());
    }

    #[Test]
    public function mapNotNull_on_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertSame([], $collection->mapNotNull(fn ($v) => $v)->toArray());
    }

    #[Test]
    public function mapNotNull_all_null(): void
    {
        $collection = $this->collectionOf([1, 2, 3]);
        $this->assertSame([], $collection->mapNotNull(fn ($v) => null)->toArray());
    }

    #[Test]
    public function mapNotNull_no_nulls(): void
    {
        $collection = $this->collectionOf([1, 2, 3]);
        $result = $collection->mapNotNull(fn ($v) => $v * 2);
        $this->assertSame([2, 4, 6], $result->toArray());
    }

    #[Test]
    public function mapNotNull_with_nullable_property(): void
    {
        $collection = $this->collectionOf(['rodney', null, 'teyla', null, 'sheppard']);
        $result = $collection->mapNotNull(fn ($v) => $v !== null ? strtoupper($v) : null);
        $this->assertSame(['RODNEY', 'TEYLA', 'SHEPPARD'], $result->toArray());
    }

    #[Test]
    public function mapNotNull_receives_index(): void
    {
        $collection = $this->collectionOf(['a', 'b', 'c']);
        $result = $collection->mapNotNull(fn ($v, $i) => $i > 0 ? "$i:$v" : null);

        if ($collection instanceof SetInterface) {
            $this->assertSame(['1:b', '2:c'], $result->toArray());
        } else {
            $this->assertSame(['1:b', '2:c'], $result->toArray());
        }
    }

    #[Test]
    public function mapNotNull_with_zero_and_empty_string(): void
    {
        $collection = $this->collectionOf([1, 2, 3]);
        // 0 and '' are NOT null, should be kept
        $result = $collection->mapNotNull(fn ($v) => $v === 2 ? 0 : ($v === 3 ? '' : $v));
        $this->assertSame([1, 0, ''], $result->toArray());
    }
}
