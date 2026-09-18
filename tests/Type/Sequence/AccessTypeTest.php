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

// The throwing element accessors return the element type itself.
assertType('string', $s->first());
assertType('string', $s->last());
assertType('string', $s->single());
assertType('string', $s->expect(static fn (string $v): bool => $v !== 'a'));
assertType('string', $s->expectLast(static fn (string $v): bool => $v !== 'a'));

// Their OrNull counterparts widen it with null, find() included.
assertType('string|null', $s->firstOrNull());
assertType('string|null', $s->lastOrNull());
assertType('string|null', $s->singleOrNull());
assertType('string|null', $s->find(static fn (string $v): bool => $v !== 'a'));
assertType('string|null', $s->findLast(static fn (string $v): bool => $v !== 'a'));

// Index access carries the element type, and widens with null in the OrNull variant.
assertType('string', $s->elementAt(1));
assertType('string|null', $s->elementAtOrNull(1));

// The element type follows the pipeline rather than the source.
assertType('float', sequenceOf([1, 2, 3])->map(static fn (int $v): float => $v * 2.5)->first());
assertType('int|null', sequenceOf([1, 2, 3])->filter(static fn (int $v): bool => $v > 1)->firstOrNull());

/** @var Sequence<string|null> $nullable */
$nullable = sequenceOf(['a', null]);

// filterNotNull() narrows what the accessors can hand back.
assertType('string|null', $nullable->firstOrNull());
assertType('string', $nullable->filterNotNull()->first());
