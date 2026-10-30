<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Store;

/**
 * Readable element store interface.
 * Used only by traits of this library, not associated with main interfaces.
 *
 * @template V
 * @extends ReadOnlyElementStore<V>
 */
interface ReadOnlyIndexedStore extends ReadOnlyElementStore
{
    // --- Element Access ---

    /**
     * Returns the value at the given index, or null if not found.
     *
     * @return ($throw is true ? V : V|null)
     */
    public function get(int $index, bool $throw = false): mixed;

    /**
     * Returns the index of the first occurrence of the value, or -1 if not found.
     *
     * @param V $value
     */
    public function indexOf(mixed $value): int;

    /**
     * Returns the index of the last occurrence of the value, or -1 if not found.
     *
     * @param V $value
     */
    public function lastIndexOf(mixed $value): int;
}
