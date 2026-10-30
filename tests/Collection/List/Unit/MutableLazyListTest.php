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
use Noctud\Collection\Tests\EnumerableLazyInit;
use function Noctud\Collection\mutableListOf;
use function is_callable;

final class MutableLazyListTest extends AbstractListTestCase
{
    use EnumerableLazyInit;

    /**
     * @template E
     * @param iterable<E>|Closure():iterable<E> $data
     * @return MutableList<E>
     */
    public function collectionOf(iterable|Closure $data): MutableList
    {
        if (!is_callable($data)) {
            return mutableListOf(fn () => $data);
        }

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
