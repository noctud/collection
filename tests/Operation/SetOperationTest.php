<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Operation;

use Noctud\Collection\List\ArrayList\ArrayIndexStore;
use Noctud\Collection\Operation\SetOperation;
use Noctud\Collection\Tests\Collection\Fixture\HashableUser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SetOperationTest extends TestCase
{
    #[Test]
    public function subtractInPlace_hashes_the_elements_of_a_store_that_is_not_a_hash_store(): void
    {
        $user = new HashableUser('1');
        $store = new ArrayIndexStore([$user, 0.1 + 0.2, $user, 'other']);

        SetOperation::subtractInPlace($store, [new HashableUser('1'), 0.3]);

        $this->assertSame(['other'], $store->toArray());
    }

    #[Test]
    public function intersectInPlace_hashes_the_elements_of_a_store_that_is_not_a_hash_store(): void
    {
        $user = new HashableUser('1');
        $store = new ArrayIndexStore([$user, 'other', $user]);

        SetOperation::intersectInPlace($store, [new HashableUser('1')]);

        $this->assertSame([$user, $user], $store->toArray());
    }
}
