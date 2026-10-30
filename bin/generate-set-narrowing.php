<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

/**
 * Generates narrowed method signatures for Set interfaces.
 * - Set.php: narrows Collection methods to Set
 * - ImmutableSet.php: narrows ImmutableCollection methods to ImmutableSet
 * - WritableSet.php: narrows WritableCollection mutation + Collection transformation to WritableSet/ImmutableSet
 * - MutableSet.php: narrows MutableCollection methods to MutableSet
 *
 * Usage: php generate-set-narrowing.php
 */

declare(strict_types=1);

require_once __DIR__ . '/NarrowingGenerator.php';

use Noctud\Collection\Bin\NarrowingGenerator;

$collectionFile = __DIR__ . '/../src/Collection.php';
$immutableCollectionFile = __DIR__ . '/../src/ImmutableCollection.php';
$writableCollectionFile = __DIR__ . '/../src/WritableCollection.php';
$mutableCollectionFile = __DIR__ . '/../src/MutableCollection.php';

$setFile = __DIR__ . '/../src/Set/Set.php';
$immutableSetFile = __DIR__ . '/../src/Set/ImmutableSet.php';
$writableSetFile = __DIR__ . '/../src/Set/WritableSet.php';
$mutableSetFile = __DIR__ . '/../src/Set/MutableSet.php';

// Read source files
$collectionContent = file_get_contents($collectionFile);
$immutableCollectionContent = file_get_contents($immutableCollectionFile);
$writableCollectionContent = file_get_contents($writableCollectionFile);
$mutableCollectionContent = file_get_contents($mutableCollectionFile);

if ($collectionContent === false || $immutableCollectionContent === false || $writableCollectionContent === false || $mutableCollectionContent === false) {
    echo "Failed to read source files\n";
    exit(1);
}

// Generate Set narrowing (Collection -> Set)
$setNarrowing = NarrowingGenerator::generateSetNarrowing($collectionContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($setFile, $setNarrowing)) {
    echo "Failed to update Set.php\n";
    exit(1);
}

// Generate ImmutableSet narrowing (ImmutableCollection -> ImmutableSet)
$immutableSetNarrowing = NarrowingGenerator::generateImmutableSetNarrowing($immutableCollectionContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($immutableSetFile, $immutableSetNarrowing)) {
    echo "Failed to update ImmutableSet.php\n";
    exit(1);
}

// Generate WritableSet narrowing:
// 1. Mutation narrowing (WritableCollection -> WritableSet)
// 2. Transformation narrowing (Collection -> ImmutableSet)
$writableSetMutationNarrowing = NarrowingGenerator::generateWritableSetNarrowing($writableCollectionContent, []);
$writableSetTransformationNarrowing = NarrowingGenerator::generateWritableSetTransformationNarrowing($collectionContent, ['onEach']);
$writableSetNarrowing = $writableSetMutationNarrowing . "\n\n" . $writableSetTransformationNarrowing;
if (!NarrowingGenerator::writeBetweenMarkers($writableSetFile, $writableSetNarrowing)) {
    echo "Failed to update WritableSet.php\n";
    exit(1);
}

// Generate WritableTrackedSet narrowing (WritableSet -> WritableTrackedSet&TrackedResult)
// Must run after WritableSet narrowing since it reads the updated file
$writableTrackedSetFile = __DIR__ . '/../src/Set/WritableTrackedSet.php';
$writableSetContent = file_get_contents($writableSetFile);
if ($writableSetContent === false) {
    echo "Failed to read WritableSet.php\n";
    exit(1);
}

$wtsNarrowing = NarrowingGenerator::generateWritableTrackedSetNarrowing($writableSetContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($writableTrackedSetFile, $wtsNarrowing)) {
    echo "Failed to update WritableTrackedSet.php\n";
    exit(1);
}

// Generate MutableSet narrowing (MutableCollection -> MutableSet)
$mutableSetNarrowing = NarrowingGenerator::generateMutableSetNarrowing($mutableCollectionContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($mutableSetFile, $mutableSetNarrowing)) {
    echo "Failed to update MutableSet.php\n";
    exit(1);
}

// Generate MutableTrackedSet narrowing (MutableSet -> MutableTrackedSet&TrackedResult)
// Must run after MutableSet narrowing since it reads the updated file
$trackedSetFile = __DIR__ . '/../src/Set/MutableTrackedSet.php';
$mutableSetContent = file_get_contents($mutableSetFile);
if ($mutableSetContent === false) {
    echo "Failed to read MutableSet.php\n";
    exit(1);
}

$trackedSetNarrowing = NarrowingGenerator::generateTrackedSetNarrowing($mutableSetContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($trackedSetFile, $trackedSetNarrowing)) {
    echo "Failed to update MutableTrackedSet.php\n";
    exit(1);
}

echo "Done!\n";
