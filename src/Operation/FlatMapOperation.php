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
 * @template V
 * @extends AbstractOperation<int,V>
 */
final class FlatMapOperation extends AbstractOperation
{
    /**
     * @template NV
     * @param callable(V):iterable<NV> $transform
     * @return Generator<NV>
     */
    public function items(callable $transform): Generator
    {
        foreach ($this->data as $v) {
            foreach ($transform($v) as $x) {
                yield $x;
            }
        }
    }
}
