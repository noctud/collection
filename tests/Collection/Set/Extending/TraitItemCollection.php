<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set\Extending;

use InvalidArgumentException;
use Noctud\Collection\Set\HashSet\HashElementStore;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\SelfPreservingImmutableSetLogic;

/**
 * Extension style #1: the SelfPreservingImmutableSetLogic trait.
 *
 * The shape-preserving API returns this exact type with no per-method annotation
 * and no newCollectionOf override — only a constructor that accepts an iterable.
 * The constructor enforces an element invariant: it proves that transforms
 * (map, flatMap, ...) never rebuild the subtype from transformed elements.
 *
 * @implements ImmutableSet<SwappableItem>
 * @phpstan-consistent-constructor
 */
class TraitItemCollection implements ImmutableSet
{
	/** @use SelfPreservingImmutableSetLogic<SwappableItem> */
	use SelfPreservingImmutableSetLogic;

	/** @param iterable<SwappableItem> $data */
	public function __construct(iterable $data = [])
	{
		$items = is_array($data) ? $data : iterator_to_array($data, false);
		foreach ($items as $item) {
			if (!$item instanceof SwappableItem) {
				throw new InvalidArgumentException('TraitItemCollection only holds SwappableItem instances');
			}
		}

		$this->store = new HashElementStore($items);
	}

	// Each method below is a compile-time assertion that the narrowed return type
	// is this class (tests/ is analysed by PHPStan), plus a runtime target.

	public function onlySwapped(): self
	{
		return $this->filter(static fn (SwappableItem $i): bool => $i->swapped);
	}

	public function sortedById(): self
	{
		return $this->sortedBy(static fn (SwappableItem $i): int => $i->id);
	}

	public function with(SwappableItem $item): self
	{
		return $this->add($item);
	}

	public function without(SwappableItem $item): self
	{
		return $this->removeElement($item);
	}

	public function common(self $other): self
	{
		return $this->intersect($other);
	}

	/** @return array{self, self} */
	public function splitBySwapped(): array
	{
		return $this->partition(static fn (SwappableItem $i): bool => $i->swapped);
	}

	// Transform methods change the element type, so they return the base
	// ImmutableSet<R> (not self). These assert R is inferred from the closure
	// rather than collapsed to mixed — the issue #3 follow-up about map().

	/** @return ImmutableSet<int> */
	public function toIds(): ImmutableSet
	{
		return $this->map(static fn (SwappableItem $i): int => $i->id);
	}

	/** @return ImmutableSet<int> */
	public function swappedIds(): ImmutableSet
	{
		return $this->mapNotNull(static fn (SwappableItem $i): ?int => $i->swapped ? $i->id : null);
	}

	/** @return ImmutableSet<int> */
	public function idsWithNegatives(): ImmutableSet
	{
		return $this->flatMap(static fn (SwappableItem $i): array => [$i->id, -$i->id]);
	}
}
