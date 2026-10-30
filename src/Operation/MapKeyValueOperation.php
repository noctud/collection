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
final class MapKeyValueOperation extends AbstractOperation
{
    /**
     * @template NV of string|int|bool|float|object
     * @param callable(V,K):NV $transform
     * @return Generator<NV,V>
     */
    public function keys(callable $transform): Generator
    {
        foreach ($this->data as $k => $v) {
            yield $transform($v, $k) => $v;
        }
    }

    /**
     * @template NV
     * @param callable(V,K):NV $transform
     * @return Generator<K,NV>
     */
    public function values(callable $transform): Generator
    {
        foreach ($this->data as $k => $v) {
            yield $k => $transform($v, $k);
        }
    }

    /**
     * @template NV
     * @param callable(V,K):(NV|null) $transform
     * @return Generator<K,NV>
     */
    public function valuesNotNull(callable $transform): Generator
    {
        foreach ($this->data as $k => $v) {
            $mapped = $transform($v, $k);
            if ($mapped !== null) {
                yield $k => $mapped;
            }
        }
    }

    /**
     * @template NV
     * @param callable(V,K):NV $callback
     * @return Generator<NV>
     */
    public function items(callable $callback): Generator
    {
        foreach ($this->data as $k => $v) {
            yield $callback($v, $k);
        }
    }

    /**
     * @template NV
     * @param callable(V,K):(NV|null) $callback
     * @return Generator<NV>
     */
    public function itemsNotNull(callable $callback): Generator
    {
        foreach ($this->data as $k => $v) {
            $mapped = $callback($v, $k);
            if ($mapped !== null) {
                yield $mapped;
            }
        }
    }
}
