<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Case;

use Closure;
use Noctud\Collection\Collection;

interface CollectionTestCase
{
    /**
     * @template E
     * @param iterable<E>|Closure():iterable<E> $data
     * @return Collection<E>
     */
    public function collectionOf(iterable|Closure $data): Collection;
}
