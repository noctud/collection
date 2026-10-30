<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

use Generator;

/**
 * @internal
 * @template K of string|int|bool|float|object
 * @template V
 * @extends AbstractOperation<K,V>
 */
final class FlatMapKeyValueOperation extends AbstractOperation
{
    /**
     * @template R
     * @param callable(V,K):iterable<R> $transform
     * @return Generator<R>
     */
    public function items(callable $transform): Generator
    {
        foreach ($this->data as $k => $v) {
            yield from $transform($v, $k);
        }
    }
}
