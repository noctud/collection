<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\Exception\NoSuchElementException;
use PHPUnit\Framework\Attributes\Test;

trait CollectionSingle
{
    #[Test]
    public function single_returns_only_element(): void
    {
        $collection = $this->collectionOf([42]);
        $this->assertSame(42, $collection->single());
    }

    #[Test]
    public function single_throws_on_empty(): void
    {
        $collection = $this->collectionOf([]);

        $this->expectException(NoSuchElementException::class);
        $this->expectExceptionMessageIsOrContains('Collection is empty');
        $collection->single();
    }

    #[Test]
    public function single_throws_on_multiple(): void
    {
        $collection = $this->collectionOf([1, 2]);

        $this->expectException(NoSuchElementException::class);
        $this->expectExceptionMessageIsOrContains('Collection contains more than one element');
        $collection->single();
    }

    #[Test]
    public function singleOrNull_returns_element(): void
    {
        $collection = $this->collectionOf([42]);
        $this->assertSame(42, $collection->singleOrNull());
    }

    #[Test]
    public function singleOrNull_returns_null_on_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertNull($collection->singleOrNull());
    }

    #[Test]
    public function singleOrNull_returns_null_on_multiple(): void
    {
        $collection = $this->collectionOf([1, 2]);
        $this->assertNull($collection->singleOrNull());
    }
}
