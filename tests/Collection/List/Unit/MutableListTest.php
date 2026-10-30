<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List\Unit;

use Closure;
use Noctud\Collection\List\MutableList;
use Noctud\Collection\Tests\Collection\List\Case\AbstractListTestCase;
use Noctud\Collection\Tests\Collection\List\ListChangedWithNull;
use function Noctud\Collection\mutableListOf;

final class MutableListTest extends AbstractListTestCase
{
    use ListChangedWithNull;

    /**
     * @template E
     * @param iterable<E>|Closure():iterable<E> $data
     * @return MutableList<E>
     */
    public function collectionOf(iterable|Closure $data): MutableList
    {
        return mutableListOf($data);
    }

    /**
     * @template E
     * @param iterable<E>|Closure():iterable<E> $data
     * @return MutableList<E>
     */
    public function enumerableOf(iterable|Closure $data): MutableList
    {
        return $this->collectionOf($data);
    }
}
