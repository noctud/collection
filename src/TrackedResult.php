<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

/**
 * Stores result from the last mutating operation.
 */
interface TrackedResult
{
    public bool $changed {
        get;
    }
}
