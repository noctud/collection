<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests;

use Noctud\Collection\Collection;
use Noctud\Collection\ImmutableCollection;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\List\MutableList;
use Noctud\Collection\Map\HashMap\ImmutableHashMap;
use Noctud\Collection\Map\HashMap\MutableHashMap;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\IntMap\ImmutableIntMap;
use Noctud\Collection\Map\IntMap\MutableIntMap;
use Noctud\Collection\Map\Map;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Map\StringMap\ImmutableStringMap;
use Noctud\Collection\Map\StringMap\MutableStringMap;
use Noctud\Collection\Map\View\MapKeySet;
use Noctud\Collection\Map\View\MapValueCollection;
use Noctud\Collection\MutableCollection;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\MutableSet;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\listOf;
use function Noctud\Collection\mapOf;
use function Noctud\Collection\mutableListOf;
use function Noctud\Collection\mapOfPairs;
use function Noctud\Collection\intMapOf;
use function Noctud\Collection\mutableIntMapOf;
use function Noctud\Collection\mutableMapOf;
use function Noctud\Collection\mutableMapOfPairs;
use function Noctud\Collection\mutableSetOf;
use function Noctud\Collection\mutableStringMapOf;
use function Noctud\Collection\setOf;
use function Noctud\Collection\stringMapOf;
use function gc_collect_cycles;
use function memory_get_usage;
use function max;
use function sprintf;

trait EnumerableCopyOnWrite
{
    private const int CowSize = 50_000;

    private const float CowMaxMemoryIncreasePercent = 10.0;

    /** @var list<mixed> Prevents GC from collecting references during memory measurement */
    private array $cowAnchors = [];

    /**
     * @return Collection<string>|Map<int|string, string>
     */
    private function createCowCollection(): Collection|Map
    {
        return $this->enumerableOf($this->generateSampleData(self::CowSize)); // @phpstan-ignore return.type
    }

    #[Test]
    public function toMutable_uses_copy_on_write(): void
    {
        $enumerable = $this->createCowCollection();

        // Views materialize data on conversion, COW does not apply
        if ($enumerable instanceof MapKeySet || $enumerable instanceof MapValueCollection) {
            $this->assertTrue(true); /** @phpstan-ignore-line */
            return;
        }

        // Materialize lazy collections
        $enumerable->count();

        gc_collect_cycles();
        $before = memory_get_usage();

        $this->cowAnchors[] = $enumerable->toMutable();

        gc_collect_cycles();
        $after = memory_get_usage();

        $increase = max(0, $after - $before);
        $this->assertCowMemory($increase);
    }

    #[Test]
    public function toImmutable_uses_copy_on_write(): void
    {
        $enumerable = $this->createCowCollection();

        // Views materialize data on conversion, COW does not apply
        if ($enumerable instanceof MapKeySet || $enumerable instanceof MapValueCollection) {
            $this->assertTrue(true); /** @phpstan-ignore-line */
            return;
        }

        // Materialize lazy collections
        $enumerable->count();

        if ($enumerable instanceof ImmutableCollection || $enumerable instanceof ImmutableMap) {
            $this->assertSame($enumerable, $enumerable->toImmutable());
            return;
        }

        gc_collect_cycles();
        $before = memory_get_usage();

        $this->cowAnchors[] = $enumerable->toImmutable();

        gc_collect_cycles();
        $after = memory_get_usage();

        $increase = max(0, $after - $before);
        $this->assertCowMemory($increase);
    }

    #[Test]
    public function constructing_from_existing_uses_copy_on_write(): void
    {
        $enumerable = $this->createCowCollection();

        // Views materialize data on conversion, COW does not apply
        if ($enumerable instanceof MapKeySet || $enumerable instanceof MapValueCollection) {
            $this->assertTrue(true); /** @phpstan-ignore-line */
            return;
        }

        // Materialize lazy collections
        $enumerable->count();

        gc_collect_cycles();
        $before = memory_get_usage();

        if ($enumerable instanceof MutableList) {
            $this->cowAnchors[] = mutableListOf($enumerable);
        } elseif ($enumerable instanceof ImmutableList) {
            $this->cowAnchors[] = listOf($enumerable);
        } elseif ($enumerable instanceof MutableSet) {
            $this->cowAnchors[] = mutableSetOf($enumerable);
        } elseif ($enumerable instanceof ImmutableSet) {
            $this->cowAnchors[] = setOf($enumerable);
        } elseif ($enumerable instanceof MutableStringMap) {
            $this->cowAnchors[] = mutableStringMapOf($enumerable);
        } elseif ($enumerable instanceof ImmutableStringMap) {
            $this->cowAnchors[] = stringMapOf($enumerable);
        } elseif ($enumerable instanceof MutableIntMap) {
            $this->cowAnchors[] = mutableIntMapOf($enumerable);
        } elseif ($enumerable instanceof ImmutableIntMap) {
            $this->cowAnchors[] = intMapOf($enumerable);
        } elseif ($enumerable instanceof MutableMap) {
            $this->cowAnchors[] = mutableMapOf($enumerable);
        } elseif ($enumerable instanceof ImmutableMap) {
            $this->cowAnchors[] = mapOf($enumerable);
        } elseif ($enumerable instanceof MutableCollection) {
            $this->cowAnchors[] = mutableListOf($enumerable);
        } elseif ($enumerable instanceof ImmutableCollection) {
            $this->cowAnchors[] = listOf($enumerable);
        } else {
            $this->fail('Unsupported enumerable type: ' . $enumerable::class);
        }

        gc_collect_cycles();
        $after = memory_get_usage();

        $increase = max(0, $after - $before);
        $this->assertCowMemory($increase);
    }

