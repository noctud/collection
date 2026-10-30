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

// toMap: value defaults to the element type unless a value transform is given.
assertType(
    'Noctud\Collection\Map\ImmutableMap<string, string>',
    $c->toMap(fn (string $x): string => $x),
);
assertType(
    'Noctud\Collection\Map\ImmutableMap<string, bool>',
    $c->toMap(fn (string $x): string => $x, fn (string $x): bool => $x !== ''),
);

assertType('Noctud\Collection\List\ImmutableList<string>', $c->toList());
assertType('Noctud\Collection\Set\ImmutableSet<string>', $c->toSet());
assertType('list<string>', $c->toArray());
assertType('Noctud\Collection\Sequence\Sequence<string>', $c->asSequence());
assertType('Noctud\Collection\MutableCollection<string>', $c->toMutable());
assertType('Noctud\Collection\ImmutableCollection<string>', $c->toImmutable());
// onEach() is a pass-through tap; forEach() is a native `: void`, so it has nothing to pin here.
assertType('Noctud\Collection\Collection<string>', $c->onEach(fn (string $x): null => null));
