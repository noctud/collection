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
use function strlen;

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

// groupBy: buckets are lists of E, or of the transformed value when one is given.
// The key type is whatever the selector returns - strlen() narrows it to int<0, max>.
assertType(
	'Noctud\Collection\Map\ImmutableMap<int<0, max>, Noctud\Collection\List\ImmutableList<string>>',
	$s->groupBy(fn (string $x): int => strlen($x)),
);
assertType(
	'Noctud\Collection\Map\ImmutableMap<int<0, max>, Noctud\Collection\List\ImmutableList<bool>>',
	$s->groupBy(fn (string $x): int => strlen($x), fn (string $x): bool => $x !== ''),
);

// countBy keeps the key type and counts, partition and unzip hand back two lists.
assertType('Noctud\Collection\Map\ImmutableMap<int<0, max>, int>', $s->countBy(fn (string $x): int => strlen($x)));
assertType(
	'array{Noctud\Collection\List\ImmutableList<string>, Noctud\Collection\List\ImmutableList<string>}',
	$s->partition(fn (string $x): bool => $x !== ''),
);
assertType(
	'array{Noctud\Collection\List\ImmutableList<mixed>, Noctud\Collection\List\ImmutableList<mixed>}',
	$s->zip([1, 2, 3])->unzip(),
);