    #[Test]
    public function constructing_cross_type_uses_copy_on_write(): void
    {
        $enumerable = $this->createCowCollection();

        // Views materialize data on conversion, COW does not apply
        if ($enumerable instanceof MapKeySet || $enumerable instanceof MapValueCollection) {
            $this->assertTrue(true); /** @phpstan-ignore-line */
            return;
        }

        // Materialize lazy collections
        $enumerable->count();

        gc_collect_cycles();
        $before = memory_get_usage();

        if ($enumerable instanceof MutableList) {
            $this->cowAnchors[] = listOf($enumerable);
        } elseif ($enumerable instanceof ImmutableList) {
            $this->cowAnchors[] = mutableListOf($enumerable);
        } elseif ($enumerable instanceof MutableSet) {
            $this->cowAnchors[] = setOf($enumerable);
        } elseif ($enumerable instanceof ImmutableSet) {
            $this->cowAnchors[] = mutableSetOf($enumerable);
        } elseif ($enumerable instanceof MutableStringMap) {
            $this->cowAnchors[] = stringMapOf($enumerable);
        } elseif ($enumerable instanceof ImmutableStringMap) {
            $this->cowAnchors[] = mutableStringMapOf($enumerable);
        } elseif ($enumerable instanceof MutableIntMap) {
            $this->cowAnchors[] = intMapOf($enumerable);
        } elseif ($enumerable instanceof ImmutableIntMap) {
            $this->cowAnchors[] = mutableIntMapOf($enumerable);
        } elseif ($enumerable instanceof MutableMap) {
            $this->cowAnchors[] = mapOf($enumerable);
        } elseif ($enumerable instanceof ImmutableMap) {
            $this->cowAnchors[] = mutableMapOf($enumerable);
        } elseif ($enumerable instanceof MutableCollection) {
            $this->cowAnchors[] = listOf($enumerable);
        } elseif ($enumerable instanceof ImmutableCollection) {
            $this->cowAnchors[] = mutableListOf($enumerable);
        } else {
            $this->fail('Unsupported enumerable type: ' . $enumerable::class);
        }

        gc_collect_cycles();
        $after = memory_get_usage();

        $increase = max(0, $after - $before);
        $this->assertCowMemory($increase);
    }

    #[Test]
    public function constructing_from_pairs_uses_copy_on_write(): void
    {
        $enumerable = $this->createCowCollection();

        // Only HashMaps support ofPairs construction
        if (!$enumerable instanceof MutableHashMap && !$enumerable instanceof ImmutableHashMap) {
            $this->assertTrue(true); /** @phpstan-ignore-line */
            return;
        }

        // Materialize lazy collections
        $enumerable->count();

        gc_collect_cycles();
        $before = memory_get_usage();

        if ($enumerable instanceof MutableHashMap) {
            $this->cowAnchors[] = mutableMapOfPairs($enumerable); /** @phpstan-ignore-line */
        } else {
            $this->cowAnchors[] = mapOfPairs($enumerable); /** @phpstan-ignore-line */
        }

        gc_collect_cycles();
        $after = memory_get_usage();

        $increase = max(0, $after - $before);
        $this->assertCowMemory($increase);
    }

    private function assertCowMemory(int $increase): void
    {
        gc_collect_cycles();
        $refBefore = memory_get_usage();
        $reference = $this->createCowCollection();
        $reference->count();
        $this->cowAnchors[] = $reference;
        gc_collect_cycles();
        $refAfter = memory_get_usage();
        $referenceMemory = max(1, $refAfter - $refBefore);

        $percent = ($increase / $referenceMemory) * 100;

        $this->assertLessThan(
            self::CowMaxMemoryIncreasePercent,
            $percent,
            sprintf(
                'Copy-on-write failed: conversion used %.1f%% of base collection memory',
                $percent,
            ),
        );
    }
}
