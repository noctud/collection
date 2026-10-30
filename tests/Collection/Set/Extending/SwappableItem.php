<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set\Extending;

/**
 * Domain element used by the set-extension fixtures (mirrors issue #3's OrderItem).
 */
final class SwappableItem
{
    public function __construct(
        public readonly int $id,
        public readonly bool $swapped = false,
    ) {
    }
}
