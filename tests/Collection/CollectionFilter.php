<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\MutableCollection;
use Noctud\Collection\Tests\Collection\Fixture\Cat;
use Noctud\Collection\Tests\Collection\Fixture\Dog;
use Noctud\Collection\Tests\Collection\Fixture\Walkable;
use PHPUnit\Framework\Attributes\Test;

trait CollectionFilter
{
    #[Test]
    public function filter_by_predicate(): void
    {
        $collection = $this->collectionOf([1, 2, 3, 4, 5, 6]);
        $filtered = $collection->filter(fn ($v) => $v % 2 === 0);

        $this->assertSame([2, 4, 6], $filtered->toArray());

        if ($collection instanceof MutableCollection) {
            $this->assertNotSame($collection, $filtered);
        }
    }

    #[Test]
    public function filter_returns_empty_when_none_match(): void
    {
        $collection = $this->collectionOf([1, 3, 5]);
        $filtered = $collection->filter(fn ($v) => $v % 2 === 0);
        $this->assertSame([], $filtered->toArray());
    }

    #[Test]
    public function filter_on_empty(): void
    {
        $collection = $this->collectionOf([]);
        $filtered = $collection->filter(fn ($v) => true);
        $this->assertSame([], $filtered->toArray());
    }

    #[Test]
    public function filterNotNull(): void
    {
        $collection = $this->collectionOf([1, null, 2, null, 3]);
        $filtered = $collection->filterNotNull();
        $this->assertSame([1, 2, 3], $filtered->toArray());
    }

    #[Test]
    public function filterNotNull_on_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertSame([], $collection->filterNotNull()->toArray());
    }

    #[Test]
    public function filterNotNull_when_all_null(): void
    {
        $collection = $this->collectionOf([null, null, null]);
        $this->assertSame([], $collection->filterNotNull()->toArray());
    }

    #[Test]
    public function filterNotNull_preserves_falsy_values(): void
    {
        $collection = $this->collectionOf([0, false, '', null, []]);
        $filtered = $collection->filterNotNull();
        $this->assertSame([0, false, '', []], $filtered->toArray());
    }

    #[Test]
    public function filterInstanceOf_filters_by_class(): void
    {
        $dog1 = new Dog('Rex');
        $dog2 = new Dog('Buddy');
        $cat = new Cat('Whiskers');

        $collection = $this->collectionOf([$dog1, $cat, $dog2]);
        $filtered = $collection->filterInstanceOf(Dog::class);

        $this->assertCount(2, $filtered);
        $this->assertSame([$dog1, $dog2], $filtered->toArray());
    }

    #[Test]
    public function filterInstanceOf_with_interface(): void
    {
        $dog = new Dog('Rex');
        $cat = new Cat('Whiskers');

        $collection = $this->collectionOf([$dog, $cat]);
        $filtered = $collection->filterInstanceOf(Walkable::class);

        $this->assertCount(1, $filtered);
        $this->assertSame([$dog], $filtered->toArray());
    }

    #[Test]
    public function filterInstanceOf_returns_empty_when_no_match(): void
    {
        $cat = new Cat('Whiskers');
        $collection = $this->collectionOf([$cat]);
        $filtered = $collection->filterInstanceOf(Dog::class);

        $this->assertSame([], $filtered->toArray());
    }

    #[Test]
    public function filterInstanceOf_on_empty_collection(): void
    {
        $collection = $this->collectionOf([]);
        $filtered = $collection->filterInstanceOf(Dog::class);

        $this->assertSame([], $filtered->toArray());
    }
}
