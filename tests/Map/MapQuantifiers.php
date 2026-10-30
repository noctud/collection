<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

trait MapQuantifiers
{
    #[Test]
    #[DataProvider('quantifierProvider')]
    public function all_any_none(array $data, Closure $pairPred, Closure $keyPred, Closure $valPred, bool $allPairs, bool $anyPair, bool $allKeys, bool $anyKey, bool $allValues, bool $anyValue): void
    {
        $map = $this->mapOf($data);

        $this->assertSame($allPairs, $map->all($pairPred));
        $this->assertSame($anyPair, $map->any($pairPred));
        $this->assertSame(!$anyPair, $map->none($pairPred));

        $this->assertSame($allKeys, $map->keys->all($keyPred));
        $this->assertSame($anyKey, $map->keys->any($keyPred));
        $this->assertSame(!$anyKey, $map->keys->none($keyPred));

        $this->assertSame($allValues, $map->values->all($valPred));
        $this->assertSame($anyValue, $map->values->any($valPred));
        $this->assertSame(!$anyValue, $map->values->none($valPred));
    }

    public static function quantifierProvider(): iterable
    {
        yield 'empty' => [
            'data' => [],
            'pairPred' => fn ($v, $k) => true,
            'keyPred' => fn ($k) => true,
            'valPred' => fn ($v) => true,
            'allPairs' => true,
            'anyPair' => false,
            'allKeys' => true,
            'anyKey' => false,
            'allValues' => true,
            'anyValue' => false,
        ];

        yield 'simple' => [
            'data' => ['a' => 1, 'b' => 2, 'c' => 3],
            'pairPred' => fn (int $v, string $k) => $v > 0 && $k !== '',
            'keyPred' => fn (string $k) => $k !== 'x',
            'valPred' => fn (int $v) => $v % 2 === 0,
            'allPairs' => true,
            'anyPair' => true,
            'allKeys' => true,
            'anyKey' => true,
            'allValues' => false,
            'anyValue' => true,
        ];

        yield 'all returns false' => [
            'data' => ['x' => 1, 'y' => 2, 'z' => 3],
            'pairPred' => fn (int $v, string $k) => $v < 3,
            'keyPred' => fn (string $k) => $k !== 'z',
            'valPred' => fn (int $v) => $v > 10,
            'allPairs' => false,
            'anyPair' => true,
            'allKeys' => false,
            'anyKey' => true,
            'allValues' => false,
            'anyValue' => false,
        ];
    }
}
