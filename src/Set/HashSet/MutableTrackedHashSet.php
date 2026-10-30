<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set\HashSet;

use Noctud\Collection\Set\MutableTrackedSetLogic;
use Noctud\Collection\Set\MutableTrackedSet;
use Noctud\Collection\Store\ReadWriteElementStore;
use Noctud\Collection\TrackedResult;
use NoDiscard;

/**
 * Mutable tracked hash set that tracks whether the last mutation changed the set.
 * Shares the underlying store with a MutableHashSet.
 *
 * @template E
 * @implements MutableTrackedSet<E>
 * @property ReadWriteElementStore<E> $store
 */
class MutableTrackedHashSet implements MutableTrackedSet, TrackedResult
{
    /** @use MutableTrackedSetLogic<E> */
    use MutableTrackedSetLogic;

    /**
     * @param ReadWriteElementStore<E> $store
     */
    public function __construct(ReadWriteElementStore $store)
    {
        $this->store = $store;
    }

    /**
     * Returns this tracked set (already tracked).
     *
     * @return MutableTrackedSet<E>
     */
    #[NoDiscard]
    public function tracked(): MutableTrackedSet
    {
        return $this;
    }
}
