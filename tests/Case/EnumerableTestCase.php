<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Case;

use Closure;
use Noctud\Collection\Collection;
use Noctud\Collection\Map\Map;

interface EnumerableTestCase
{
    /**
     * @template K of string|int|bool|float|object
     * @template V
     * @param iterable<K,V>|Closure():iterable<K,V> $data
     * @return Collection<V>|Map<K,V>
     */
    public function enumerableOf(iterable|Closure $data): Collection|Map;
}
