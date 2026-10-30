<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set;

use Noctud\Collection\MutableCollectionLogic;
use Noctud\Collection\Set\HashSet\HashElementStore;
use Noctud\Collection\Set\HashSet\MutableTrackedHashSet;
use Noctud\Collection\Store\ReadWriteElementStore;
use NoDiscard;

/**
 * @template E
 * @mixin MutableSet<E>
 * @implements MutableSet<E>
 */
trait MutableSetLogic
{
    /** @var ReadWriteElementStore<E> */
    protected ReadWriteElementStore $store;

    /** @use SetLogic<E> */
    use SetLogic;

    /** @use MutableCollectionLogic<E> */
    use MutableCollectionLogic;

    // --- Tracking ---

    /**
     * @inheritDoc
     */
    #[NoDiscard]
    public function tracked(): MutableTrackedSet
    {
        /** @var HashElementStore<E> $store */
        $store = $this->store;
        return new MutableTrackedHashSet($store);
    }
}
