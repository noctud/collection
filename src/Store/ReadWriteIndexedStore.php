<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Store;

use Noctud\Collection\Exception\IndexOutOfBoundsException;

/**
 * Writable and readable element store interface.
 * Used only by traits of this library, not associated with main interfaces.
 *
 * @template V
 * @extends ReadOnlyIndexedStore<V>
 * @extends ReadWriteElementStore<V>
 */
interface ReadWriteIndexedStore extends ReadOnlyIndexedStore, ReadWriteElementStore
{
    // --- Mutation ---

    /**
     * Replaces the element at an existing index; it never appends.
     *
     * @param int $index
     * @param V $element
     * @throws IndexOutOfBoundsException If no element is at the index
     */
    public function set(int $index, mixed $element): void;

    /**
     * Removes the element at the given index.
     *
     * @param int $index
     * @throws IndexOutOfBoundsException If no element is at the index
     */
    public function removeAt(int $index): void;

    /**
     * Removes all occurrences of the element.
     *
     * @param V $element
     */
    public function removeEvery(mixed $element): void;
}
