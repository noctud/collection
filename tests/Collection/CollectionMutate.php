<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\List\MutableList;
use Noctud\Collection\MutableCollection;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\MutableSet;
use Noctud\Collection\Set\Set as SetInterface;
use PHPUnit\Framework\Attributes\Test;

trait CollectionMutate
{
    #[Test]
    public function clear_collection(): void
    {
        $collection = $this->collectionOf([1, 2, 3]);

        if ($collection instanceof MutableCollection) {
            $result = $collection->clear();
            $this->assertSame($collection, $result);
            $this->assertSame([], $collection->toArray());
            $this->assertTrue($collection->isEmpty());
        } else {
            $this->assertTrue(true); /** @phpstan-ignore-line **/
        }
    }

    #[Test]
    public function clear_empty_collection(): void
    {
        $collection = $this->collectionOf([]);

        if ($collection instanceof MutableCollection) {
            $tracked = $collection->tracked();
            $result = $tracked->clear();
            $this->assertFalse($result->changed);
            $this->assertSame([], $collection->toArray());
        } else {
            $this->assertTrue(true); /** @phpstan-ignore-line **/
        }
    }

    #[Test]
    public function reverse_in_place(): void
    {
        $collection = $this->collectionOf([1, 2, 3]);

        if ($collection instanceof MutableCollection) {
            $result = $collection->reverse();
            $this->assertSame($collection, $result);
            $this->assertSame([3, 2, 1], $collection->toArray());

            $collection2 = $this->collectionOf([1, 2, 3]);
            if ($collection2 instanceof MutableCollection) {
                $tracked = $collection2->tracked();
                $result = $tracked->reverse();
                $this->assertTrue($result->changed);
                $this->assertSame([3, 2, 1], $collection2->toArray());

                $single = $this->collectionOf([1]);
                if ($single instanceof MutableCollection) {
                    $tracked2 = $single->tracked();
                    $this->assertFalse($tracked2->reverse()->changed);
                }
            }
        } else {
            $this->assertTrue(true); /** @phpstan-ignore-line **/
        }
    }

    #[Test]
    public function toMutable_creates_mutable_copy(): void
    {
        $collection = $this->collectionOf([1, 2, 3]);
        $mutable = $collection->toMutable();

        if ($collection instanceof SetInterface) {
            $this->assertInstanceOf(MutableSet::class, $mutable);
        } else {
            $this->assertInstanceOf(MutableList::class, $mutable);
        }

        $this->assertSame([1, 2, 3], $mutable->toArray());
    }

    #[Test]
    public function toImmutable_creates_immutable_copy(): void
    {
        $collection = $this->collectionOf([1, 2, 3]);
        $immutable = $collection->toImmutable();

        if ($collection instanceof SetInterface) {
            $this->assertInstanceOf(ImmutableSet::class, $immutable);
        } else {
            $this->assertInstanceOf(ImmutableList::class, $immutable);
        }

        $this->assertSame([1, 2, 3], $immutable->toArray());
    }
}
