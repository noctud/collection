<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set;

use PHPUnit\Framework\Attributes\Test;

trait SetMutate
{
    #[Test]
    public function uniqueness_enforced_on_construction(): void
    {
        $set = $this->collectionOf([1, 2, 2, 3, 3, 3]);
        $this->assertSame([1, 2, 3], $set->toArray());
        $this->assertSame(3, $set->count());
    }
}
