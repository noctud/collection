<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\StringMap\Unit;

use Closure;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Tests\Map\StringMap\Case\AbstractStringMapTestCase;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\mutableStringMapOf;
use function is_callable;

final class MutableLazyStringMapTest extends AbstractStringMapTestCase
{
	/**
	 * @template V
	 * @param iterable<string,V>|Closure():iterable<string,V> $data
	 * @return MutableMap<string,V>
	 */
	public function mapOf(iterable|Closure $data): MutableMap
	{
		if (!is_callable($data)) {
			return mutableStringMapOf(fn () => $data);
		}

		return mutableStringMapOf($data);
	}

	#[Test]
	public function lazy_init_from_closure(): void
	{
		$called = false;
		$map = mutableStringMapOf(function () use (&$called) {
			$called = true;
			return ['a' => 1, 'b' => 2];
		});

		// Not called yet
		$this->assertFalse($called);

		// Triggers initialization
		$this->assertSame(2, $map->count());
		$this->assertTrue($called); // @phpstan-ignore method.impossibleType
	}
}
