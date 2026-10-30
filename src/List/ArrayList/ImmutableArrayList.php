<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List\ArrayList;

use Closure;
use Noctud\Collection\CollectionLogic;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\List\ImmutableListLogic;
use ReflectionClass;

/**
 * Immutable array list set preserving insertion order.
 * If the given data is Closure, the list will be lazily initialized when first accessed.
 *
 * The class is empty for easy extendability if you want your own ImmutableList,
 * use the ImmutableArrayListLogic trait in your own class; this way you are not tied
 * to our class hierarchy (you can extend your own base class).
 *
 * @template E
 * @implements ImmutableList<E>
 * @use CollectionLogic<E>
 */
class ImmutableArrayList implements ImmutableList
{
    /** @use ImmutableListLogic<E> */
    use ImmutableListLogic;

    /**
     * @param iterable<E>|Closure():iterable<E> $data
     */
    public function __construct(iterable|Closure $data = [])
    {
        if ($data instanceof Closure) {
            $reflector = new ReflectionClass(ArrayIndexStore::class);
            $this->store = $reflector->newLazyProxy(fn (): ArrayIndexStore => new ArrayIndexStore($data()));
        } elseif ($data instanceof ArrayIndexStore) {
            $this->store = clone $data;
        } elseif ($data instanceof self || $data instanceof MutableArrayList) {
            $this->store = clone $data->__internalCollectionStore(); // @phpstan-ignore assign.propertyType
        } else {
            $this->store = new ArrayIndexStore($data);
        }
    }
}
