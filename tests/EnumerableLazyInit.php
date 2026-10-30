<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests;

use Generator;
use LogicException;
use Noctud\Collection\Tests\Collection\List\Unit\ImmutableLazyListTest;
use Noctud\Collection\Tests\Collection\List\Unit\MutableLazyListTest;
use Noctud\Collection\Tests\Collection\Set\Unit\ImmutableLazySetTest;
use Noctud\Collection\Tests\Collection\Set\Unit\MutableLazySetTest;
use Noctud\Collection\Tests\Map\Unit\ImmutableLazyHashMapTest;
use Noctud\Collection\Tests\Map\Unit\MutableLazyHashMapTest;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\listOf;
use function Noctud\Collection\mapOf;
use function Noctud\Collection\mutableListOf;
use function Noctud\Collection\mutableMapOf;
use function Noctud\Collection\mutableSetOf;
use function Noctud\Collection\setOf;

trait EnumerableLazyInit
{
    #[Test]
    public function lazy_init_from_array(): void
    {
        $materialized = false;
        $collection = $this->enumerableOf(function () use (&$materialized) {
            $materialized = true;
            return [
                1 => 'a',
                2 => 'b',
                3 => 'c',
            ];
        });

        $this->assertFalse($materialized);
        $this->assertSame(3, $collection->count());

        /** @phpstan-ignore-next-line */
        $this->assertTrue($materialized);
    }

    #[Test]
    public function generator_is_lazy_only_inside_function(): void
    {
        $materialized = false;

        $functionThatProducesTheGenerator = function () use (&$materialized): Generator {
            $materialized = true;
            yield 1 => 'a';
            yield 2 => 'b';
            yield 3 => 'c';
        };

        // Correct way to construct a lazy map
        $collection = $this->enumerableOf($functionThatProducesTheGenerator);

        $this->assertFalse($materialized);
        $this->assertSame(3, $collection->count());

        /** @phpstan-ignore-next-line */
        $this->assertTrue($materialized);

        // Generator is iterable, unpacked immediately when Generator is iterated
        $materialized = false;
        $iterable = $functionThatProducesTheGenerator();

        /** @phpstan-ignore-next-line */
        $this->assertFalse($materialized); // Note that the function internal code was not called yet

        // We need to use custom constructors here, because $this->collectionOf would wrap it in callable again
        if ($this instanceof ImmutableLazyHashMapTest) { /** @phpstan-ignore-line */
            $collection = mapOf($iterable);
        } elseif ($this instanceof MutableLazyHashMapTest) { /** @phpstan-ignore-line */
            $collection = mutableMapOf($iterable);
        } elseif ($this instanceof ImmutableLazyListTest) { /** @phpstan-ignore-line */
            $collection = listOf($iterable);
        } elseif ($this instanceof MutableLazyListTest) { /** @phpstan-ignore-line */
            $collection = mutableListOf($iterable);
        } elseif ($this instanceof ImmutableLazySetTest) { /** @phpstan-ignore-line */
            $collection = setOf($iterable);
        } elseif ($this instanceof MutableLazySetTest) { /** @phpstan-ignore-line */
            $collection = mutableSetOf($iterable);
        } else {
            throw new LogicException('Unknown collection type');
        }

        /** @phpstan-ignore-next-line */
        $this->assertTrue($materialized);
        $this->assertSame(3, $collection->count());
    }
}
