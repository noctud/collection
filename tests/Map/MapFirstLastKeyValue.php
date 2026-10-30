<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Noctud\Collection\Exception\NoSuchElementException;
use PHPUnit\Framework\Attributes\Test;

trait MapFirstLastKeyValue
{
    #[Test]
    public function first_last_key_value(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(['a', 'a'], [$map->keys->first(), $map->keys->firstOrNull()]);
        $this->assertSame(['c', 'c'], [$map->keys->last(), $map->keys->lastOrNull()]);
        $this->assertSame([1, 1], [$map->values->first(), $map->values->firstOrNull()]);
        $this->assertSame([3, 3], [$map->values->last(), $map->values->lastOrNull()]);
    }

    #[Test]
    public function random_key_value_on_empty_map(): void
    {
        $map = $this->mapOf([]);

        $this->expectException(NoSuchElementException::class);
        $map->entries->random();
    }

    #[Test]
    public function first_last_key_value_on_empty_map(): void
    {
        $map = $this->mapOf([]);

        try {
            $map->keys->first();
            $this->assertFalse(true, 'Expected NoSuchElementException not thrown for firstKey'); /** @phpstan-ignore-line **/
        } catch (NoSuchElementException) {
            // Expected exception, test passes
        }

        try {
            $map->keys->last();
            $this->assertFalse(true, 'Expected NoSuchElementException not thrown for lastKey'); /** @phpstan-ignore-line **/
        } catch (NoSuchElementException) {
            // Expected exception, test passes
        }

        try {
            $map->values->first();
            $this->assertFalse(true, 'Expected NoSuchElementException not thrown for firstValue'); /** @phpstan-ignore-line **/
        } catch (NoSuchElementException) {
            // Expected exception, test passes
        }

        try {
            $map->values->last();
            $this->assertFalse(true, 'Expected NoSuchElementException not thrown for lastValue'); /** @phpstan-ignore-line **/
        } catch (NoSuchElementException) {
            // Expected exception, test passes
        }

        $this->assertSame([null, null, null, null], [$map->keys->firstOrNull(), $map->keys->lastOrNull(), $map->values->firstOrNull(), $map->values->lastOrNull()]);
    }
}
