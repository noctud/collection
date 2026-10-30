<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Map\Map;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

trait MapContainsAndGet
{
    #[Test]
    #[DataProvider('containsProvider')]
    public function containsKey_containsValue_get(array $data, string $presentKey, string $missingKey, mixed $presentValue, mixed $missingValue): void
    {
        /** @var Map<string, mixed> $map */
        $map = $this->mapOf($data);

        // containsValue
        $this->assertTrue($map->containsValue($presentValue));
        $this->assertFalse($map->containsValue($missingValue));

        // containsKey
        $this->assertTrue(isset($map[$presentKey]));
        $this->assertTrue($map->containsKey($presentKey));
        $this->assertFalse($map->containsKey($missingKey));

        // get existing key
        $this->assertSame($presentValue, $map[$presentKey]);
        $this->assertSame($presentValue, $map->get($presentKey));
        $this->assertSame($presentValue, $map->getOrNull($presentKey));
        $this->assertSame($presentValue, $map->getOrDefault($presentKey, null));

        // get missing key - safe access via ?? operator
        $this->assertSame(null, $map[$missingKey] ?? null);
        $this->assertSame(null, $map->getOrNull($missingKey));
        $this->assertSame(null, $map->getOrDefault($missingKey, null));
        $this->assertSame($presentValue, $map->getOrDefault($missingKey, $presentValue));

        // get throws when missing
        $this->expectException(NoSuchElementException::class);
        $map->get($missingKey);
    }

    #[Test]
    public function offsetGet_throws_on_missing_key(): void
    {
        $map = $this->mapOf(['a' => 1]);

        $this->expectException(NoSuchElementException::class);
        $map['missing']; // @phpstan-ignore expr.resultUnused
    }

    #[Test]
    public function offsetGet_with_null_coalescing_returns_default(): void
    {
        $map = $this->mapOf(['a' => 1]);

        $this->assertSame('default', $map['missing'] ?? 'default');
        $this->assertSame(1, $map['a'] ?? 'default');
    }

    public static function containsProvider(): iterable
    {
        yield 'strings' => [
            'data' => ['a' => 'x', 'b' => 'y'],
            'presentKey' => 'a',
            'missingKey' => 'c',
            'presentValue' => 'x',
            'missingValue' => null,
        ];

        yield 'null value present' => [
            'data' => ['k1' => null],
            'presentKey' => 'k1',
            'missingKey' => 'k2',
            'presentValue' => null,
            'missingValue' => 'not null',
        ];
    }

    #[Test]
    public function getOrCompute_returns_value_when_key_exists(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);
        $called = false;

        $result = $map->getOrCompute('a', function () use (&$called) {
            $called = true;
            return 99;
        });

        $this->assertSame(1, $result);
        $this->assertFalse($called);
    }

    #[Test]
    public function getOrCompute_calls_closure_when_key_missing(): void
    {
        $map = $this->mapOf(['a' => 1]);

        $result = $map->getOrCompute('missing', fn () => 42);

        $this->assertSame(42, $result);
    }

    #[Test]
    public function getOrCompute_calls_closure_when_key_missing_with_null_value(): void
    {
        $map = $this->mapOf(['a' => null]);

        // Key exists with null value — should return null, not call compute
        $result = $map->getOrCompute('a', fn () => 'computed');
        $this->assertNull($result);

        // Key missing — should call compute
        $result = $map->getOrCompute('b', fn () => 'computed');
        $this->assertSame('computed', $result);
    }

    #[Test]
    public function containsValue_is_strict(): void
    {
        $map = $this->mapOf(['1' => 1, '2' => '1', '3' => true]);

        $this->assertTrue($map->containsValue(1));
        $this->assertTrue($map->containsValue('1'));
        $this->assertTrue($map->containsValue(true));
        $this->assertFalse($map->containsValue(0));
        $this->assertFalse($map->containsValue(false));
    }
}
