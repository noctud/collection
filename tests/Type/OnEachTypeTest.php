<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type;

use Noctud\Collection\Collection;
use Noctud\Collection\ImmutableCollection;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\List\ListInterface;
use Noctud\Collection\List\MutableList;
use Noctud\Collection\List\MutableTrackedList;
use Noctud\Collection\List\WritableList;
use Noctud\Collection\List\WritableTrackedList;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\Map;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Map\MutableTrackedMap;
use Noctud\Collection\Map\WritableMap;
use Noctud\Collection\Map\WritableTrackedMap;
use Noctud\Collection\MutableCollection;
use Noctud\Collection\MutableTrackedCollection;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\MutableSet;
use Noctud\Collection\Set\MutableTrackedSet;
use Noctud\Collection\Set\Set;
use Noctud\Collection\Set\WritableSet;
use Noctud\Collection\Set\WritableTrackedSet;
use Noctud\Collection\WritableCollection;
use Noctud\Collection\WritableTrackedCollection;
use function Noctud\Collection\listOf;
use function Noctud\Collection\mapOf;
use function PHPStan\Testing\assertType;

// onEach*() is declared through @method tags on 0.1.x, so each interface pins its own narrowed type
// the way the native forEach*() declarations do.

/**
 * @param Collection<int> $collection
 * @param ImmutableCollection<int> $immutableCollection
 * @param WritableCollection<int> $writableCollection
 * @param MutableCollection<int> $mutableCollection
 * @param WritableTrackedCollection<int> $writableTrackedCollection
 * @param MutableTrackedCollection<int> $mutableTrackedCollection
 */
function collections(
	Collection $collection,
	ImmutableCollection $immutableCollection,
	WritableCollection $writableCollection,
	MutableCollection $mutableCollection,
	WritableTrackedCollection $writableTrackedCollection,
	MutableTrackedCollection $mutableTrackedCollection,
): void {
	$action = fn (int $x, int $i): null => null;

	assertType('Noctud\Collection\Collection<int>', $collection->onEach($action));
	assertType('Noctud\Collection\ImmutableCollection<int>', $immutableCollection->onEach($action));
	assertType('Noctud\Collection\WritableCollection<int>', $writableCollection->onEach($action));
	assertType('Noctud\Collection\MutableCollection<int>', $mutableCollection->onEach($action));
	assertType('Noctud\Collection\TrackedResult&Noctud\Collection\WritableTrackedCollection<int>', $writableTrackedCollection->onEach($action));
	assertType('Noctud\Collection\MutableTrackedCollection<int>&Noctud\Collection\TrackedResult', $mutableTrackedCollection->onEach($action));
}

/**
 * @param ListInterface<int> $list
 * @param ImmutableList<int> $immutableList
 * @param WritableList<int> $writableList
 * @param MutableList<int> $mutableList
 * @param WritableTrackedList<int> $writableTrackedList
 * @param MutableTrackedList<int> $mutableTrackedList
 */
function lists(
	ListInterface $list,
	ImmutableList $immutableList,
	WritableList $writableList,
	MutableList $mutableList,
	WritableTrackedList $writableTrackedList,
	MutableTrackedList $mutableTrackedList,
): void {
	$action = fn (int $x, int $i): null => null;

	assertType('Noctud\Collection\List\ListInterface<int>', $list->onEach($action));
	assertType('Noctud\Collection\List\ImmutableList<int>', $immutableList->onEach($action));
	assertType('Noctud\Collection\List\WritableList<int>', $writableList->onEach($action));
	assertType('Noctud\Collection\List\MutableList<int>', $mutableList->onEach($action));
	assertType('Noctud\Collection\List\WritableTrackedList<int>&Noctud\Collection\TrackedResult', $writableTrackedList->onEach($action));
	assertType('Noctud\Collection\List\MutableTrackedList<int>&Noctud\Collection\TrackedResult', $mutableTrackedList->onEach($action));
}

/**
 * @param Set<int> $set
 * @param ImmutableSet<int> $immutableSet
 * @param WritableSet<int> $writableSet
 * @param MutableSet<int> $mutableSet
 * @param WritableTrackedSet<int> $writableTrackedSet
 * @param MutableTrackedSet<int> $mutableTrackedSet
 */
