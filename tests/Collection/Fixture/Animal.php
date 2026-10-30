<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Fixture;

class Animal
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
