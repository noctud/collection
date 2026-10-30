<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\IntMap\Case;

use Closure;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\MutableMap;

interface IntMapTestCase
{
    /**
     * @template V
     * @param iterable<int,V>|Closure():iterable<int,V> $data
     * @return ImmutableMap<int,V>|MutableMap<int,V>
     */
    public function mapOf(iterable|Closure $data): ImmutableMap|MutableMap;
}
