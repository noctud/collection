<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type\Sequence;

use Noctud\Collection\Sequence\Sequence;
use stdClass;
use function Noctud\Collection\sequenceOf;
use function PHPStan\Testing\assertType;

// filter() preserves the element type.
assertType('Noctud\Collection\Sequence\Sequence<int>', sequenceOf([1, 2, 3])->filter(static fn (int $v): bool => $v > 1));

// map() infers the new element type from the transform return.
assertType('Noctud\Collection\Sequence\Sequence<bool>', sequenceOf([1, 2, 3])->map(static fn (int $v): bool => $v > 1));

// A fused filter->map chain carries types through both stages.
assertType(
    'Noctud\Collection\Sequence\Sequence<float>',
    sequenceOf([1, 2, 3])->filter(static fn (int $v): bool => $v > 1)->map(static fn (int $v): float => $v * 2.5),
);

/** @var Sequence<string> $s */
$s = sequenceOf(['a', 'b', 'c']);

// Filtering preserves the element type.
assertType('Noctud\Collection\Sequence\Sequence<string>', $s->filterNotNull());
assertType('Noctud\Collection\Sequence\Sequence<stdClass>', $s->filterInstanceOf(stdClass::class));

/** @var Sequence<string|null> $nullable */
$nullable = sequenceOf(['a', null]);
assertType('Noctud\Collection\Sequence\Sequence<string>', $nullable->filterNotNull());

// Mapping changes the element type.
assertType('Noctud\Collection\Sequence\Sequence<int>', $s->mapNotNull(static fn (string $x): ?int => $x !== '' ? 1 : null));
assertType('Noctud\Collection\Sequence\Sequence<int>', $s->flatMap(static fn (string $x): array => [(int) $x]));

// flatten() keeps non-iterable elements as-is.
assertType('Noctud\Collection\Sequence\Sequence<string>', $s->flatten());

/** @var Sequence<array<int>> $arrays */
$arrays = sequenceOf([[1, 2], [3]]);
assertType('Noctud\Collection\Sequence\Sequence<int>', $arrays->flatten());

// ...and the conditional distributes over union element types.
/** @var Sequence<array<int>|string> $mixed */
$mixed = sequenceOf([[1], 'a']);
assertType('Noctud\Collection\Sequence\Sequence<int|string>', $mixed->flatten());

// Slicing preserves the element type.
assertType('Noctud\Collection\Sequence\Sequence<string>', $s->takeFirst(2));
assertType('Noctud\Collection\Sequence\Sequence<string>', $s->dropFirst(2));
assertType('Noctud\Collection\Sequence\Sequence<string>', $s->takeWhile(static fn (string $x): bool => $x !== ''));
assertType('Noctud\Collection\Sequence\Sequence<string>', $s->dropWhile(static fn (string $x): bool => $x !== ''));
assertType('Noctud\Collection\Sequence\Sequence<string>', $s->distinct());
assertType('Noctud\Collection\Sequence\Sequence<string>', $s->distinctBy(static fn (string $x): int => (int) $x));

// Pairing yields tuples, and stays a Sequence (the Collection counterparts return a list).
assertType('Noctud\Collection\Sequence\Sequence<array{string, int}>', $s->zip([1, 2]));
assertType('Noctud\Collection\Sequence\Sequence<array{string, string}>', $s->zipWithNext());

// onEach() is a pass-through tap.
assertType('Noctud\Collection\Sequence\Sequence<string>', $s->onEach(static fn (string $x): null => null));

// chunked()/windowed() group elements into lists without leaving the lazy world.
assertType('Noctud\Collection\Sequence\Sequence<Noctud\Collection\List\ImmutableList<string>>', $s->chunked(2));
assertType('Noctud\Collection\Sequence\Sequence<Noctud\Collection\List\ImmutableList<string>>', $s->windowed(3, 1, true));
