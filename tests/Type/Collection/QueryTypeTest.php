<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type\Collection;

use Noctud\Collection\Collection;
use function Noctud\Collection\listOf;
use function PHPStan\Testing\assertType;

/** @var Collection<string> $c */
$c = listOf(['a', 'b', 'c']);

// countWhere() pins the declared @return int<0, max>.
// count() itself is native-typed (`: int`, no PHPDoc) and needs no type test.
assertType('int<0, max>', $c->countWhere(fn (string $x): bool => $x !== ''));

// countBy: the key selector's return becomes the map key, values are the counts.
assertType(
    'Noctud\Collection\Map\ImmutableMap<string, int>',
    $c->countBy(fn (string $x): string => $x),
);
