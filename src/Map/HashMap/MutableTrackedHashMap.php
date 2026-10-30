<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\HashMap;

use Noctud\Collection\Map\MutableTrackedMapLogic;
use Noctud\Collection\Map\MutableTrackedMap;
use Noctud\Collection\Store\KeyValueStore;
use Noctud\Collection\TrackedResult;
use NoDiscard;

/**
 * Mutable tracked hash map that tracks whether the last mutation changed the map.
 * Shares the underlying store with a MutableHashMap.
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @implements MutableTrackedMap<K,V>
 */
final class MutableTrackedHashMap implements MutableTrackedMap, TrackedResult
{
    /** @use MutableTrackedMapLogic<K,V> */
    use MutableTrackedMapLogic;

    /**
     * @param KeyValueStore<K,V> $store
     */
    public function __construct(KeyValueStore $store)
    {
        $this->store = $store;
    }

    /**
     * Returns this tracked map (already tracked).
     *
     * @return MutableTrackedMap<K,V>
     */
    #[NoDiscard]
    public function tracked(): MutableTrackedMap
    {
        return $this;
    }
}
