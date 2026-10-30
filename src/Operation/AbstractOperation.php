<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

/**
 * @internal
 * @template K
 * @template V
 */
abstract class AbstractOperation
{
    /**
     * @param iterable<K,V> $data
     */
    public function __construct(
        protected iterable $data,
    ) {}
}
