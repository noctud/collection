<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

/**
 * Single entry point for all code generation.
 *
 * Runs every narrowing pass (Collection/List/Set/Map interfaces) and then, as the last
 * step, regenerates the SelfPreserving*Logic trait bodies. The self-preserving pass reads
 * the narrowed immutable interfaces produced above, so it MUST run after them.
 *
 * Usage: php generate-all.php
 */

declare(strict_types=1);

require_once __DIR__ . '/NarrowingGenerator.php';

use Noctud\Collection\Bin\NarrowingGenerator;

$collectionFile = __DIR__ . '/../src/Collection.php';
$immutableCollectionFile = __DIR__ . '/../src/ImmutableCollection.php';
$writableCollectionFile = __DIR__ . '/../src/WritableCollection.php';
$mutableCollectionFile = __DIR__ . '/../src/MutableCollection.php';
$trackedCollectionFile = __DIR__ . '/../src/MutableTrackedCollection.php';

$collectionContent = file_get_contents($collectionFile);
if ($collectionContent === false) {
    echo "Failed to read Collection.php\n";
    exit(1);
}

// Generate ImmutableCollection narrowing (Collection -> ImmutableCollection)
$immutableCollectionNarrowing = NarrowingGenerator::generateImmutableCollectionNarrowing($collectionContent, ['onEach']);
if (!NarrowingGenerator::writeBetweenMarkers($immutableCollectionFile, $immutableCollectionNarrowing)) {
    echo "Failed to update ImmutableCollection.php\n";
    exit(1);
}

// Generate WritableCollection narrowing (Collection -> ImmutableCollection)
$writableCollectionNarrowing = NarrowingGenerator::generateWritableCollectionTransformationNarrowing($collectionContent, ['onEach']);
if (!NarrowingGenerator::writeBetweenMarkers($writableCollectionFile, $writableCollectionNarrowing)) {
    echo "Failed to update WritableCollection.php\n";
    exit(1);
}

// Generate MutableCollection narrowing (WritableCollection -> MutableCollection)
$writableCollectionContent = file_get_contents($writableCollectionFile);
if ($writableCollectionContent === false) {
    echo "Failed to read WritableCollection.php\n";
    exit(1);
}

// Generate WritableTrackedCollection narrowing (WritableCollection -> WritableTrackedCollection&TrackedResult)
$writableTrackedCollectionFile = __DIR__ . '/../src/WritableTrackedCollection.php';
$wtcNarrowing = NarrowingGenerator::generateWritableTrackedCollectionNarrowing($writableCollectionContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($writableTrackedCollectionFile, $wtcNarrowing)) {
    echo "Failed to update WritableTrackedCollection.php\n";
    exit(1);
}

$mutableCollectionNarrowing = NarrowingGenerator::generateMutableCollectionNarrowing($writableCollectionContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($mutableCollectionFile, $mutableCollectionNarrowing)) {
    echo "Failed to update MutableCollection.php\n";
    exit(1);
}

// Generate TrackedCollection narrowing (MutableCollection -> TrackedCollection&TrackedResult)
// Must run after MutableCollection narrowing since it reads the updated file
$mutableCollectionContent = file_get_contents($mutableCollectionFile);
if ($mutableCollectionContent === false) {
    echo "Failed to read MutableCollection.php\n";
    exit(1);
}

$trackedCollectionNarrowing = NarrowingGenerator::generateTrackedCollectionNarrowing($mutableCollectionContent, []);
if (!NarrowingGenerator::writeBetweenMarkers($trackedCollectionFile, $trackedCollectionNarrowing)) {
    echo "Failed to update MutableTrackedCollection.php\n";
    exit(1);
}

// Generate type-specific narrowing (includes tracked variants)
require_once __DIR__ . '/generate-set-narrowing.php';
require_once __DIR__ . '/generate-list-narrowing.php';
require_once __DIR__ . '/generate-map-narrowing.php';

// Self-preserving traits read the narrowed immutable interfaces generated above, so this
// must run last. The codegen CI job runs this entry point and fails on any drift.
require_once __DIR__ . '/generate-self-preserving.php';
