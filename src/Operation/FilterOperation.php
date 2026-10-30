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
final class FilterOperation extends AbstractOperation
{
    /**
     * @param callable(V,K):bool $predicate
     * @return Generator<K,V>
     */
    public function byPredicate(callable $predicate): Generator
    {
        foreach ($this->data as $k => $v) {
            if ($predicate($v, $k)) {
                yield $k => $v;
            }
        }
    }

    /**
     * @param callable(K):bool $predicate
     * @return Generator<K,V>
     */
    public function byKey(callable $predicate): Generator
    {
        return $this->byPredicate(fn ($v, $k) => $predicate($k));
    }

    /**
     * @param callable(V):bool $predicate
     * @return Generator<K,V>
     */
    public function byValue(callable $predicate): Generator
    {
        return $this->byPredicate(fn ($v, $k) => $predicate($v));
    }

    /**
     * @return Generator<K,V>
     */
    public function notNullValues(): Generator
    {
        return $this->byPredicate(fn ($v, $k) => $v !== null);
    }
}
