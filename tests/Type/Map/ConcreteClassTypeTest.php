<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type\Map;

use Noctud\Collection\Map\HashMap\ImmutableHashMap;
use Noctud\Collection\Map\HashMap\MutableHashMap;
use Noctud\Collection\Map\IntMap\ImmutableIntMap;
use Noctud\Collection\Map\IntMap\MutableIntMap;
use Noctud\Collection\Map\StringMap\ImmutableStringMap;
use Noctud\Collection\Map\StringMap\MutableStringMap;
use Noctud\Collection\Tests\Map\Extending\ManualScoreBoard;
use function Noctud\Collection\mutableIntMapOf;
use function Noctud\Collection\mutableMapOf;
use function Noctud\Collection\mutableStringMapOf;
use function PHPStan\Testing\assertType;

// The other type tests reach the maps through their interfaces (mapOf(), @var).
// A value typed as a concrete class resolves its methods from the logic traits instead,
// so a trait docblock that diverges from the interface shows up only here.

/** @var ImmutableHashMap<string, int> $hashMap */
$hashMap = ImmutableHashMap::of(['a' => 1]);
/** @var ImmutableIntMap<int> $intMap */
$intMap = new ImmutableIntMap([1 => 1]);
/** @var ImmutableStringMap<int> $stringMap */
$stringMap = new ImmutableStringMap(['a' => 1]);
/** @var MutableHashMap<string, int> $mutableHashMap */
$mutableHashMap = mutableMapOf(['a' => 1]);
/** @var MutableIntMap<int> $mutableIntMap */
$mutableIntMap = mutableIntMapOf([1 => 1]);
/** @var MutableStringMap<int> $mutableStringMap */
$mutableStringMap = mutableStringMapOf(['a' => 1]);
$scoreBoard = new ManualScoreBoard(['a' => 1]);

// Clean scalars, to observe widening.
$anInt = $hashMap->get('a');
$aBool = $hashMap->isEmpty();
/** @var string $aString */
$aString = 'b';

// Immutable puts keep the types of a same-typed entry...
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $hashMap->put('b', $anInt));
assertType('int', $hashMap->put('b', $anInt)->get('b'));

// ...and widen them like the interface does.
assertType('Noctud\Collection\Map\ImmutableMap<int|string, bool|int>', $hashMap->put($anInt, $aBool));
assertType('Noctud\Collection\Map\ImmutableMap<int|string, bool|int>', $hashMap->putFirst($anInt, $aBool));
assertType('Noctud\Collection\Map\ImmutableMap<int|string, bool|int>', $hashMap->putIfAbsent($anInt, $aBool));
assertType('Noctud\Collection\Map\ImmutableMap<int|string, bool|int>', $hashMap->putAll([$anInt => $aBool]));
assertType('Noctud\Collection\Map\ImmutableMap<int|string, bool|int>', $hashMap->putAllPairs([[$anInt, $aBool]]));

// A class built on the base trait that rebuilds itself declares put() with @method, which
// keeps a foreign entry out and the result typed as the class.
assertType('Noctud\Collection\Tests\Map\Extending\ManualScoreBoard', $scoreBoard->put('b', $anInt));

// Lookups and removals accept any key or value, as the interfaces do.
assertType('bool', $hashMap->containsKey($anInt));
assertType('bool', $hashMap->containsValue($aBool));
assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $hashMap->remove($anInt));
assertType('Noctud\Collection\Map\MutableMap<string, int>', $mutableHashMap->remove($anInt));

// IntMap and StringMap fall back to a hash map for a foreign key type.
assertType('Noctud\Collection\Map\ImmutableMap<int|string, bool|int>', $intMap->putIfAbsent($aString, $aBool));
assertType('Noctud\Collection\Map\ImmutableMap<int|string, bool|int>', $stringMap->putIfAbsent($anInt, $aBool));

// toArray() keeps an array-key key type, on mutable maps too.
assertType('array<string, int>', $hashMap->toArray());
assertType('array<int, int>', $intMap->toArray());
assertType('array<string, int>', $stringMap->toArray());
assertType('array<string, int>', $mutableHashMap->toArray());
assertType('array<int, int>', $mutableIntMap->toArray());
assertType('array<string, int>', $mutableStringMap->toArray());
