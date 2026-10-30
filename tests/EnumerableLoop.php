<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests;

use Noctud\Collection\Tests\Case\EnumerableTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @mixin EnumerableTestCase
 */
trait EnumerableLoop
{
    #[Test]
    public function loop(): void
    {
        $empty = $this->enumerableOf([]);
        foreach ($empty as $element) {
            $this->fail('Loop executed on empty collection');
        }

        $one = $this->enumerableOf(['a']);
        foreach ($one as $k => $v) {
            $this->assertSame(0, $k);
            $this->assertSame('a', $v);
        }

        $multiple = $this->enumerableOf(['a', 'b', 'c']);
        $expected = ['a', 'b', 'c'];
        foreach ($multiple as $k => $v) {
            $this->assertSame($expected[$k], $v);
        }
    }
}
