<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Store;

use IteratorAggregate;
use Noctud\Collection\Exception\NoSuchElementException;
use Traversable;

/**
 * Readable element store interface.
 * Used only by traits of this library, not associated with main interfaces.
 *
 * @template E
 * @extends IteratorAggregate<non-negative-int,E>
 */
interface ReadOnlyElementStore extends IteratorAggregate
{
    // --- Element Access ---

    /**
     * Returns the first element.
     *
     * @return ($throw is true ? E : E|null)
     * @throws NoSuchElementException
     */
    public function first(bool $throw = false): mixed;

    /**
     * Returns the last element.
     *
     * @return ($throw is true ? E : E|null)
     * @throws NoSuchElementException
     */
    public function last(bool $throw = false): mixed;

    /**
     * Returns a random element.
     *
     * @return ($throw is true ? E : E|null)
     * @throws NoSuchElementException
     */
    public function random(bool $throw = false): mixed;

    // --- Querying ---

    /**
     * Returns true if the storage contains the given value (strict comparison).
     *
     * @param mixed $element
     */
    public function contains(mixed $element): bool;

    /**
     * Returns true if the storage is empty.
     */
    public function isEmpty(): bool;

    /**
     * Returns the number of elements.
     */
    public function count(): int;

    // --- Conversion ---

    /**
     * Returns all elements as a PHP array.
     *
     * @return list<E>
     */
    public function toArray(): array;

    // --- Internal ---

    /**
     * Returns an iterator over all elements.
     *
     * @return Traversable<non-negative-int,E>
     */
    public function getIterator(): Traversable;
}
