<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\IntMap\Case;

use Closure;
use Noctud\Collection\Collection;
use Noctud\Collection\Map\Map;
use Noctud\Collection\Tests\EnumerableCopyOnWrite;
use PHPUnit\Framework\TestCase;
use Noctud\Collection\Tests\Map\IntMap\IntMapConvert;
use Noctud\Collection\Tests\Map\IntMap\IntMapCopyOnWrite;
use Noctud\Collection\Tests\Map\IntMap\IntMapKeyEnforcement;
use Noctud\Collection\Tests\Map\IntMap\IntMapBasics;

/**
 * Base test case for IntMap tests.
 * Uses only int-keyed associative arrays in test data.
 */
abstract class AbstractIntMapTestCase extends TestCase implements IntMapTestCase
{
    use EnumerableCopyOnWrite;
    use IntMapBasics;
    use IntMapConvert;
    use IntMapCopyOnWrite;
    use IntMapKeyEnforcement;

    /**
     * @template V
     * @param iterable<int,V>|Closure():iterable<int,V> $data
     * @return Map<int,V>
     */
    public function enumerableOf(iterable|Closure $data): Collection|Map
    {
        return $this->mapOf($data);
    }

    /**
     * @return array<int, string>
     */
    protected function generateSampleData(int $size): array
    {
        $data = [];
        for ($i = 0; $i < $size; $i++) {
            $data[$i] = "value_$i";
        }

        return $data;
    }
}