function sets(
	Set $set,
	ImmutableSet $immutableSet,
	WritableSet $writableSet,
	MutableSet $mutableSet,
	WritableTrackedSet $writableTrackedSet,
	MutableTrackedSet $mutableTrackedSet,
): void {
	$action = fn (int $x, int $i): null => null;

	assertType('Noctud\Collection\Set\Set<int>', $set->onEach($action));
	assertType('Noctud\Collection\Set\ImmutableSet<int>', $immutableSet->onEach($action));
	assertType('Noctud\Collection\Set\WritableSet<int>', $writableSet->onEach($action));
	assertType('Noctud\Collection\Set\MutableSet<int>', $mutableSet->onEach($action));
	assertType('Noctud\Collection\Set\WritableTrackedSet<int>&Noctud\Collection\TrackedResult', $writableTrackedSet->onEach($action));
	assertType('Noctud\Collection\Set\MutableTrackedSet<int>&Noctud\Collection\TrackedResult', $mutableTrackedSet->onEach($action));
}

/**
 * @param Map<string, int> $map
 * @param ImmutableMap<string, int> $immutableMap
 * @param WritableMap<string, int> $writableMap
 * @param MutableMap<string, int> $mutableMap
 * @param WritableTrackedMap<string, int> $writableTrackedMap
 * @param MutableTrackedMap<string, int> $mutableTrackedMap
 */
function maps(
	Map $map,
	ImmutableMap $immutableMap,
	WritableMap $writableMap,
	MutableMap $mutableMap,
	WritableTrackedMap $writableTrackedMap,
	MutableTrackedMap $mutableTrackedMap,
): void {
	$entry = fn (int $v, string $k): null => null;
	$key = fn (string $k): null => null;
	$value = fn (int $v): null => null;

	assertType('Noctud\Collection\Map\Map<string, int>', $map->onEach($entry));
	assertType('Noctud\Collection\Map\Map<string, int>', $map->onEachKey($key));
	assertType('Noctud\Collection\Map\Map<string, int>', $map->onEachValue($value));

	assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $immutableMap->onEach($entry));
	assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $immutableMap->onEachKey($key));
	assertType('Noctud\Collection\Map\ImmutableMap<string, int>', $immutableMap->onEachValue($value));

	assertType('Noctud\Collection\Map\WritableMap<string, int>', $writableMap->onEach($entry));
	assertType('Noctud\Collection\Map\WritableMap<string, int>', $writableMap->onEachKey($key));
	assertType('Noctud\Collection\Map\WritableMap<string, int>', $writableMap->onEachValue($value));

	assertType('Noctud\Collection\Map\MutableMap<string, int>', $mutableMap->onEach($entry));
	assertType('Noctud\Collection\Map\MutableMap<string, int>', $mutableMap->onEachKey($key));
	assertType('Noctud\Collection\Map\MutableMap<string, int>', $mutableMap->onEachValue($value));

	assertType('Noctud\Collection\Map\WritableTrackedMap<string, int>&Noctud\Collection\TrackedResult', $writableTrackedMap->onEach($entry));
	assertType('Noctud\Collection\Map\WritableTrackedMap<string, int>&Noctud\Collection\TrackedResult', $writableTrackedMap->onEachKey($key));
	assertType('Noctud\Collection\Map\WritableTrackedMap<string, int>&Noctud\Collection\TrackedResult', $writableTrackedMap->onEachValue($value));

	assertType('Noctud\Collection\Map\MutableTrackedMap<string, int>&Noctud\Collection\TrackedResult', $mutableTrackedMap->onEach($entry));
	assertType('Noctud\Collection\Map\MutableTrackedMap<string, int>&Noctud\Collection\TrackedResult', $mutableTrackedMap->onEachKey($key));
	assertType('Noctud\Collection\Map\MutableTrackedMap<string, int>&Noctud\Collection\TrackedResult', $mutableTrackedMap->onEachValue($value));
}

// A chain keeps narrowing past the tap, which is the point of onEach() over forEach() in 0.2.
function chains(): void
{
	assertType(
		'Noctud\Collection\List\ImmutableList<int>',
		listOf([1, 2, 3])->onEach(fn (int $x): null => null)->filter(fn (int $x): bool => $x > 1),
	);
	assertType(
		'Noctud\Collection\Map\ImmutableMap<string, int>',
		mapOf(['a' => 1])->onEachValue(fn (int $v): null => null)->filter(fn (int $v): bool => $v > 0),
	);
}
