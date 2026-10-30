<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

use Noctud\Collection\KeyHasher;

/**
 * @template K of string|int|bool|float|object
 * @template V
 * @implements MapEntry<K,V>
 */
final class SimpleMapEntry implements MapEntry
{
    /**
     * @param K $key
     */
    public function __construct(
        /** @var K */
        public readonly string|int|bool|float|object $key,
        /** @var V */
        public readonly mixed $value,
    ) {}

    public function identity(): string
    {
        return KeyHasher::hashMapKey($this->key) . ':' . KeyHasher::hashSetKey($this->value);
    }
}
