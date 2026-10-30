<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type\Map;

use stdClass;
use function Noctud\Collection\mapOf;
use function Noctud\Collection\mutableStringMapOf;
use function Noctud\Collection\stringMapOf;
use function PHPStan\Testing\assertType;

$map = mapOf(['a' => 1, 'b' => 2]);

// Filtering preserves both key and value types and narrows back to ImmutableMap.
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->filter(fn (int $v, string $k): bool => $v > 0));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->filterKeys(fn (string $k): bool => $k !== ''));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->filterValues(fn (int $v): bool => $v > 0));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->filterValuesNotNull());
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', stringMapOf(['a' => 1, 'b' => null])->filterValuesNotNull());
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', mutableStringMapOf(['a' => 1, 'b' => null])->filterValuesNotNull());
assertType(
    'Noctud\Collection\Map\ImmutableMap<string, stdClass>',
    $map->filterValuesInstanceOf(stdClass::class),
);

// mapKeys changes the key type; mapValues changes the value type.
assertType(
    'Noctud\Collection\Map\ImmutableMap<int, int>',
    $map->mapKeys(fn (int $v, string $k): int => $v),
);
assertType(
    'Noctud\Collection\Map\ImmutableMap<string, bool>',
    $map->mapValues(fn (int $v, string $k): bool => $v > 0),
);
assertType(
    'Noctud\Collection\Map\ImmutableMap<string, bool>',
    $map->mapValuesNotNull(fn (int $v, string $k): ?bool => $v > 0 ? true : null),
);

// flip swaps keys and values.
assertType('Noctud\Collection\Map\ImmutableMap<int, string>', $map->flip());

// Ordering keeps the key/value types.
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->sortedByKey());
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->sortedByKeyDesc());
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->sortedByValue());
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->sortedByValueDesc());
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->sortedBy(fn (int $v, string $k): int => $v));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->sortedByDesc(fn (int $v, string $k): int => $v));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->sortedWithKey(fn (string $a, string $b): int => $a <=> $b));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->sortedWithValue(fn (int $a, int $b): int => $a <=> $b));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->sortedWith(fn ($a, $b): int => 0));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->reversed());
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->shuffled());

// Slicing keeps the key/value types.
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->takeFirst(1));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->takeLast(1));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->dropFirst(1));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->dropLast(1));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->takeWhile(fn (int $v, string $k): bool => $v > 0));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->dropWhile(fn (int $v, string $k): bool => $v > 0));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->takeLastWhile(fn (int $v, string $k): bool => $v > 0));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $map->dropLastWhile(fn (int $v, string $k): bool => $v > 0));
