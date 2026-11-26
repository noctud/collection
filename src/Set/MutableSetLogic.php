<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set;

use Noctud\Collection\MutableCollectionLogic;
use Noctud\Collection\Operation\SetOperation;
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

    // --- Mutation: Remove ---

    /** {@inheritDoc} */
    public function removeAll(iterable $elements): static
    {
        SetOperation::subtractInPlace($this->store, $elements);
        return $this;
    }

    /** {@inheritDoc} */
    public function retainAll(iterable $elements): static
    {
        SetOperation::intersectInPlace($this->store, $elements);
        return $this;
    }

    // --- Tracking ---

    /**
     * @inheritDoc
     */
    #[NoDiscard]
    public function tracked(): MutableTrackedSet
    {
        return new MutableTrackedHashSet($this->store);
    }
}
