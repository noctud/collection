<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\View;

use Noctud\Collection\Store\ReadOnlyElementStore;
use Noctud\Collection\Store\KeyValueStore;
use Traversable;

/**
 * @template E
 * @implements ReadOnlyElementStore<E>
 */
final class MapValueStore implements ReadOnlyElementStore
{
	public function __construct(
		/** @var KeyValueStore<string|int|bool|float|object,E> */
		private readonly KeyValueStore $store,
	) {}

	/**
	 * @return Traversable<E>
	 */
	public function getIterator(): Traversable
	{
		foreach ($this->store as $value) {
			yield $value;
		}
	}

	public function count(): int
	{
		return $this->store->count();
	}

	public function isEmpty(): bool
	{
		return $this->store->isEmpty();
	}

	public function first(bool $throw = false): mixed
	{
		return $this->store->first($throw)?->value;
	}

	public function last(bool $throw = false): mixed
	{
		return $this->store->last($throw)?->value;
	}

	public function random(bool $throw = false): mixed
	{
		return $this->store->random($throw)?->value;
	}

	public function contains(mixed $element): bool
	{
		return $this->store->contains($element);
	}

	public function toArray(): array
	{
		return $this->store->values();
	}
}
