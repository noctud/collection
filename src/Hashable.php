<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

/**
 * Returns a unique identity for this object, used for hashing in maps and sets.
 *
 * Two objects that are considered equal MUST return the same identity.
 * Two objects that are NOT equal MUST return different identities.
 */
interface Hashable
{
    public function identity(): string|int;
}
