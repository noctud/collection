<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Noctud\Collection\Map\MutableMap;
use PHPUnit\Framework\Attributes\Test;

trait MapChangedWithNull
{
    #[Test]
    public function put_null_to_new_key_sets_changed_true(): void
    {
        $map = $this->mapOf([]);

        if (!$map instanceof MutableMap) {
            $this->markTestSkipped('Only applicable to mutable maps');
        }

        $tracked = $map->tracked();
        $result = $tracked->put($this->sampleKey(), null);

        $this->assertTrue($result->changed);
    }

    #[Test]
    public function put_null_to_existing_null_key_sets_changed_false(): void
    {
        $map = $this->mapOf([$this->sampleKey() => null]);

        if (!$map instanceof MutableMap) {
            $this->markTestSkipped('Only applicable to mutable maps');
        }

        $tracked = $map->tracked();
        $result = $tracked->put($this->sampleKey(), null);

        $this->assertFalse($result->changed);
    }

    #[Test]
    public function put_null_to_existing_non_null_key_sets_changed_true(): void
    {
        $map = $this->mapOf([$this->sampleKey() => 'value']);

        if (!$map instanceof MutableMap) {
            $this->markTestSkipped('Only applicable to mutable maps');
        }

        $tracked = $map->tracked();
        $result = $tracked->put($this->sampleKey(), null); // @phpstan-ignore argument.type

        $this->assertTrue($result->changed);
    }

    #[Test]
    public function put_value_to_existing_null_key_sets_changed_true(): void
    {
        $map = $this->mapOf([$this->sampleKey() => null]);

        if (!$map instanceof MutableMap) {
            $this->markTestSkipped('Only applicable to mutable maps');
        }

        $tracked = $map->tracked();
        $result = $tracked->put($this->sampleKey(), 'new_value'); // @phpstan-ignore argument.type

        $this->assertTrue($result->changed);
    }

    /**
     * Returns a sample key appropriate for the map type being tested.
     */
    abstract protected function sampleKey(): int|string;
}
