<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set\Extending;

use Noctud\Collection\Set\HashSet\HashElementStore;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\SelfPreservingImmutableSetLogic;

/**
 * Extension style #1 without an element invariant, for elements that are not SwappableItem.
 *
 * @implements ImmutableSet<mixed>
 * @phpstan-consistent-constructor
 */
class MixedItemCollection implements ImmutableSet
{
    /** @use SelfPreservingImmutableSetLogic<mixed> */
    use SelfPreservingImmutableSetLogic;

    /** @param iterable<mixed> $data */
    public function __construct(iterable $data = [])
    {
        $this->store = new HashElementStore($data);
    }
}
