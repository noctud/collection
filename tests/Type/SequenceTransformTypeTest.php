<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type;

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
