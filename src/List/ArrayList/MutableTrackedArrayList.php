<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List\ArrayList;

use Noctud\Collection\List\MutableTrackedListLogic;
use Noctud\Collection\List\MutableTrackedList;
use Noctud\Collection\Store\ReadWriteIndexedStore;
use Noctud\Collection\TrackedResult;
use NoDiscard;

/**
 * Mutable tracked array list that tracks whether the last mutation changed the list.
 * Shares the underlying store with a MutableArrayList.
 *
 * @template E
 * @implements MutableTrackedList<E>
 * @property ReadWriteIndexedStore<E> $store
 */
class MutableTrackedArrayList implements MutableTrackedList, TrackedResult
{
    /** @use MutableTrackedListLogic<E> */
    use MutableTrackedListLogic;

    /**
     * @param ReadWriteIndexedStore<E> $store
     */
    public function __construct(ReadWriteIndexedStore $store)
    {
        $this->store = $store;
    }

    /**
     * Returns this tracked list (already tracked).
     *
     * @return MutableTrackedList<E>
     */
    #[NoDiscard]
    public function tracked(): MutableTrackedList
    {
        return $this;
    }
}
