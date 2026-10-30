<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List;

use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\listOf;
use function Noctud\Collection\mutableListOf;
use function gc_collect_cycles;
use function memory_get_usage;
use function max;
use function sprintf;

trait ListCopyOnWrite
{
    private const int ListCowSize = 100_000;

    private const float ListCowMaxMemoryIncreasePercent = 25.0;

    /** @var list<mixed> */
    private array $listCowAnchors = [];

    /**
     * @return list<string>
     */
    private function generateListCowArray(): array
    {
        $array = [];
        for ($i = 0; $i < self::ListCowSize; $i++) {
            $array[] = "value_$i";
        }

        return $array;
    }

    /**
     * Measures how much memory a full (non-COW) copy of the array uses.
     * Forces PHP to duplicate the hash table by iterating element-by-element.
     *
     * @param list<string> $source
     */
    private function measureListArrayCopyCost(array $source): int
    {
        gc_collect_cycles();
        $before = memory_get_usage();
        $copy = [];
        foreach ($source as $v) {
            $copy[] = $v;
        }
        $this->listCowAnchors[] = $copy;
        gc_collect_cycles();

        return max(1, memory_get_usage() - $before);
    }

    #[Test]
    public function constructing_list_from_array_uses_copy_on_write(): void
    {
        $array = $this->generateListCowArray();
        $referenceMemory = $this->measureListArrayCopyCost($array);

        gc_collect_cycles();
        $before = memory_get_usage();

        $this->listCowAnchors[] = listOf($array);

        gc_collect_cycles();
        $after = memory_get_usage();

        $increase = max(0, $after - $before);
        $percent = ($increase / $referenceMemory) * 100;

        $this->assertLessThan(
            self::ListCowMaxMemoryIncreasePercent,
            $percent,
            sprintf(
                'Copy-on-write failed: constructing list from array used %.1f%% of array copy memory (expected near 0%%)',
                $percent,
            ),
        );
    }

    #[Test]
    public function constructing_mutable_list_from_array_uses_copy_on_write(): void
    {
        $array = $this->generateListCowArray();
        $referenceMemory = $this->measureListArrayCopyCost($array);

        gc_collect_cycles();
        $before = memory_get_usage();

        $this->listCowAnchors[] = mutableListOf($array);

        gc_collect_cycles();
        $after = memory_get_usage();

        $increase = max(0, $after - $before);
        $percent = ($increase / $referenceMemory) * 100;

        $this->assertLessThan(
            self::ListCowMaxMemoryIncreasePercent,
            $percent,
            sprintf(
                'Copy-on-write failed: constructing mutable list from array used %.1f%% of array copy memory (expected near 0%%)',
                $percent,
            ),
        );
    }
}
