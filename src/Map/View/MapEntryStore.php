<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\View;

use Noctud\Collection\Map\MapEntry;
use Noctud\Collection\Map\SimpleMapEntry;
use Noctud\Collection\Store\ReadOnlyElementStore;
use Noctud\Collection\Store\KeyValueStore;
use Traversable;
use function iterator_to_array;

/**
 * @template K of string|int|bool|float|object
 * @template V
 * @implements ReadOnlyElementStore<MapEntry<K,V>>
 */
final class MapEntryStore implements ReadOnlyElementStore
{
    public function __construct(
        /** @var KeyValueStore<K,V> */
        private readonly KeyValueStore $store,
    ) {}

    /**
     * @return Traversable<MapEntry<K,V>>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->store as $key => $value) {
            yield new SimpleMapEntry($key, $value);
        }
    }

    public function count(): int
    {
        return $this->store->count();
    }

    public function isEmpty(): bool
    {
        return $this->store->isEmpty();
    }

    public function first(bool $throw = false): ?MapEntry
    {
        return $this->store->first($throw);
    }

    public function last(bool $throw = false): ?MapEntry
    {
        return $this->store->last($throw);
    }

    public function random(bool $throw = false): ?MapEntry
    {
        return $this->store->random($throw);
    }

    public function contains(mixed $element): bool
    {
        if (!$element instanceof MapEntry) {
            return false;
        }

        return $this->store->containsKey($element->key) && $this->store->get($element->key) === $element->value; // @phpstan-ignore argument.type, argument.type
    }

    public function toArray(): array
    {
        return iterator_to_array($this, preserve_keys: false);
    }
}
