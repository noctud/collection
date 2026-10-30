<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\StringMap\Case;

use Closure;
use Noctud\Collection\Collection;
use Noctud\Collection\Map\Map;
use Noctud\Collection\Tests\EnumerableCopyOnWrite;
use PHPUnit\Framework\TestCase;
use Noctud\Collection\Tests\Map\StringMap\StringMapConvert;
use Noctud\Collection\Tests\Map\StringMap\StringMapCopyOnWrite;
use Noctud\Collection\Tests\Map\StringMap\StringMapKeyEnforcement;
use Noctud\Collection\Tests\Map\StringMap\StringMapBasics;

/**
 * Base test case for StringMap tests.
 * Uses only string-keyed associative arrays in test data.
 */
abstract class AbstractStringMapTestCase extends TestCase implements StringMapTestCase
{
    use EnumerableCopyOnWrite;
    use StringMapBasics;
    use StringMapConvert;
    use StringMapCopyOnWrite;
    use StringMapKeyEnforcement;

    /**
     * @template V
     * @param iterable<string,V>|Closure():iterable<string,V> $data
     * @return Map<string,V>
     */
    public function enumerableOf(iterable|Closure $data): Collection|Map
    {
        return $this->mapOf($data);
    }

    /**
     * @return array<string, string>
     */
    protected function generateSampleData(int $size): array
    {
        $data = [];
        for ($i = 0; $i < $size; $i++) {
            $data["key_$i"] = "value_$i";
        }

        return $data;
    }
}
