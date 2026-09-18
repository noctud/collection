<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List\Extending;

use Closure;
use InvalidArgumentException;
use Noctud\Collection\List\ArrayList\ArrayIndexStore;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\List\ImmutableListLogic;
use Noctud\Collection\Tests\Collection\Set\Extending\SwappableItem;
use ReturnTypeWillChange;

/**
 * Extension style #2 (List): base ImmutableListLogic + class-level `@method self`
 * overrides + a newCollectionOf override.
 *
 * The constructor enforces an element invariant: it proves that transforms
 * never go through the hand-written newCollectionOf() with transformed elements.
 *
 * @implements ImmutableList<SwappableItem>
 * @phpstan-consistent-constructor
 * @method self filter(Closure(SwappableItem, int): bool $predicate)
 * @method self sorted()
 */
class ManualLineItems implements ImmutableList
{
	/** @use ImmutableListLogic<SwappableItem> */
	use ImmutableListLogic;

	/** @param iterable<SwappableItem> $data */
	public function __construct(iterable $data = [])
	{
		$items = is_array($data) ? $data : iterator_to_array($data, false);
		foreach ($items as $item) {
			if (!$item instanceof SwappableItem) {
				throw new InvalidArgumentException('ManualLineItems must only hold SwappableItem instances');
			}
		}

		$this->store = new ArrayIndexStore($items);
	}

	/**
	 * Deliberately omits a native return type to exercise legacy ArrayAccess compatibility.
	 *
	 * @param int $offset
	 * @return SwappableItem
	 */
	#[ReturnTypeWillChange]
	public function offsetGet(mixed $offset)
	{
		return $this->get($offset);
	}

	/** @param iterable<SwappableItem> $data */
	protected function newCollectionOf(iterable $data): ImmutableList
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

	/** @return ImmutableList<int> */
	public function toIds(): ImmutableList
	{
		return $this->map(static fn (SwappableItem $i): int => $i->id);
	}
}
