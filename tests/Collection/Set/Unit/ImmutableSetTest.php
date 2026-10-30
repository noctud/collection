<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set\Unit;

use Closure;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Tests\Collection\CollectionMutateWrite;
use Noctud\Collection\Tests\Collection\Set\Case\AbstractSetTestCase;
use Noctud\Collection\Tests\Collection\Set\SetMutateWrite;
use function Noctud\Collection\setOf;

final class ImmutableSetTest extends AbstractSetTestCase
{
    use CollectionMutateWrite;
    use SetMutateWrite;

    /**
     * @template E
     * @param iterable<E>|Closure():iterable<E> $data
     * @return ImmutableSet<E>
     */
    public function collectionOf(iterable|Closure $data): ImmutableSet
    {
        return setOf($data);
    }

    /**
     * @template E
     * @param iterable<E>|Closure():iterable<E> $data
     * @return ImmutableSet<E>
     */
    public function enumerableOf(iterable|Closure $data): ImmutableSet
    {
        return $this->collectionOf($data);
    }
}
