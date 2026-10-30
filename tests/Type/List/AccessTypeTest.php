<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type\List;

use function Noctud\Collection\listOf;
use function PHPStan\Testing\assertType;

$list = listOf([1, 2, 3]);

assertType('int', $list->get(0));
assertType('int|null', $list->getOrNull(0));
assertType("'x'|int", $list->getOrDefault(0, 'x'));
assertType("'x'|int", $list->getOrCompute(0, fn (): string => 'x'));
assertType('Noctud\Collection\List\ImmutableList<int>', $list->slice(0, 2));

// Array access via [] returns the element (offsetGet throws on a missing index).
assertType('int', $list[0]);

// toIndexedMap: value defaults to the element type unless a transform is given.
assertType('Noctud\Collection\Map\ImmutableMap<int, int>', $list->toIndexedMap());
assertType(
    'Noctud\Collection\Map\ImmutableMap<int, bool>',
    $list->toIndexedMap(fn (int $x): bool => $x > 0),
);
