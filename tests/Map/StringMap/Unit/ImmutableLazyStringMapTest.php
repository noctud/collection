<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\StringMap\Unit;

use Closure;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Tests\Map\StringMap\Case\AbstractStringMapTestCase;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\stringMapOf;
use function is_callable;

final class ImmutableLazyStringMapTest extends AbstractStringMapTestCase
{
    /**
     * @template V
     * @param iterable<string,V>|Closure():iterable<string,V> $data
     * @return ImmutableMap<string,V>
     */
    public function mapOf(iterable|Closure $data): ImmutableMap
    {
        if (!is_callable($data)) {
            return stringMapOf(fn () => $data);
        }

        return stringMapOf($data);
    }

    #[Test]
    public function lazy_init_from_closure(): void
    {
        $called = false;
        $map = stringMapOf(function () use (&$called) {
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
