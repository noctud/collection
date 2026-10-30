<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

use Noctud\Collection\Store\KeyValueStore;

/**
 * @internal
 * @template K of string|int|bool|float|object
 * @template V
 */
abstract class AbstractKeyValueOperation
{
    /**
     * @param KeyValueStore<K,V> $data
     */
    public function __construct(
        protected KeyValueStore $data,
    ) {}
}
