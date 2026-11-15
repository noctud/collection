<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set;

use Noctud\Collection\Set\MutableSet;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests for $changed property behavior when adding/removing null values.
 * These tests ensure the isset() optimization works correctly.
 */
trait SetChangedWithNull
{
    #[Test]
    public function add_null_to_empty_set_sets_changed_true(): void
    {
        $set = $this->collectionOf([]);

        if (!$set instanceof MutableSet) {
            $this->markTestSkipped('Only applicable to mutable sets');
        }

        $tracked = $set->tracked();
        $result = $tracked->add(null);

        $this->assertTrue($result->changed);
    }

    #[Test]
    public function add_null_when_null_exists_sets_changed_false(): void
    {
        $set = $this->collectionOf([null, 'a', 'b']);

        if (!$set instanceof MutableSet) {
            $this->markTestSkipped('Only applicable to mutable sets');
        }

        $tracked = $set->tracked();
        $result = $tracked->add(null);

        $this->assertFalse($result->changed);
    }

    #[Test]
    public function add_value_when_null_exists_sets_changed_true(): void
    {
        $set = $this->collectionOf([null]);

        if (!$set instanceof MutableSet) {
            $this->markTestSkipped('Only applicable to mutable sets');
        }

        $tracked = $set->tracked();
        $result = $tracked->add('new_value');

        $this->assertTrue($result->changed);
    }

    #[Test]
    public function remove_null_when_exists_sets_changed_true(): void
    {
        $set = $this->collectionOf([null, 'a', 'b']);

        if (!$set instanceof MutableSet) {
            $this->markTestSkipped('Only applicable to mutable sets');
        }

        $tracked = $set->tracked();
        $result = $tracked->removeElement(null);

        $this->assertTrue($result->changed);
    }

    #[Test]
    public function remove_null_when_not_exists_sets_changed_false(): void
    {
        $set = $this->collectionOf(['a', 'b']);

        if (!$set instanceof MutableSet) {
            $this->markTestSkipped('Only applicable to mutable sets');
        }

        $tracked = $set->tracked();
        $result = $tracked->removeElement(null);

        $this->assertFalse($result->changed);
    }
}
