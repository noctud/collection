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

/** @var Sequence<int> $s */
$s = sequenceOf([1, 2, 3]);

// fold: the return type follows the initial accumulator type R.
assertType('int', $s->fold(0, fn (int $acc, int $x): int => $acc + $x));
assertType('string', $s->fold('', fn (string $acc, int $x): string => $acc . $x));

assertType('int', $s->reduce(fn (int $acc, int $x): int => $acc + $x));
assertType('int|null', $s->reduceOrNull(fn (int $acc, int $x): int => $acc + $x));

// min/max hand back the element type E, declared `: mixed`, not a fixed scalar.
assertType('int', $s->min());
assertType('int|null', $s->minOrNull());
assertType('int', $s->max());
assertType('int|null', $s->maxOrNull());

// minOf/maxOf hand back the selector's type R instead of mixed.
$strings = sequenceOf(['a', 'bb']);
assertType('int', $strings->minOf(fn (string $x): int => (int) $x));
assertType('float', $strings->maxOf(fn (string $x): float => (float) $x));
assertType('int|null', $strings->minOfOrNull(fn (string $x): int => (int) $x));
assertType('int|null', $strings->maxOfOrNull(fn (string $x): int => (int) $x));

// sum: the conditional return type narrows to int for a sequence of ints, and widens as soon as
// a float is involved - on either side of the condition.
assertType('int', sequenceOf([1, 2, 3])->sum());
assertType('float|int', sequenceOf([1.0, 2.0])->sum());
assertType('int', sequenceOf([1, 2, 3])->sum(fn (int $x): int => $x * 2));
assertType('float|int', sequenceOf([1, 2, 3])->sum(fn (int $x): float => $x / 2));

// The element type follows the pipeline rather than the source.
assertType('float|int', sequenceOf([1, 2, 3])->map(fn (int $x): float => $x * 1.5)->sum());
