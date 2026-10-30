<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Case;

use Noctud\Collection\Tests\EnumerableCopyOnWrite;
use Noctud\Collection\Tests\EnumerableCount;
use Noctud\Collection\Tests\EnumerableLoop;
use PHPUnit\Framework\TestCase;

abstract class AbstractEnumerableTestCase extends TestCase
{
    use EnumerableCopyOnWrite;
    use EnumerableCount;
    use EnumerableLoop;

    /**
     * @return array<int|string, string>
     */
    protected function generateSampleData(int $size): array
    {
        $data = [];
        for ($i = 0; $i < $size; $i++) {
            $data[] = "value_$i";
        }

        return $data;
    }
}
