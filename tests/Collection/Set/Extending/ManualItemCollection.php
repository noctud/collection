<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set\Extending;

use Closure;
use InvalidArgumentException;
use Noctud\Collection\Set\HashSet\HashElementStore;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\ImmutableSetLogic;

/**
 * Extension style #2: the base ImmutableSetLogic with hand-written class-level
 * `@method self` overrides + a newCollectionOf override. Param types use the
 * concrete element type, so element/closure checking is fully preserved.
 *
 * The constructor enforces an element invariant: it proves that transforms
 * never go through the hand-written newCollectionOf() with transformed elements.
 *
 * @implements ImmutableSet<SwappableItem>
 * @phpstan-consistent-constructor
 * @method self filter(Closure(SwappableItem, int): bool $predicate)
 * @method self sorted()
 * @method array{self, self} partition(Closure(SwappableItem, int): bool $predicate)
 */
class ManualItemCollection implements ImmutableSet
{
	/** @use ImmutableSetLogic<SwappableItem> */
	use ImmutableSetLogic;

	/** @param iterable<SwappableItem> $data */
	public function __construct(iterable $data = [])
	{
		$items = is_array($data) ? $data : iterator_to_array($data, false);
		foreach ($items as $item) {
			if (!$item instanceof SwappableItem) {
				throw new InvalidArgumentException('ManualItemCollection must only hold SwappableItem instances');
			}
		}

		$this->store = new HashElementStore($items);
	}

	/** @param iterable<SwappableItem> $data */
	protected function newCollectionOf(iterable $data): ImmutableSet
	{
		return new self($data);
	}

	public function onlySwapped(): self
	{
		return $this->filter(static fn (SwappableItem $i): bool => $i->swapped);
	}

	public function sortedItems(): self
	{
		return $this->sorted();
	}

	/** @return array{self, self} */
	public function splitBySwapped(): array
	{
		return $this->partition(static fn (SwappableItem $i): bool => $i->swapped);
	}

	/** @return ImmutableSet<int> */
	public function toIds(): ImmutableSet
	{
		return $this->map(static fn (SwappableItem $i): int => $i->id);
	}
}
