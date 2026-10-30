<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set\Unit;

use Closure;
use Noctud\Collection\Set\MutableSet;
use Noctud\Collection\Tests\Collection\CollectionMutateWrite;
use Noctud\Collection\Tests\Collection\Set\Case\AbstractSetTestCase;
use Noctud\Collection\Tests\Collection\Set\SetMutateWrite;
use Noctud\Collection\Tests\EnumerableLazyInit;
use function Noctud\Collection\mutableSetOf;
use function is_callable;

final class MutableLazySetTest extends AbstractSetTestCase
{
    use CollectionMutateWrite;
    use EnumerableLazyInit;
    use SetMutateWrite;

    /**
     * @template E
     * @param iterable<E>|Closure():iterable<E> $data
     * @return MutableSet<E>
     */
    public function collectionOf(iterable|Closure $data): MutableSet
    {
        if (!is_callable($data)) {
            return mutableSetOf(fn () => $data);
        }

        return mutableSetOf($data);
    }

    /**
     * @template E
     * @param iterable<E>|Closure():iterable<E> $data
     * @return MutableSet<E>
     */
    public function enumerableOf(iterable|Closure $data): MutableSet
    {
        return $this->collectionOf($data);
    }
}
