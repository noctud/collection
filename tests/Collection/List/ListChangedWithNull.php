<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List;

use Noctud\Collection\List\MutableList;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests for $changed property behavior when setting/removing null values.
 */
trait ListChangedWithNull
{
    #[Test]
    public function set_null_to_non_null_sets_changed_true(): void
    {
        $list = $this->collectionOf(['a', 'b', 'c']);

        if (!$list instanceof MutableList) {
            $this->markTestSkipped('Only applicable to mutable lists');
        }

        $tracked = $list->tracked();
        $result = $tracked->set(1, null);

        $this->assertTrue($result->changed);
    }

    #[Test]
    public function set_null_to_existing_null_sets_changed_false(): void
    {
        $list = $this->collectionOf(['a', null, 'c']);

        if (!$list instanceof MutableList) {
            $this->markTestSkipped('Only applicable to mutable lists');
        }

        $tracked = $list->tracked();
        $result = $tracked->set(1, null);

        $this->assertFalse($result->changed);
    }

    #[Test]
    public function set_value_to_existing_null_sets_changed_true(): void
    {
        $list = $this->collectionOf(['a', null, 'c']);

        if (!$list instanceof MutableList) {
            $this->markTestSkipped('Only applicable to mutable lists');
        }

        $tracked = $list->tracked();
        $result = $tracked->set(1, 'new_value');

        $this->assertTrue($result->changed);
    }

    #[Test]
    public function remove_null_when_exists_sets_changed_true(): void
    {
        $list = $this->collectionOf(['a', null, 'b']);

        if (!$list instanceof MutableList) {
            $this->markTestSkipped('Only applicable to mutable lists');
        }

        $tracked = $list->tracked();
        $result = $tracked->removeElement(null);

        $this->assertTrue($result->changed);
    }

    #[Test]
    public function remove_null_when_not_exists_sets_changed_false(): void
    {
        $list = $this->collectionOf(['a', 'b', 'c']);

        if (!$list instanceof MutableList) {
            $this->markTestSkipped('Only applicable to mutable lists');
        }

        $tracked = $list->tracked();
        $result = $tracked->removeElement(null);

        $this->assertFalse($result->changed);
    }
}
