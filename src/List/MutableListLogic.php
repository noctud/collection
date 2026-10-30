<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List;

use Noctud\Collection\List\ArrayList\ArrayIndexStore;
use Noctud\Collection\List\ArrayList\MutableTrackedArrayList;
use Noctud\Collection\MutableCollectionLogic;
use Noctud\Collection\Store\ReadWriteIndexedStore;
use NoDiscard;

/**
 * @template E
 * @implements MutableList<E>
 */
trait MutableListLogic
{
    /** @var ReadWriteIndexedStore<E> */
    protected ReadWriteIndexedStore $store;

    /** @use ListLogic<E> */
    use ListLogic;

    /** @use MutableCollectionLogic<E> */
    use MutableCollectionLogic {
        ListLogic::newCollectionOf insteadof MutableCollectionLogic;
    }

    // --- Tracking ---

    /**
     * @inheritDoc
     */
    #[NoDiscard]
    public function tracked(): MutableTrackedList
    {
        /** @var ArrayIndexStore<E> $store */
        $store = $this->store;
        return new MutableTrackedArrayList($store);
    }

    // --- Mutation: List ---

    /** {@inheritDoc} */
    public function set(int $index, mixed $element): MutableList
    {
        $this->store->set($index, $element);
        return $this;
    }

    /** {@inheritDoc} */
    public function removeAt(int $index): MutableList
    {
        $this->store->removeAt($index);
        return $this;
    }

    /** {@inheritDoc} */
    public function removeEvery(mixed $element): MutableList
    {
        $this->store->removeEvery($element);
        return $this;
    }

    // --- ArrayAccess ---

    /**
     * @param int|null $offset
     * @param E $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->add($value);
        } else {
            $this->set($offset, $value);
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->removeAt($offset);
    }
}
