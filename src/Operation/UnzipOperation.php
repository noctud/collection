<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

use Noctud\Collection\Exception\UnsupportedOperationException;
use function is_array;
use function array_key_exists;

/**
 * @internal
 * @template V
 * @extends AbstractOperation<int,V>
 */
final class UnzipOperation extends AbstractOperation
{
    /**
     * Splits a source of pairs into two plain lists, the inverse of ZipOperation.
     *
     * @return array{list<mixed>, list<mixed>}
     * @throws UnsupportedOperationException When an element is not a pair.
     */
    public function pairs(): array
    {
        $first = [];
        $second = [];

        foreach ($this->data as $pair) {
            if (!is_array($pair)) {
                throw new UnsupportedOperationException('unzip() requires a collection of pairs (arrays with indices 0 and 1)');
            }

            $a = $pair[0] ?? null;
            $b = $pair[1] ?? null;

            if ($a === null && !array_key_exists(0, $pair) || $b === null && !array_key_exists(1, $pair)) {
                throw new UnsupportedOperationException('unzip() requires a collection of pairs (arrays with indices 0 and 1)');
            }

            $first[] = $a;
            $second[] = $b;
        }

        return [$first, $second];
    }
}
