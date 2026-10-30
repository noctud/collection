<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\IntMap;

use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\intMapOf;
use function Noctud\Collection\mutableIntMapOf;
use function gc_collect_cycles;
use function memory_get_usage;
use function max;
use function sprintf;

trait IntMapCopyOnWrite
{
    private const int IntMapCowSize = 100_000;

    private const float IntMapCowMaxMemoryIncreasePercent = 25.0;

    /** @var list<mixed> */
    private array $intMapCowAnchors = [];

    /**
     * @return array<int, string>
     */
    private function generateIntMapCowArray(): array
    {
        $array = [];
        for ($i = 0; $i < self::IntMapCowSize; $i++) {
            $array[$i] = "value_$i";
        }

        return $array;
    }

    /**
     * Measures how much memory a full (non-COW) copy of the array uses.
     * Forces PHP to duplicate the hash table by iterating element-by-element.
     *
     * @param array<int, string> $source
     */
    private function measureIntMapArrayCopyCost(array $source): int
    {
        gc_collect_cycles();
        $before = memory_get_usage();
        $copy = [];
        foreach ($source as $k => $v) {
            $copy[$k] = $v;
        }
        $this->intMapCowAnchors[] = $copy;
        gc_collect_cycles();

        return max(1, memory_get_usage() - $before);
    }

    #[Test]
    public function constructing_int_map_from_array_uses_copy_on_write(): void
    {
        $array = $this->generateIntMapCowArray();
        $referenceMemory = $this->measureIntMapArrayCopyCost($array);

        gc_collect_cycles();
        $before = memory_get_usage();

        $this->intMapCowAnchors[] = intMapOf($array);

        gc_collect_cycles();
        $after = memory_get_usage();

        $increase = max(0, $after - $before);
        $percent = ($increase / $referenceMemory) * 100;

        $this->assertLessThan(
            self::IntMapCowMaxMemoryIncreasePercent,
            $percent,
            sprintf(
                'Copy-on-write failed: constructing IntMap from array used %.1f%% of array copy memory (expected near 0%%)',
                $percent,
            ),
        );
    }

    #[Test]
    public function constructing_mutable_int_map_from_array_uses_copy_on_write(): void
    {
        $array = $this->generateIntMapCowArray();
        $referenceMemory = $this->measureIntMapArrayCopyCost($array);

        gc_collect_cycles();
        $before = memory_get_usage();

        $this->intMapCowAnchors[] = mutableIntMapOf($array);

        gc_collect_cycles();
        $after = memory_get_usage();

        $increase = max(0, $after - $before);
        $percent = ($increase / $referenceMemory) * 100;

        $this->assertLessThan(
            self::IntMapCowMaxMemoryIncreasePercent,
            $percent,
            sprintf(
                'Copy-on-write failed: constructing mutable IntMap from array used %.1f%% of array copy memory (expected near 0%%)',
                $percent,
            ),
        );
    }
}
