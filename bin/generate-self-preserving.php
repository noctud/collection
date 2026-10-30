<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

/**
 * Generates the bodies of the SelfPreserving{ImmutableSet,ImmutableList,ImmutableMap}Logic
 * traits: every shape-preserving method of the base immutable interface, narrowed to return
 * `static` and delegating to an aliased copy of the base implementation.
 *
 * The hand-written file headers (prose + imports + trait declaration + markers) are kept;
 * only the region between the Self-Preserving markers is regenerated.
 *
 * Usage: php generate-self-preserving.php
 */

declare(strict_types=1);

require_once __DIR__ . '/NarrowingGenerator.php';

use Noctud\Collection\Bin\NarrowingGenerator;

$collection = file_get_contents(__DIR__ . '/../src/Collection.php');
$immutableSet = file_get_contents(__DIR__ . '/../src/Set/ImmutableSet.php');
$immutableList = file_get_contents(__DIR__ . '/../src/List/ImmutableList.php');
$immutableMap = file_get_contents(__DIR__ . '/../src/Map/ImmutableMap.php');

if ($collection === false || $immutableSet === false || $immutableList === false || $immutableMap === false) {
    echo "Failed to read source files\n";
    exit(1);
}

$start = NarrowingGenerator::SelfPreservingStartMarker;
$end = NarrowingGenerator::SelfPreservingEndMarker;

// Type-changing methods (different element type, conversions, or chainable $this) are not narrowed.
$elementBlacklist = ['toImmutable', 'onEach', 'filterInstanceOf', 'map', 'mapNotNull', 'flatMap', 'flatten'];

// Set: + intersect/union/subtract (return Set, declared on Collection) and partition (array shape).
$set = NarrowingGenerator::generateSelfPreserving(
    $immutableSet,
    $collection,
    'ImmutableSetLogic',
    'ImmutableSet',
    'newCollectionOf',
    'E',
    $elementBlacklist,
    ['partition'],
    ['intersect', 'union', 'subtract'],
);
NarrowingGenerator::writeBetweenMarkers(__DIR__ . '/../src/Set/SelfPreservingImmutableSetLogic.php', $set, $start, $end);

// List: + partition. Set operations are excluded (a list's intersect/union/subtract produce a Set).
$list = NarrowingGenerator::generateSelfPreserving(
    $immutableList,
    $collection,
    'ImmutableListLogic',
    'ImmutableList',
    'newCollectionOf',
    'E',
    $elementBlacklist,
    ['partition'],
    [],
);
NarrowingGenerator::writeBetweenMarkers(__DIR__ . '/../src/List/SelfPreservingImmutableListLogic.php', $list, $start, $end);

// Map: key/value-changing methods, sortedWith (MapEntry invariance), views, and conversions excluded.
$mapBlacklist = [
    'toImmutable',
    'filterValuesInstanceOf',
    'mapKeys',
    'mapValues',
    'mapValuesNotNull',
    'flip',
    'sortedWith',
    'onEach',
    'onEachKey',
    'onEachValue',
];
$map = NarrowingGenerator::generateSelfPreserving(
    $immutableMap,
    $collection,
    'ImmutableMapLogic',
    'ImmutableMap',
    'newMapOf',
    'K,V',
    $mapBlacklist,
    [],
    [],
);
NarrowingGenerator::writeBetweenMarkers(__DIR__ . '/../src/Map/SelfPreservingImmutableMapLogic.php', $map, $start, $end);

echo "Done!\n";
