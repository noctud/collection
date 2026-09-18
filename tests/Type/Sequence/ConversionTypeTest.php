<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type\Sequence;

use Noctud\Collection\Sequence\Sequence;
use function Noctud\Collection\sequenceOf;
use function PHPStan\Testing\assertType;

/** @var Sequence<string> $s */
$s = sequenceOf(['a', 'b', 'c']);

// toMap: the value defaults to the element type unless a value transform is given.
assertType(
	'Noctud\Collection\Map\ImmutableMap<string, string>',
	$s->toMap(fn (string $x): string => $x),
);
assertType(
	'Noctud\Collection\Map\ImmutableMap<string, bool>',
	$s->toMap(fn (string $x): string => $x, fn (string $x): bool => $x !== ''),
);

// The other materializations carry E through. forEach() is a native `: void`, so it has
// nothing to pin here.
assertType('Noctud\Collection\List\ImmutableList<string>', $s->toList());
assertType('Noctud\Collection\Set\ImmutableSet<string>', $s->toSet());
assertType('list<string>', $s->toArray());

// The element type follows the pipeline rather than the source.
assertType(
	'Noctud\Collection\Map\ImmutableMap<int, float>',
	sequenceOf([1, 2])->map(fn (int $x): float => $x * 1.5)->toMap(fn (float $x): int => (int) $x),
);
