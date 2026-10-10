<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Type\Collection;

use Noctud\Collection\List\ArrayList\ImmutableArrayList;
use Noctud\Collection\List\ArrayList\MutableArrayList;
use Noctud\Collection\Set\HashSet\ImmutableHashSet;
use Noctud\Collection\Set\HashSet\MutableHashSet;
use Noctud\Collection\Tests\Collection\List\Extending\LineItems;
use Noctud\Collection\Tests\Collection\List\Extending\ManualLineItems;
use Noctud\Collection\Tests\Collection\Set\Extending\ManualItemCollection;
use Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem;
use Noctud\Collection\Tests\Collection\Set\Extending\TraitItemCollection;
use function PHPStan\Testing\assertType;

// The other type tests reach the library through its interfaces (listOf(), setOf(), @var).
// A value typed as a concrete class resolves its methods from the logic traits instead, so
// any trait docblock that diverges from the interface shows up only here: an inherited
// @return that does not fit a narrower native type is dropped along with its generics (#49),
// and a @param override can unbind a method template the inherited @return still uses.

$immutableList = new ImmutableArrayList([1, 2, 3]);
$mutableList = new MutableArrayList([1, 2, 3]);
$immutableSet = new ImmutableHashSet([1, 2, 3]);
$mutableSet = new MutableHashSet([1, 2, 3]);
$lineItems = new LineItems([new SwappableItem(1)]);
$items = new TraitItemCollection([new SwappableItem(1)]);
$manualLineItems = new ManualLineItems([new SwappableItem(1)]);
$manualItems = new ManualItemCollection([new SwappableItem(1)]);

// chunked() and windowed() keep the element type of the chunks.
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<int>>', $immutableList->chunked(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<int>>', $mutableList->chunked(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<int>>', $immutableSet->chunked(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<int>>', $mutableSet->chunked(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem>>', $lineItems->chunked(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem>>', $items->chunked(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem>>', $manualLineItems->chunked(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem>>', $manualItems->chunked(2));

assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<int>>', $immutableList->windowed(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<int>>', $mutableList->windowed(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<int>>', $immutableSet->windowed(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<int>>', $mutableSet->windowed(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem>>', $lineItems->windowed(2));
assertType('Noctud\Collection\List\ImmutableList<Noctud\Collection\List\ImmutableList<Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem>>', $items->windowed(2));

// ...so the elements of a chunk stay usable.
assertType('int', $immutableSet->chunked(2)->first()->first());

// zip() and zipWithNext() keep the pair shape.
assertType('Noctud\Collection\List\ImmutableList<array{int, string}>', $immutableList->zip(['a', 'b']));
assertType('Noctud\Collection\List\ImmutableList<array{int, string}>', $mutableList->zip(['a', 'b']));
assertType('Noctud\Collection\List\ImmutableList<array{int, string}>', $immutableSet->zip(['a', 'b']));
assertType('Noctud\Collection\List\ImmutableList<array{int, string}>', $mutableSet->zip(['a', 'b']));
assertType('Noctud\Collection\List\ImmutableList<array{Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem, string}>', $lineItems->zip(['a', 'b']));
assertType('Noctud\Collection\List\ImmutableList<array{Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem, string}>', $items->zip(['a', 'b']));

assertType('Noctud\Collection\List\ImmutableList<array{int, int}>', $immutableList->zipWithNext());
assertType('Noctud\Collection\List\ImmutableList<array{int, int}>', $mutableList->zipWithNext());
assertType('Noctud\Collection\List\ImmutableList<array{int, int}>', $immutableSet->zipWithNext());
assertType('Noctud\Collection\List\ImmutableList<array{int, int}>', $mutableSet->zipWithNext());
assertType('Noctud\Collection\List\ImmutableList<array{Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem, Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem}>', $lineItems->zipWithNext());
assertType('Noctud\Collection\List\ImmutableList<array{Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem, Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem}>', $items->zipWithNext());

// Immutable additions keep the element type, without leaking the interface's NE template...
assertType('Noctud\Collection\List\ImmutableList<int>', $immutableList->add(4));
assertType('Noctud\Collection\List\ImmutableList<int>', $immutableList->addFirst(0));
assertType('Noctud\Collection\List\ImmutableList<int>', $immutableList->addAll([4, 5]));
assertType('Noctud\Collection\Set\ImmutableSet<int>', $immutableSet->add(4));
assertType('Noctud\Collection\Set\ImmutableSet<int>', $immutableSet->addFirst(0));
assertType('Noctud\Collection\Set\ImmutableSet<int>', $immutableSet->addAll([4, 5]));
assertType('int', $immutableList->add(4)->first());

// ...and widen it like the interfaces do.
assertType('Noctud\Collection\List\ImmutableList<int|string>', $immutableList->add('a'));
assertType('Noctud\Collection\Set\ImmutableSet<int|string>', $immutableSet->add('a'));

// A class built on the base trait that rebuilds itself declares add() with @method, which
// keeps a foreign element out and the result typed as the class.
assertType('Noctud\Collection\Tests\Collection\List\Extending\ManualLineItems', $manualLineItems->add(new SwappableItem(2)));
assertType('Noctud\Collection\Tests\Collection\Set\Extending\ManualItemCollection', $manualItems->add(new SwappableItem(2)));

// groupBy() without a value transform keeps the collection kind of the source...
assertType('Noctud\Collection\Map\ImmutableMap<bool, Noctud\Collection\List\ImmutableList<int>>', $immutableList->groupBy(fn (int $x): bool => $x > 1));
assertType('Noctud\Collection\Map\ImmutableMap<bool, Noctud\Collection\Set\ImmutableSet<int>>', $immutableSet->groupBy(fn (int $x): bool => $x > 1));

// ...and with one, every group is a list of the transformed values, as the interfaces declare.
assertType('Noctud\Collection\Map\ImmutableMap<bool, Noctud\Collection\List\ImmutableList<bool>>', $immutableList->groupBy(fn (int $x): bool => $x > 1, fn (int $x): bool => $x % 2 === 0));
assertType('Noctud\Collection\Map\ImmutableMap<bool, Noctud\Collection\List\ImmutableList<bool>>', $mutableList->groupBy(fn (int $x): bool => $x > 1, fn (int $x): bool => $x % 2 === 0));
assertType('Noctud\Collection\Map\ImmutableMap<bool, Noctud\Collection\List\ImmutableList<bool>>', $immutableSet->groupBy(fn (int $x): bool => $x > 1, fn (int $x): bool => $x % 2 === 0));
assertType('Noctud\Collection\Map\ImmutableMap<bool, Noctud\Collection\List\ImmutableList<bool>>', $mutableSet->groupBy(fn (int $x): bool => $x > 1, fn (int $x): bool => $x % 2 === 0));
assertType('Noctud\Collection\Map\ImmutableMap<bool, Noctud\Collection\List\ImmutableList<int>>', $manualItems->groupBy(fn (SwappableItem $i): bool => $i->swapped, fn (SwappableItem $i): int => $i->id));
