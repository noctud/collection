<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List\Extending;

use InvalidArgumentException;
use Noctud\Collection\List\ArrayList\ArrayIndexStore;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\List\SelfPreservingImmutableListLogic;
use Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem;

/**
 * Extension style #1 (List): the SelfPreservingImmutableListLogic trait.
 *
 * The constructor enforces an element invariant: it proves that transforms
 * (map, flatMap, ...) never rebuild the subtype from transformed elements.
 *
 * @implements ImmutableList<SwappableItem>
 * @phpstan-consistent-constructor
 */
class LineItems implements ImmutableList
{
	/** @use SelfPreservingImmutableListLogic<SwappableItem> */
	use SelfPreservingImmutableListLogic;

	/** @param iterable<SwappableItem> $data */
	public function __construct(iterable $data = [])
	{
		$items = is_array($data) ? $data : iterator_to_array($data, false);
		foreach ($items as $item) {
			if (!$item instanceof SwappableItem) {
				throw new InvalidArgumentException('LineItems only holds SwappableItem instances');
			}
		}

		$this->store = new ArrayIndexStore($items);
	}

	public function onlySwapped(): self
	{
		return $this->filter(static fn (SwappableItem $i): bool => $i->swapped);
	}

	public function sortedById(): self
	{
		return $this->sortedBy(static fn (SwappableItem $i): int => $i->id);
	}

	public function append(SwappableItem $item): self
	{
		return $this->add($item);
	}

	public function firstTwo(): self
	{
		return $this->takeFirst(2);
	}

	public function middle(): self
	{
		return $this->slice(1, 3);
	}

	public function replaceAt(int $index, SwappableItem $item): self
	{
		return $this->set($index, $item);
	}

	/** @return array{self, self} */
	public function splitBySwapped(): array
	{
		return $this->partition(static fn (SwappableItem $i): bool => $i->swapped);
	}

	// Transform methods change the element type, so they return the base
	// ImmutableList<R> (not self). These assert R is inferred from the closure
	// rather than collapsed to mixed — the issue #3 follow-up about map().

	/** @return ImmutableList<int> */
	public function toIds(): ImmutableList
	{
		return $this->map(static fn (SwappableItem $i): int => $i->id);
	}

	/** @return ImmutableList<int> */
	public function swappedIds(): ImmutableList
	{
		return $this->mapNotNull(static fn (SwappableItem $i): ?int => $i->swapped ? $i->id : null);
	}

	/** @return ImmutableList<int> */
	public function idsWithNegatives(): ImmutableList
	{
		return $this->flatMap(static fn (SwappableItem $i): array => [$i->id, -$i->id]);
	}
}
