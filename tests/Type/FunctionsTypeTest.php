<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type;

use function Noctud\Collection\generateSequence;
use function Noctud\Collection\intMapOf;
use function Noctud\Collection\listOf;
use function Noctud\Collection\mapOf;
use function Noctud\Collection\mapOfPairs;
use function Noctud\Collection\mutableIntMapOf;
use function Noctud\Collection\mutableListOf;
use function Noctud\Collection\mutableMapOf;
use function Noctud\Collection\mutableMapOfPairs;
use function Noctud\Collection\mutableSetOf;
use function Noctud\Collection\mutableStringMapOf;
use function Noctud\Collection\setOf;
use function Noctud\Collection\stringMapOf;
use function PHPStan\Testing\assertType;
use function strlen;

// Lists
assertType('Noctud\Collection\List\ImmutableList<string>', listOf(['a', 'b']));
assertType('Noctud\Collection\List\MutableList<int>', mutableListOf([1, 2]));

// Sets
assertType('Noctud\Collection\Set\ImmutableSet<string>', setOf(['a', 'b']));
assertType('Noctud\Collection\Set\MutableSet<int>', mutableSetOf([1, 2]));

// Hash maps (assoc + pairs)
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', mapOf(['a' => 1, 'b' => 2]));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', mapOfPairs([['a', 1], ['b', 2]]));
assertType('Noctud\Collection\Map\MutableMap<string, int>', mutableMapOf(['a' => 1]));
assertType('Noctud\Collection\Map\MutableMap<string, int>', mutableMapOfPairs([['a', 1]]));

// String maps
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', stringMapOf(['a' => 1]));
assertType('Noctud\Collection\Map\MutableMap<string, int>', mutableStringMapOf(['a' => 1]));

// Int maps
assertType('Noctud\Collection\Map\ImmutableMap<int, string>', intMapOf([1 => 'a']));
assertType('Noctud\Collection\Map\MutableMap<int, string>', mutableIntMapOf([1 => 'a']));

// Sequences - the element type comes from the seed, the next function walks it forward.
/** @var string $seed */
$seed = 'a';
assertType(
    'Noctud\Collection\Sequence\Sequence<string>',
    generateSequence($seed, static fn (string $v): ?string => strlen($v) < 3 ? $v . 'a' : null),
);
