<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use PHPUnit\Framework\Attributes\Test;

trait CollectionQuantifiers
{
    #[Test]
    public function all_returns_true_when_all_match(): void
    {
        $collection = $this->collectionOf([2, 4, 6, 8]);
        $this->assertTrue($collection->all(fn ($v) => $v % 2 === 0));
    }

    #[Test]
    public function all_returns_false_when_one_does_not_match(): void
    {
        $collection = $this->collectionOf([2, 4, 5, 8]);
        $this->assertFalse($collection->all(fn ($v) => $v % 2 === 0));
    }

    #[Test]
    public function all_returns_true_for_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertTrue($collection->all(fn ($v) => false));
    }

    #[Test]
    public function any_returns_true_when_one_matches(): void
    {
        $collection = $this->collectionOf([1, 3, 4, 7]);
        $this->assertTrue($collection->any(fn ($v) => $v % 2 === 0));
    }

    #[Test]
    public function any_returns_false_when_none_match(): void
    {
        $collection = $this->collectionOf([1, 3, 5, 7]);
        $this->assertFalse($collection->any(fn ($v) => $v % 2 === 0));
    }

    #[Test]
    public function any_returns_false_for_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertFalse($collection->any(fn ($v) => true));
    }

    #[Test]
    public function none_returns_true_when_none_match(): void
    {
        $collection = $this->collectionOf([1, 3, 5]);
        $this->assertTrue($collection->none(fn ($v) => $v % 2 === 0));
    }

    #[Test]
    public function none_returns_false_when_one_matches(): void
    {
        $collection = $this->collectionOf([1, 2, 3]);
        $this->assertFalse($collection->none(fn ($v) => $v % 2 === 0));
    }

    #[Test]
    public function none_returns_true_for_empty(): void
    {
        $collection = $this->collectionOf([]);
        $this->assertTrue($collection->none(fn ($v) => true));
    }
}
