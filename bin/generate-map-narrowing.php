<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

/**
 * Generates narrowed method signatures for Map interfaces.
 * - ImmutableMap.php: narrows Map methods to ImmutableMap
 * - WritableMap.php: narrows Map transformation methods to ImmutableMap
 * - MutableMap.php: narrows WritableMap methods to MutableMap
 * - MutableTrackedMap.php: narrows MutableMap methods to MutableTrackedMap
 *
 * Usage: php generate-map-narrowing.php
 */

declare(strict_types=1);

require_once __DIR__ . '/NarrowingGenerator.php';

use Noctud\Collection\Bin\NarrowingGenerator;

$mapFile = __DIR__ . '/../src/Map/Map.php';
$immutableMapFile = __DIR__ . '/../src/Map/ImmutableMap.php';
$writableMapFile = __DIR__ . '/../src/Map/WritableMap.php';
$mutableMapFile = __DIR__ . '/../src/Map/MutableMap.php';
$trackedMapFile = __DIR__ . '/../src/Map/MutableTrackedMap.php';

// Read source files
$mapContent = file_get_contents($mapFile);

if ($mapContent === false) {
    echo "Failed to read source files\n";
    exit(1);
}

// Generate ImmutableMap narrowing (Map -> ImmutableMap)
$immutableMapNarrowing = NarrowingGenerator::generateImmutableMapNarrowing($mapContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($immutableMapFile, $immutableMapNarrowing)) {
    echo "Failed to update ImmutableMap.php\n";
    exit(1);
}

// Generate WritableMap transformation narrowing (Map -> ImmutableMap)
$writableMapNarrowing = NarrowingGenerator::generateWritableMapTransformationNarrowing($mapContent, ['onEach', 'onEachKey', 'onEachValue']);
if (!NarrowingGenerator::writeBetweenMarkers($writableMapFile, $writableMapNarrowing)) {
    echo "Failed to update WritableMap.php\n";
    exit(1);
}

// Generate MutableMap narrowing (WritableMap -> MutableMap)
$writableMapContent = file_get_contents($writableMapFile);
if ($writableMapContent === false) {
    echo "Failed to read WritableMap.php (skipping MutableMap narrowing)\n";
} else {
    // Generate WritableTrackedMap narrowing (WritableMap -> WritableTrackedMap&TrackedResult)
    $writableTrackedMapFile = __DIR__ . '/../src/Map/WritableTrackedMap.php';
    $wtmNarrowing = NarrowingGenerator::generateWritableTrackedMapNarrowing($writableMapContent, []);
    if (!NarrowingGenerator::writeBetweenMarkers($writableTrackedMapFile, $wtmNarrowing)) {
        echo "Failed to update WritableTrackedMap.php\n";
        exit(1);
    }

    $mutableMapNarrowing = NarrowingGenerator::generateMutableMapNarrowing($writableMapContent, []);
    if (!NarrowingGenerator::writeBetweenMarkers($mutableMapFile, $mutableMapNarrowing)) {
        echo "Failed to update MutableMap.php\n";
        exit(1);
    }
}

// Generate MutableTrackedMap narrowing (MutableMap -> MutableTrackedMap&TrackedResult)
// Must run after MutableMap narrowing since it reads the updated file
$mutableMapContent = file_get_contents($mutableMapFile);
if ($mutableMapContent === false) {
    echo "Failed to read MutableMap.php\n";
    exit(1);
}

$trackedMapNarrowing = NarrowingGenerator::generateTrackedMapNarrowing($mutableMapContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($trackedMapFile, $trackedMapNarrowing)) {
    echo "Failed to update MutableTrackedMap.php\n";
    exit(1);
}

echo "Done!\n";
