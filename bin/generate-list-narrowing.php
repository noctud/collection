<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

/**
 * Generates narrowed method signatures for List interfaces.
 * - ListInterface.php: narrows Collection methods to ListInterface
 * - ImmutableList.php: narrows ImmutableCollection methods to ImmutableList
 * - WritableList.php: narrows WritableCollection mutation + Collection transformation to WritableList/ImmutableList
 * - MutableList.php: narrows MutableCollection + WritableList methods to MutableList
 *
 * Usage: php generate-list-narrowing.php
 */

declare(strict_types=1);

require_once __DIR__ . '/NarrowingGenerator.php';

use Noctud\Collection\Bin\NarrowingGenerator;

$collectionFile = __DIR__ . '/../src/Collection.php';
$immutableCollectionFile = __DIR__ . '/../src/ImmutableCollection.php';
$writableCollectionFile = __DIR__ . '/../src/WritableCollection.php';
$mutableCollectionFile = __DIR__ . '/../src/MutableCollection.php';

$listFile = __DIR__ . '/../src/List/ListInterface.php';
$immutableListFile = __DIR__ . '/../src/List/ImmutableList.php';
$writableListFile = __DIR__ . '/../src/List/WritableList.php';
$mutableListFile = __DIR__ . '/../src/List/MutableList.php';

// Read source files
$collectionContent = file_get_contents($collectionFile);
$immutableCollectionContent = file_get_contents($immutableCollectionFile);
$writableCollectionContent = file_get_contents($writableCollectionFile);
$mutableCollectionContent = file_get_contents($mutableCollectionFile);

if ($collectionContent === false || $immutableCollectionContent === false || $writableCollectionContent === false || $mutableCollectionContent === false) {
    echo "Failed to read source files\n";
    exit(1);
}

// Generate ListInterface narrowing (Collection -> ListInterface)
$listNarrowing = NarrowingGenerator::generateListNarrowing($collectionContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($listFile, $listNarrowing)) {
    echo "Failed to update ListInterface.php\n";
    exit(1);
}

// Generate ImmutableList narrowing (ImmutableCollection -> ImmutableList)
$immutableListNarrowing = NarrowingGenerator::generateImmutableListNarrowing($immutableCollectionContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($immutableListFile, $immutableListNarrowing)) {
    echo "Failed to update ImmutableList.php\n";
    exit(1);
}

// Generate WritableList narrowing:
// 1. Mutation narrowing (WritableCollection -> WritableList)
// 2. Transformation narrowing (Collection -> ImmutableList)
$writableListMutationNarrowing = NarrowingGenerator::generateWritableListNarrowing($writableCollectionContent, []);
$writableListTransformationNarrowing = NarrowingGenerator::generateWritableListTransformationNarrowing($collectionContent, ['onEach']);
$writableListNarrowing = $writableListMutationNarrowing . "\n\n" . $writableListTransformationNarrowing;
if (!NarrowingGenerator::writeBetweenMarkers($writableListFile, $writableListNarrowing)) {
    echo "Failed to update WritableList.php\n";
    exit(1);
}

// Generate MutableList narrowing (MutableCollection + WritableList -> MutableList)
// Must run after WritableList narrowing since it reads the updated file
$writableListContent = file_get_contents($writableListFile);
if ($writableListContent === false) {
    echo "Failed to read WritableList.php\n";
    exit(1);
}

// Generate WritableTrackedList narrowing (WritableList -> WritableTrackedList&TrackedResult)
$writableTrackedListFile = __DIR__ . '/../src/List/WritableTrackedList.php';
$wtlNarrowing = NarrowingGenerator::generateWritableTrackedListNarrowing($writableListContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($writableTrackedListFile, $wtlNarrowing)) {
    echo "Failed to update WritableTrackedList.php\n";
    exit(1);
}

$mutableListNarrowing = NarrowingGenerator::generateMutableListNarrowing($mutableCollectionContent, [], $writableListContent);
if (!NarrowingGenerator::writeBetweenMarkers($mutableListFile, $mutableListNarrowing)) {
    echo "Failed to update MutableList.php\n";
    exit(1);
}

// Generate MutableTrackedList narrowing (MutableList -> MutableTrackedList&TrackedResult)
// Must run after MutableList narrowing since it reads the updated file
$trackedListFile = __DIR__ . '/../src/List/MutableTrackedList.php';
$mutableListContent = file_get_contents($mutableListFile);
if ($mutableListContent === false) {
    echo "Failed to read MutableList.php\n";
    exit(1);
}

$trackedListNarrowing = NarrowingGenerator::generateTrackedListNarrowing($mutableListContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($trackedListFile, $trackedListNarrowing)) {
    echo "Failed to update MutableTrackedList.php\n";
    exit(1);
}

echo "Done!\n";
