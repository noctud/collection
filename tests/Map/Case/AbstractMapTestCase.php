<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\Case;

use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Tests\Case\AbstractEnumerableTestCase;
use Noctud\Collection\Tests\Map\MapContainsAndGet;
use Noctud\Collection\Tests\Map\MapCount;
use Noctud\Collection\Tests\Map\MapFilter;
use Noctud\Collection\Tests\Map\MapFirstLastKeyValue;
use Noctud\Collection\Tests\Map\MapFlatMap;
use Noctud\Collection\Tests\Map\MapLoop;
use Noctud\Collection\Tests\Map\MapInstance;
use Noctud\Collection\Tests\Map\MapKeyPreservation;
use Noctud\Collection\Tests\Map\MapKeysValuesEntries;
use Noctud\Collection\Tests\Map\MapMap;
use Noctud\Collection\Tests\Map\MapMapNotNull;
use Noctud\Collection\Tests\Map\MapPut;
use Noctud\Collection\Tests\Map\MapQuantifiers;
use Noctud\Collection\Tests\Map\MapRemove;
use Noctud\Collection\Tests\Map\MapOrdering;
use Noctud\Collection\Tests\Map\MapSlicing;
use Noctud\Collection\Tests\Map\MapConvert;
use Noctud\Collection\Tests\Map\MapFlip;

abstract class AbstractMapTestCase extends AbstractEnumerableTestCase implements MapTestCase
{
    /**
     * Narrows a map union type to MutableMap while preserving generic type parameters.
     *
     * PHPStan loses generic types when narrowing `ImmutableMap<K,V>|MutableMap<K,V>` via instanceof.
     * Using a helper function with template annotations preserves the types through the call.
     *
     * @template TK of string|int|bool|float|object
     * @template TV
     * @param ImmutableMap<TK,TV>|MutableMap<TK,TV> $map
     * @return MutableMap<TK,TV>|null
     */
    protected static function asMutableMap(ImmutableMap|MutableMap $map): ?MutableMap
    {
        return $map instanceof MutableMap ? $map : null;
    }

    // basics
    use MapContainsAndGet;
    use MapCount;
    use MapPut;
    use MapRemove;
    use MapConvert;
    use MapFlip;

    // filters
    use MapFilter;
    use MapFlatMap;
    use MapLoop;
    use MapKeysValuesEntries;
    use MapMap;
    use MapMapNotNull;
    use MapQuantifiers;
    use MapOrdering;
    use MapSlicing;

    // properties
    use MapKeyPreservation;
    use MapInstance;
    use MapFirstLastKeyValue;

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
