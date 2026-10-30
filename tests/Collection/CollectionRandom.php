<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use Noctud\Collection\Exception\NoSuchElementException;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use function Noctud\Collection\mutableSetOf;
use function Noctud\Collection\setOf;

trait CollectionRandom
{
    #[Test]
    public function random(): void
    {
        $empty = $this->collectionOf([]);

        try {
            $empty->random();
            $this->fail('Expected NoSuchElementException not thrown'); /* @phpstan-ignore-line */
        } catch (NoSuchElementException) {
            // Expected exception, test passes
        }

        $this->assertEquals(null, $empty->randomOrNull());

        $one = $this->collectionOf(['a']);
        $this->assertSame('a', $one->random());
        $this->assertSame('a', $one->randomOrNull());

        $object = new stdClass();
        $multiple = $this->collectionOf(['a', 'b', 'a', $object, null, false, 1]);

        // Test that all values can be returned
        foreach (setOf($multiple) as $expected) {
            do {
                $random = $multiple->random();
            } while ($random !== $expected);

            do {
                $randomOrNull = $multiple->randomOrNull();
            } while ($randomOrNull !== $expected);

            $this->assertSame($expected, $random);
            $this->assertSame($expected, $randomOrNull);
        }

        $mutSet = mutableSetOf();
        $result = $mutSet->reverse()->reverse()->reverse()->toList();
        $this->assertSame([], $result->toArray());
    }
}
