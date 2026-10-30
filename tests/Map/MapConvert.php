<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Hashable;
use Noctud\Collection\KeyHasher;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\KeyCollisionStrategy;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Tests\Map\Unit\Enum\TestStatus;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use function Noctud\Collection\setOf;
use function spl_object_id;
use function json_encode;

trait MapConvert
{
    #[Test]
    public function toArray_with_mapper_using_key_hasher_output(): void
    {
        $object = new stdClass();
        $enum = setOf(TestStatus::cases())->random();
        $map = $this->mapOfPairs([
            ['1', 'a'],
            [1, 'b'],
            [$object, 'd'],
            [new class() implements Hashable {
                public function identity(): string
                {
                    return 'hashable';
                }
            }, 'e'],
            [false, 'g'],
            [2.0, 'h'],
            [1.234, 'i'],
            [$enum, 'j'],
        ]);

        $this->assertSame(
            [
                's:1' => 'a',
                1 => 'b',
                'o:' . spl_object_id($object) => 'd',
                'h:hashable' => 'e',
                'b:0' => 'g',
                'f:2.000000000000' => 'h',
                'f:1.234000000000' => 'i',
                'o:' . spl_object_id($enum) => 'j',
            ],
            $map->mapKeys(fn ($v, $k) => KeyHasher::hashMapKey($k))->toArray()
        );
    }

    #[Test]
    public function toArray_with_null_key_throws(): void
    {
        $this->expectException(InvalidKeyTypeException::class);
        $map = $this->mapOfPairs([[null, 'value']]); // @phpstan-ignore argument.type, argument.templateType
        $map->count(); // Force lazy initialization
    }

    #[Test]
    public function toArray_fails_because_of_object(): void
    {
        $map = $this->mapOfPairs([[new stdClass(), 'a'],]);

        $this->expectException(ConversionException::class);
        $result = $map->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function toArray_fails_because_of_key_duplicity(): void
    {
        $map = $this->mapOfPairs([['1', 'a'], [1, 'b']]);

        $this->expectException(ConversionException::class);
        $result = $map->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function toArray_keep_first_on_collision(): void
    {
        $map = $this->mapOfPairs([['1', 'a'], [1, 'b']]);

        $this->assertSame([1 => 'a'], $map->toArray(KeyCollisionStrategy::KeepFirst));
    }

    #[Test]
    public function toArray_keep_last_on_collision(): void
    {
        $map = $this->mapOfPairs([['1', 'a'], [1, 'b']]);

        $this->assertSame([1 => 'b'], $map->toArray(KeyCollisionStrategy::KeepLast));
    }

    #[Test]
    public function toArray_with_integer_compatible_float_keys(): void
    {
        $map = $this->mapOfPairs([[1.0, 'a'], [2.0, 'b']]);

        $this->assertSame([1 => 'a', 2 => 'b'], $map->toArray());
    }

    #[Test]
    public function toArray_fails_because_of_float_precision_loss(): void
    {
        $map = $this->mapOfPairs([[1.5, 'a']]);

        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('precision loss');
        $result = $map->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function toArray_float_precision_loss_with_keep_first(): void
    {
        $map = $this->mapOfPairs([[1.5, 'a']]);

        $this->expectException(ConversionException::class);
        $result = $map->toArray(KeyCollisionStrategy::KeepFirst); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
    }

    #[Test]
    public function jsonSerialize(): void
    {
        $map = $this->mapOfPairs([
            ['a', 1],
            ['b', 2],
            ['c', 3],
        ]);

        $this->assertSame(
            '{"a":1,"b":2,"c":3}',
            json_encode($map),
        );
    }

    #[Test]
    public function jsonSerialize_with_object_key_throws(): void
    {
        $map = $this->mapOfPairs([
            [new stdClass(), 1],
        ]);

        $this->expectException(ConversionException::class);
        json_encode($map);
    }

    #[Test]
    public function toPairs(): void
    {
        $map = $this->mapOfPairs([['a', 1], ['b', 2], ['c', 3]]);

        $this->assertSame([['a', 1], ['b', 2], ['c', 3]], $map->toPairs());
    }

    #[Test]
    public function toPairs_empty(): void
    {
        $map = $this->mapOf([]);

        $this->assertSame([], $map->toPairs());
    }

    #[Test]
    public function toPairs_with_object_keys(): void
    {
        $obj = new stdClass();
        $map = $this->mapOfPairs([[$obj, 'a'], ['b', 2]]);

        $pairs = $map->toPairs();
        $this->assertSame($obj, $pairs[0][0]);
        $this->assertSame('a', $pairs[0][1]);
        $this->assertSame('b', $pairs[1][0]);
        $this->assertSame(2, $pairs[1][1]);
    }

    #[Test]
    public function count_returns_total_entries(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);

        $this->assertSame(4, $map->count());
    }

    #[Test]
    public function count_on_empty_map(): void
    {
        $map = $this->mapOf([]);

        $this->assertSame(0, $map->count());
    }

    #[Test]
    public function debug_info(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2]);

        $debugInfo = $map->__debugInfo(); /** @phpstan-ignore-line */
        $this->assertEquals([['a', 1], ['b', 2]], $debugInfo);
    }

    #[Test]
    public function toMutable_and_toImmutable(): void
    {
        $map = $this->mapOf(['a' => 1, 'b' => 2, 'c' => 3]);

        $immutableMap = $map->toImmutable();
        $this->assertFalse($immutableMap instanceof MutableMap);
        $this->assertSame($map->toArray(), $immutableMap->toArray());

        // since the object is guaranteed to never change, why not return itself?
        if ($map instanceof ImmutableMap) {
            $this->assertTrue(spl_object_id($immutableMap) === spl_object_id($map));
        } else {
            $this->assertFalse(spl_object_id($immutableMap) === spl_object_id($map));
        }

        $mutableMap = $map->toMutable();
        $this->assertFalse($mutableMap instanceof ImmutableMap);
        $this->assertSame($immutableMap->toArray(), $mutableMap->toArray());
        $this->assertFalse(spl_object_id($mutableMap) === spl_object_id($map));
        $mutableMap->put('d', 4);
        $this->assertFalse($mutableMap->toArray() === $map->toArray());
    }
}
