<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests;

use Noctud\Collection\Set\Set;
use PHPUnit\Framework\Attributes\Test;
use function count;

trait EnumerableCount
{
    #[Test]
    public function count_of_items(): void
    {
        $empty = $this->enumerableOf([]);
        $this->assertSame([0, 0], [$empty->count(), count($empty)]);
        $this->assertTrue($empty->isEmpty());

        $one = $this->enumerableOf(['a']);
        $this->assertSame([1, 1], [$one->count(), count($one)]);
        $this->assertTrue($one->isNotEmpty());

        $multiple = $this->enumerableOf(['a', 'b', 'a']);
        if ($multiple instanceof Set) {
            $this->assertSame([2, 2], [$multiple->count(), count($multiple)]);
        } else {
            $this->assertSame([3, 3], [$multiple->count(), count($multiple)]);
        }
        $this->assertTrue($multiple->isNotEmpty());
    }
}
