<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\View;

use Noctud\Collection\Map\MapEntry;
use Noctud\Collection\Set\Set;
use Noctud\Collection\Set\SetLogic;
use Noctud\Collection\Store\ReadOnlyElementStore;
use Noctud\Collection\Store\KeyValueStore;

/**
 * Live view of map entries as a Set.
 *
 * This view stays synchronized with the underlying map - if the map is mutable
 * and entries are added/removed, the changes are immediately visible through this view.
 *
 * @internal
 * @template K of string|int|bool|float|object
 * @template V
 * @implements Set<MapEntry<K,V>>
 */
final class MapEntrySet implements Set
{
    /** @use SetLogic<MapEntry<K,V>> */
    use SetLogic;

    /** @var ReadOnlyElementStore<MapEntry<K,V>> */
    protected ReadOnlyElementStore $store;

    /**
     * @param KeyValueStore<K,V>|ReadOnlyElementStore<MapEntry<K,V>> $store
     */
    public function __construct(KeyValueStore|ReadOnlyElementStore $store)
    {
        $this->store = $store instanceof ReadOnlyElementStore ? $store : new MapEntryStore($store);
    }
}
