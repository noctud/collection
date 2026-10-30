<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\IntMap\Unit;

use Closure;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Tests\Map\IntMap\Case\AbstractIntMapTestCase;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\mutableIntMapOf;
use function is_callable;

final class MutableLazyIntMapTest extends AbstractIntMapTestCase
{
	/**
	 * @template V
	 * @param iterable<int,V>|Closure():iterable<int,V> $data
	 * @return MutableMap<int,V>
	 */
	public function mapOf(iterable|Closure $data): MutableMap
	{
		if (!is_callable($data)) {
			return mutableIntMapOf(fn () => $data);
		}

		return mutableIntMapOf($data);
	}

	#[Test]
	public function lazy_init_from_closure(): void
	{
		$called = false;
		$map = mutableIntMapOf(function () use (&$called) {
			$called = true;
			return [1 => 'a', 2 => 'b'];
		});

		// Not called yet
		$this->assertFalse($called);

		// Triggers initialization
		$this->assertSame(2, $map->count());
		$this->assertTrue($called); // @phpstan-ignore method.impossibleType
	}
}
