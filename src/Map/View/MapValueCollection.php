<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\View;

use Noctud\Collection\Collection;
use Noctud\Collection\CollectionLogic;
use Noctud\Collection\ImmutableCollection;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Store\ReadOnlyElementStore;
use Noctud\Collection\Store\KeyValueStore;
use function Noctud\Collection\listOf;

/**
 * Live view of map values as a Collection.
 *
 * This view stays synchronized with the underlying map - if the map is mutable
 * and values are added/removed, the changes are immediately visible through this view.
 *
 * @internal
 * @template E
 * @implements Collection<E>
 */
final class MapValueCollection implements Collection
{
    /** @use CollectionLogic<E> */
    use CollectionLogic;

    /** @var ReadOnlyElementStore<E> */
    protected ReadOnlyElementStore $store;

    /**
     * @template TK of string|int|bool|float|object
     * @param KeyValueStore<TK,E>|ReadOnlyElementStore<E> $store
     */
    public function __construct(KeyValueStore|ReadOnlyElementStore $store)
    {
        $this->store = $store instanceof ReadOnlyElementStore ? $store : new MapValueStore($store);
    }

    /**
     * @param iterable<E> $data
     * @return ImmutableList<E>
     */
    protected function newCollectionOf(iterable $data): ImmutableCollection
    {
        return listOf($data);
    }
}
