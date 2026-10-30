<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\View;

use Noctud\Collection\Set\Set;
use Noctud\Collection\Set\SetLogic;
use Noctud\Collection\Store\ReadOnlyElementStore;
use Noctud\Collection\Store\KeyValueStore;

/**
 * Live view of map keys as a Set.
 *
 * This view stays synchronized with the underlying map - if the map is mutable
 * and keys are added/removed, the changes are immediately visible through this view.
 *
 * @internal
 * @template E of string|int|bool|float|object
 * @implements Set<E>
 */
final class MapKeySet implements Set
{
    /** @use SetLogic<E> */
    use SetLogic;

    /** @var ReadOnlyElementStore<E> */
    protected ReadOnlyElementStore $store;

    /**
     * @template TV
     * @param KeyValueStore<E,TV>|ReadOnlyElementStore<E> $store
     */
    public function __construct(KeyValueStore|ReadOnlyElementStore $store)
    {
        $this->store = $store instanceof ReadOnlyElementStore ? $store : new MapKeyStore($store);
    }
}
