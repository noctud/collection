<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

use Noctud\Collection\Hashable;

/**
 * @template K of string|int|bool|float|object
 * @template V
 */
interface MapEntry extends Hashable
{
    /**
     * @var K
     */
    public string|int|bool|float|object $key {
        get;
    }

    /**
     * @var V
     */
    public mixed $value {
        get;
    }
}
