<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\Case;

use Closure;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\MutableMap;

interface MapTestCase
{
    /**
     * @template K of string|int|bool|float|object
     * @template V
     * @param iterable<K,V>|Closure():iterable<K,V> $data
     * @return ImmutableMap<K,V>|MutableMap<K,V>
     */
    public function mapOf(iterable|Closure $data): ImmutableMap|MutableMap;

    /**
     * @template K of string|int|bool|float|object
     * @template V
     * @param iterable<array{0:K,1:V}>|Closure():iterable<array{0:K,1:V}> $data
     * @return ImmutableMap<K,V>|MutableMap<K,V>
     */
    public function mapOfPairs(iterable|Closure $data): ImmutableMap|MutableMap;
}
