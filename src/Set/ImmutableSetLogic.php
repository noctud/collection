<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set;

use Closure;
use Noctud\Collection\Operation\FilterOperation;
use Noctud\Collection\Store\ReadWriteElementStore;
use Noctud\Collection\StrictElementLookup;

/**
 * @template E
 * @implements ImmutableSet<E>
 */
trait ImmutableSetLogic
{
	/** @var ReadWriteElementStore<E> */
	protected ReadWriteElementStore $store;

	/** @use SetLogic<E> */
	use SetLogic;

	// --- Mutation (returns new) ---

	/**
	 * {@inheritDoc}
	 * @param E $element
	 */
	public function add(mixed $element): ImmutableSet
	{
		$store = clone $this->store;
		$store->add($element);
		return $this->newCollectionOf($store); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 * @param E $element
	 */
	public function addFirst(mixed $element): ImmutableSet
	{
		$store = clone $this->store;
		$store->addFirst($element);
		return $this->newCollectionOf($store); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 * @param iterable<E> $elements
	 */
	public function addAll(iterable $elements): ImmutableSet
	{
		$store = clone $this->store;
		$store->addAll($elements);
		return $this->newCollectionOf($store); // @phpstan-ignore return.type
	}

	/** {@inheritDoc} */
	public function removeIf(Closure $predicate): ImmutableSet
	{
		$i = 0;
		$filtered = new FilterOperation($this->store->toArray())->byValue(function ($v) use ($predicate, &$i) {
			return !$predicate($v, $i++);
		});
		return $this->newCollectionOf($filtered);
	}

	/** {@inheritDoc} */
	public function removeAll(iterable $elements): ImmutableSet
	{
		$lookup = new StrictElementLookup($elements, $this->store->count());
		return $this->newCollectionOf(new FilterOperation($this->store->toArray())->byValue($lookup->predicate(negate: true)));
	}

	/** {@inheritDoc} */
	public function removeElement(mixed $element): ImmutableSet
	{
		$store = clone $this->store;
		$store->removeFirstOccurrence($element);
		return $this->newCollectionOf($store);
	}

	/** {@inheritDoc} */
	public function removeFirst(): ImmutableSet
	{
		$store = clone $this->store;
		$store->removeFirst();
		return $this->newCollectionOf($store);
	}

	/** {@inheritDoc} */
	public function removeLast(): ImmutableSet
	{
		$store = clone $this->store;
		$store->removeLast();
		return $this->newCollectionOf($store);
	}

	/** {@inheritDoc} */
	public function retainAll(iterable $elements): ImmutableSet
	{
		$lookup = new StrictElementLookup($elements, $this->store->count());
		return $this->newCollectionOf(new FilterOperation($this->store->toArray())->byValue($lookup->predicate()));
	}
}
