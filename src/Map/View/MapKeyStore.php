<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\View;

use Noctud\Collection\Store\ReadOnlyElementStore;
use Noctud\Collection\Store\KeyValueStore;
use Traversable;
use function is_object;
use function is_scalar;

/**
 * @template E of string|int|bool|float|object
 * @implements ReadOnlyElementStore<E>
 */
final class MapKeyStore implements ReadOnlyElementStore
{
    public function __construct(
        /** @var KeyValueStore<E,mixed> */
        private readonly KeyValueStore $store,
    ) {}

    /**
     * @return Traversable<E>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->store as $key => $value) {
            yield $key;
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

    public function first(bool $throw = false): mixed
    {
        return $this->store->first($throw)?->key;
    }

    public function last(bool $throw = false): mixed
    {
        return $this->store->last($throw)?->key;
    }

    public function random(bool $throw = false): mixed
    {
        return $this->store->random($throw)?->key;
    }

    public function contains(mixed $element): bool
    {
        // containsKey() only takes key types; null, an array or a resource is never a key.
        if (!is_scalar($element) && !is_object($element)) {
            return false;
        }

        return $this->store->containsKey($element);
    }

    public function toArray(): array
    {
        return $this->store->keys();
    }
}
