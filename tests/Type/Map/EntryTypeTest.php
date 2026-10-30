<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type\Map;

use function Noctud\Collection\mapOf;
use function PHPStan\Testing\assertType;

$map = mapOf(['a' => 1, 'b' => 2]);

// The entries view of a map is a set of MapEntry.
assertType('Noctud\Collection\Set\Set<Noctud\Collection\Map\MapEntry<string, int>>', $map->entries);
assertType('Noctud\Collection\Map\MapEntry<string, int>|null', $map->entries->firstOrNull());

// MapEntry exposes key/value through virtual property hooks (generic K/V, not the
// declared `string|int|bool|float|object`/`mixed`).
$entry = $map->entries->firstOrNull();
if ($entry !== null) {
    assertType('string', $entry->key);
    assertType('int', $entry->value);
}
