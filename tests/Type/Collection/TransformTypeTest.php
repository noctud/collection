<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type\Collection;

use Noctud\Collection\Collection;
use stdClass;
use function Noctud\Collection\listOf;
use function PHPStan\Testing\assertType;

/** @var Collection<string> $c */
$c = listOf(['a', 'b', 'c']);

// Filtering preserves the element type.
assertType('Noctud\Collection\Collection<string>', $c->filter(fn (string $x): bool => $x !== ''));
assertType('Noctud\Collection\Collection<string>', $c->filterNotNull());
assertType('Noctud\Collection\Collection<stdClass>', $c->filterInstanceOf(stdClass::class));

/** @var Collection<string|null> $nullable */
$nullable = listOf(['a', null]);
assertType('Noctud\Collection\Collection<string>', $nullable->filterNotNull());

// Mapping changes the element type.
assertType('Noctud\Collection\Collection<int>', $c->map(fn (string $x): int => (int) $x));
assertType('Noctud\Collection\Collection<int>', $c->mapNotNull(fn (string $x): ?int => $x !== '' ? 1 : null));
assertType('Noctud\Collection\Collection<int>', $c->flatMap(fn (string $x): array => [(int) $x]));

// flatten() keeps non-iterable elements as-is; see FlattenTypeTest for the nesting cases.
assertType('Noctud\Collection\Collection<string>', $c->flatten());

// Slicing preserves the element type.
assertType('Noctud\Collection\Collection<string>', $c->takeFirst(2));
assertType('Noctud\Collection\Collection<string>', $c->dropFirst(2));
assertType('Noctud\Collection\Collection<string>', $c->takeLast(2));
assertType('Noctud\Collection\Collection<string>', $c->dropLast(2));
assertType('Noctud\Collection\Collection<string>', $c->takeWhile(fn (string $x): bool => $x !== ''));
assertType('Noctud\Collection\Collection<string>', $c->dropWhile(fn (string $x): bool => $x !== ''));
assertType('Noctud\Collection\Collection<string>', $c->takeLastWhile(fn (string $x): bool => $x !== ''));
assertType('Noctud\Collection\Collection<string>', $c->dropLastWhile(fn (string $x): bool => $x !== ''));
assertType('Noctud\Collection\Collection<string>', $c->distinct());
assertType('Noctud\Collection\Collection<string>', $c->distinctBy(fn (string $x): int => (int) $x));

// Grouping into lists of lists.
assertType('Noctud\Collection\List\ListInterface<Noctud\Collection\List\ListInterface<string>>', $c->chunked(2));
assertType('Noctud\Collection\List\ListInterface<Noctud\Collection\List\ListInterface<string>>', $c->windowed(2));
assertType('Noctud\Collection\List\ListInterface<array{string, int}>', $c->zip([1, 2]));
assertType('Noctud\Collection\List\ListInterface<array{string, string}>', $c->zipWithNext());
assertType('array{Noctud\Collection\List\ImmutableList<mixed>, Noctud\Collection\List\ImmutableList<mixed>}', $c->unzip());
assertType('array{Noctud\Collection\Collection<string>, Noctud\Collection\Collection<string>}', $c->partition(fn (string $x): bool => $x !== ''));

// groupBy is conditional on the value transform.
assertType(
	'Noctud\Collection\Map\ImmutableMap<string, Noctud\Collection\Collection<string>>',
	$c->groupBy(fn (string $x): string => $x),
);
assertType(
	'Noctud\Collection\Map\ImmutableMap<string, Noctud\Collection\List\ImmutableList<int>>',
	$c->groupBy(fn (string $x): string => $x, fn (string $x): int => (int) $x),
);

// Set operations.
assertType('Noctud\Collection\Set\Set<string>', $c->intersect(['a', 'b']));
assertType('Noctud\Collection\Set\Set<string>', $c->union(['a', 'b']));
assertType('Noctud\Collection\Set\Set<string>', $c->subtract(['a', 'b']));
