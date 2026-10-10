<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type\List;

use Noctud\Collection\List\ListInterface;
use stdClass;
use function Noctud\Collection\listOf;
use function Noctud\Collection\mutableListOf;
use function PHPStan\Testing\assertType;

$imm = listOf([1, 2, 3]);

// Shape-preserving transforms narrow back to ImmutableList.
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->filter(fn (int $x): bool => $x > 0));
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->filterNotNull());
assertType('Noctud\Collection\List\ImmutableList<int>', listOf([1, null])->filterNotNull());
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->takeFirst(2));
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->dropLast(1));
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->takeWhile(fn (int $x): bool => $x > 0));
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->distinct());
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->distinctBy(fn (int $x): int => $x));
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->sorted());
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->sortedBy(fn (int $x): int => $x));
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->reversed());
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->shuffled());
assertType('Noctud\Collection\List\ImmutableList<int>', $imm->slice(0, 2));
assertType(
	'array{Noctud\Collection\List\ImmutableList<int>, Noctud\Collection\List\ImmutableList<int>}',
	$imm->partition(fn (int $x): bool => $x > 0),
);

// Type-changing transforms move to the new element type but stay ImmutableList.
assertType('Noctud\Collection\List\ImmutableList<bool>', $imm->map(fn (int $x): bool => $x > 0));
assertType('Noctud\Collection\List\ImmutableList<stdClass>', $imm->filterInstanceOf(stdClass::class));

// flatten() extracts the element type of iterable elements (one level).
assertType('Noctud\Collection\List\ImmutableList<int>', listOf([listOf([1, 2]), listOf([3])])->flatten());

// Only one level is flattened.
assertType(
	'Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<int>>',
	listOf([listOf([listOf([1])])])->flatten(),
);

// Non-iterable elements are kept as-is.
assertType('Noctud\Collection\List\ImmutableList<string>', listOf(['a', 'b'])->flatten());

// flatten() on an empty collection stays typed (E = never).
assertType('Noctud\Collection\List\ImmutableList<*NEVER*>', listOf([])->flatten());

// chunked/windowed always produce an immutable list of immutable lists. Collection declares
// that nested type itself: the element type is invariant, so a sub-interface or trait could
// not narrow ListInterface<ListInterface<E>> to it without breaking variance.
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<int>>', $imm->chunked(2));

// A mutable list's transforms produce a fresh immutable list.
$mut = mutableListOf([1, 2, 3]);
assertType('Noctud\Collection\List\ImmutableList<int>', $mut->filter(fn (int $x): bool => $x > 0));
assertType('Noctud\Collection\List\ImmutableList<bool>', $mut->map(fn (int $x): bool => $x > 0));
assertType('Noctud\Collection\List\ImmutableList<int>', $mut->sorted());
assertType('Noctud\Collection\List\ImmutableList<int>', mutableListOf([1, null])->filterNotNull());
assertType('Noctud\Collection\List\ImmutableList<int>', mutableListOf([listOf([1, 2])])->flatten());

// The base ListInterface contract keeps everything at the ListInterface level.
/** @var ListInterface<int> $list */
$list = listOf([1, 2, 3]);
assertType('Noctud\Collection\List\ListInterface<int>', $list->filter(fn (int $x): bool => $x > 0));
assertType('Noctud\Collection\List\ListInterface<bool>', $list->map(fn (int $x): bool => $x > 0));
assertType('Noctud\Collection\List\ListInterface<int>', $list->sorted());
