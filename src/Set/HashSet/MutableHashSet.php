<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set\HashSet;

use Closure;
use Noctud\Collection\Set\MutableSet;
use Noctud\Collection\Set\MutableSetLogic;
use ReflectionClass;

/**
 * Mutable hash set preserving insertion order.
 * If the given data is Closure, the set will be lazily initialized when first accessed.
 *
 * The class is empty for easy extendability if you want your own MutableSet,
 * use the MutableHashSetLogic trait in your own class; this way you are not tied
 * to our class hierarchy (you can extend your own base class).
 *
 * @template E
 * @implements MutableSet<E>
 */
class MutableHashSet implements MutableSet
{
    /** @use MutableSetLogic<E> */
    use MutableSetLogic;

    /**
     * @param iterable<E>|Closure():iterable<E> $data
     */
    public function __construct(iterable|Closure $data = [])
    {
        if ($data instanceof Closure) {
            $reflector = new ReflectionClass(HashElementStore::class);
            $this->store = $reflector->newLazyProxy(fn (): HashElementStore => new HashElementStore($data()));
        } elseif ($data instanceof HashElementStore) {
            $this->store = clone $data;
        } elseif ($data instanceof self || $data instanceof ImmutableHashSet) {
            $this->store = clone $data->__internalCollectionStore(); // @phpstan-ignore assign.propertyType
        } else {
            $this->store = new HashElementStore($data);
        }
    }
}
