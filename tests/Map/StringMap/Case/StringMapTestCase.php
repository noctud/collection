<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\StringMap\Case;

use Closure;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\MutableMap;

interface StringMapTestCase
{
    /**
     * @template V
     * @param iterable<string,V>|Closure():iterable<string,V> $data
     * @return ImmutableMap<string,V>|MutableMap<string,V>
     */
    public function mapOf(iterable|Closure $data): ImmutableMap|MutableMap;
}
