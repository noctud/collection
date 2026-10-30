<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\Unit;

use Closure;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Tests\EnumerableLazyInit;
use Noctud\Collection\Tests\Map\Case\AbstractMapTestCase;
use function Noctud\Collection\mutableMapOf;
use function Noctud\Collection\mutableMapOfPairs;
use function is_callable;

final class MutableLazyHashMapTest extends AbstractMapTestCase
{
    use EnumerableLazyInit;

    /**
     * @template K of string|int|bool|float|object
     * @template V
     * @param iterable<K,V>|Closure():iterable<K,V> $data
     * @return MutableMap<K,V>
     */
    public function mapOf(iterable|Closure $data): MutableMap
    {
        if (!is_callable($data)) {
            return mutableMapOf(fn () => $data);
        }

        return mutableMapOf($data);
    }

    /**
     * @template K of string|int|bool|float|object
     * @template V
     * @param iterable<array{0:K,1:V}>|Closure():iterable<array{0:K,1:V}> $data
     * @return MutableMap<K,V>
     */
    public function mapOfPairs(iterable|Closure $data): MutableMap
    {
        if (!is_callable($data)) {
            return mutableMapOfPairs(fn () => $data);
        }

        return mutableMapOfPairs($data);
    }

    /**
     * @template K of string|int|bool|float|object
     * @template V
     * @param iterable<K,V>|Closure():iterable<K,V> $data
     * @return MutableMap<K,V>
     */
    public function enumerableOf(iterable|Closure $data): MutableMap
    {
        if (!is_callable($data)) {
            return mutableMapOf(fn () => $data);
        }

        return mutableMapOf($data);
    }
}
