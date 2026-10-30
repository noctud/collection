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
final class ZipWithNextOperation extends AbstractOperation
{
    /**
     * @return Generator<array{V, V}>
     */
    public function pairs(): Generator
    {
        $previous = null;
        $hasPrevious = false;

        foreach ($this->data as $v) {
            if ($hasPrevious) {
                yield [$previous, $v];
            }

            $previous = $v;
            $hasPrevious = true;
        }
    }
}
