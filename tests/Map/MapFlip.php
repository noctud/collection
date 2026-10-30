<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\KeyCollisionStrategy;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

trait MapFlip
{
    #[Test]
    public function flip_basic(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $flipped = $map->flip();

        $this->assertSame('a', $flipped->get(1));
        $this->assertSame('b', $flipped->get(2));
        $this->assertSame(2, $flipped->count());
    }

    #[Test]
    public function flip_empty(): void
    {
        $map = $this->mapOf([]);
        $flipped = $map->flip();

        $this->assertSame(0, $flipped->count());
        $this->assertTrue($flipped->isEmpty());
    }

    #[Test]
    public function flip_throws_on_duplicate_values(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 1]);

        $this->expectException(ConversionException::class);
        $result = $map->flip(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function flip_keep_first(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 1]);
        $flipped = $map->flip(KeyCollisionStrategy::KeepFirst);

        $this->assertSame(1, $flipped->count());
        $this->assertSame('a', $flipped->get(1));
    }

    #[Test]
    public function flip_keep_last(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 1]);
        $flipped = $map->flip(KeyCollisionStrategy::KeepLast);

        $this->assertSame(1, $flipped->count());
        $this->assertSame('b', $flipped->get(1));
    }

    #[Test]
    public function flip_always_returns_immutable(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $flipped = $map->flip();

        // Transformation methods always return ImmutableMap
        $this->assertInstanceOf(ImmutableMap::class, $flipped);
    }

    #[Test]
    public function flip_with_object_values_as_keys(): void
    {
        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $map = $this->mapOf(['a' => $obj1, 'b' => $obj2]);
        $flipped = $map->flip();

        $this->assertSame('a', $flipped->get($obj1));
        $this->assertSame('b', $flipped->get($obj2));
    }

    #[Test]
    public function flip_with_null_value_throws(): void
    {
        $map = $this->mapOf(['a' => null]);

        $this->expectException(InvalidKeyTypeException::class);
        $result = $map->flip(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function flip_twice_returns_original(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);
        $flippedTwice = $map->flip()->flip();

        $this->assertSame($map->toArray(), $flippedTwice->toArray());
    }
}
