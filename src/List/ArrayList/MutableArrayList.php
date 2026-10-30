<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List\ArrayList;

use Closure;
use Noctud\Collection\CollectionLogic;
use Noctud\Collection\List\MutableList;
use Noctud\Collection\List\MutableListLogic;
use ReflectionClass;

/**
 * Mutable array list set preserving insertion order.
 * If the given data is Closure, the list will be lazily initialized when first accessed.
 *
 * The class is empty for easy extendability if you want your own MutableList,
 * use the MutableArrayListLogic trait in your own class; this way you are not tied
 * to our class hierarchy (you can extend your own base class).
 *
 * @template E
 * @implements MutableList<E>
 * @use CollectionLogic<E>
 */
class MutableArrayList implements MutableList
{
    /** @use MutableListLogic<E> */
    use MutableListLogic;

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
        } elseif ($data instanceof self || $data instanceof ImmutableArrayList) {
            $this->store = clone $data->__internalCollectionStore(); // @phpstan-ignore assign.propertyType
        } else {
            $this->store = new ArrayIndexStore($data);
        }
    }
}
