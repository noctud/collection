<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\StringMap;

use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\mutableStringMapOf;
use function Noctud\Collection\stringMapOf;
use function gc_collect_cycles;
use function memory_get_usage;
use function max;
use function sprintf;

trait StringMapCopyOnWrite
{
	private const int StringMapCowSize = 100_000;

	private const float StringMapCowMaxMemoryIncreasePercent = 25.0;

	/** @var list<mixed> */
	private array $stringMapCowAnchors = [];

	/**
	 * @return array<string, string>
	 */
	private function generateStringMapCowArray(): array
	{
		$array = [];
		for ($i = 0; $i < self::StringMapCowSize; $i++) {
			$array["key_$i"] = "value_$i";
		}

		return $array;
	}

	/**
	 * Measures how much memory a full (non-COW) copy of the array uses.
	 * Forces PHP to duplicate the hash table by iterating element-by-element.
	 *
	 * @param array<string, string> $source
	 */
	private function measureStringMapArrayCopyCost(array $source): int
	{
		gc_collect_cycles();
		$before = memory_get_usage();
		$copy = [];
		foreach ($source as $k => $v) {
			$copy[$k] = $v;
		}
		$this->stringMapCowAnchors[] = $copy;
		gc_collect_cycles();

		return max(1, memory_get_usage() - $before);
	}

	#[Test]
	public function constructing_string_map_from_array_uses_copy_on_write(): void
	{
		$array = $this->generateStringMapCowArray();
		$referenceMemory = $this->measureStringMapArrayCopyCost($array);

		gc_collect_cycles();
		$before = memory_get_usage();

		$this->stringMapCowAnchors[] = stringMapOf($array);

		gc_collect_cycles();
		$after = memory_get_usage();

		$increase = max(0, $after - $before);
		$percent = ($increase / $referenceMemory) * 100;

		$this->assertLessThan(
			self::StringMapCowMaxMemoryIncreasePercent,
			$percent,
			sprintf(
				'Copy-on-write failed: constructing StringMap from array used %.1f%% of array copy memory (expected near 0%%)',
				$percent,
			),
		);
	}

	#[Test]
	public function constructing_mutable_string_map_from_array_uses_copy_on_write(): void
	{
		$array = $this->generateStringMapCowArray();
		$referenceMemory = $this->measureStringMapArrayCopyCost($array);

		gc_collect_cycles();
		$before = memory_get_usage();

		$this->stringMapCowAnchors[] = mutableStringMapOf($array);

		gc_collect_cycles();
		$after = memory_get_usage();

		$increase = max(0, $after - $before);
		$percent = ($increase / $referenceMemory) * 100;

		$this->assertLessThan(
			self::StringMapCowMaxMemoryIncreasePercent,
			$percent,
			sprintf(
				'Copy-on-write failed: constructing mutable StringMap from array used %.1f%% of array copy memory (expected near 0%%)',
				$percent,
			),
		);
	}
}
