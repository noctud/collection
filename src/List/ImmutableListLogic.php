<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List;

use Closure;
use Noctud\Collection\Operation\FilterOperation;
use Noctud\Collection\Store\ReadWriteIndexedStore;
use Traversable;

/**
 * @template E
 * @implements ImmutableList<E>
 */
trait ImmutableListLogic
{
	/** @var ReadWriteIndexedStore<E> */
	protected ReadWriteIndexedStore $store;

	/** @use ListLogic<E> */
	use ListLogic;

	// --- Mutation (returns new) ---

	/** {@inheritDoc} */
	public function add(mixed $element): ImmutableList
	{
		$store = clone $this->store;
		$store->add($element);
		return $this->newCollectionOf($store); // @phpstan-ignore return.type
	}

	/** {@inheritDoc} */
	public function addFirst(mixed $element): ImmutableList
	{
		$store = clone $this->store;
		$store->addFirst($element);
		return $this->newCollectionOf($store); // @phpstan-ignore return.type
	}

	/** {@inheritDoc} */
	public function addAll(iterable $elements): ImmutableList
	{
		$store = clone $this->store;
		$store->addAll($elements);
		return $this->newCollectionOf($store); // @phpstan-ignore return.type
	}

	/** {@inheritDoc} */
	public function removeIf(Closure $predicate): ImmutableList
	{
		$i = 0;
		$filtered = new FilterOperation($this->store->toArray())->byValue(function ($v) use ($predicate, &$i) {
			return !$predicate($v, $i++);
		});
		return $this->newCollectionOf($filtered);
	}

	/** {@inheritDoc} */
	public function removeAll(iterable $elements): ImmutableList
	{
		$itemsArray = $elements instanceof Traversable ? iterator_to_array($elements, false) : array_values($elements);
		return $this->newCollectionOf(new FilterOperation($this->store->toArray())->byValue(fn ($v) => !in_array($v, $itemsArray, true)));
	}

	/** {@inheritDoc} */
	public function removeElement(mixed $element): ImmutableList
	{
		$store = clone $this->store;
		$store->removeFirstOccurrence($element);
		return $this->newCollectionOf($store);
	}

	/** {@inheritDoc} */
	public function removeFirst(): ImmutableList
	{
		$store = clone $this->store;
		$store->removeFirst();
		return $this->newCollectionOf($store);
	}

	/** {@inheritDoc} */
	public function removeLast(): ImmutableList
	{
		$store = clone $this->store;
		$store->removeLast();
		return $this->newCollectionOf($store);
	}

	/** {@inheritDoc} */
	public function retainAll(iterable $elements): ImmutableList
	{
		$itemsArray = $elements instanceof Traversable ? iterator_to_array($elements, false) : array_values($elements);
		return $this->newCollectionOf(new FilterOperation($this->store->toArray())->byValue(fn ($v) => in_array($v, $itemsArray, true)));
	}

	// --- Mutation: List (returns new) ---

	/** {@inheritDoc} */
	public function set(int $index, mixed $element): ImmutableList
	{
		$store = clone $this->store;
		$store->set($index, $element); // @phpstan-ignore argument.type
		return $this->newCollectionOf($store); // @phpstan-ignore return.type
	}

	/** {@inheritDoc} */
	public function removeAt(int $index): ImmutableList
	{
		$store = clone $this->store;
		$store->removeAt($index);
		return $this->newCollectionOf($store);
	}

	/** {@inheritDoc} */
	public function removeEvery(mixed $element): ImmutableList
	{
		$store = clone $this->store;
		$store->removeEvery($element);
		return $this->newCollectionOf($store);
	}
}
