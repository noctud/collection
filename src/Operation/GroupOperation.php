<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\KeyHasher;

/**
 * @internal
 * @template V
 * @extends AbstractOperation<int,V>
 */
final class GroupOperation extends AbstractOperation
{
    /**
     * Accumulates elements into one bucket per key, and hands the key and bucket arrays back:
     * wrapping them into collections is the caller's job, the way PartitionOperation
     * returns plain lists.
     *
     * Buckets are appended in place in local arrays keyed by the key hash: going through
     * the store would copy the whole bucket on every element and hash each key twice.
     *
     * @template K of string|int|bool|float|object
     * @template T
     * @param callable(V, int):K $keySelector
     * @param (callable(V, int):T)|null $valueTransform
     * @return array{array<int|string, K>, array<int|string, list<V|T>>}
     * @throws InvalidKeyTypeException If the key selector returns an unsupported map key
     */
    public function byKey(callable $keySelector, ?callable $valueTransform = null): array
    {
        $keys = [];
        $buckets = [];

        foreach ($this->data as $i => $v) {
            $key = $keySelector($v, $i);
            $hash = KeyHasher::hashMapKey($key);
            $keys[$hash] ??= $key;
            $buckets[$hash][] = $valueTransform !== null ? $valueTransform($v, $i) : $v;
        }

        return [$keys, $buckets];
    }

    /**
     * Counts elements per key without ever holding a bucket - the group sizes are all
     * groupBy()->map(count) ever needed.
     *
     * @template K of string|int|bool|float|object
     * @param callable(V, int):K $keySelector
     * @return array{array<int|string, K>, array<int|string, positive-int>}
     * @throws InvalidKeyTypeException If the key selector returns an unsupported map key
     */
    public function countByKey(callable $keySelector): array
    {
        $keys = [];
        $counts = [];

        foreach ($this->data as $i => $v) {
            $key = $keySelector($v, $i);
            $hash = KeyHasher::hashMapKey($key);
            $keys[$hash] ??= $key;
            $counts[$hash] = ($counts[$hash] ?? 0) + 1;
        }

        return [$keys, $counts];
    }
}
