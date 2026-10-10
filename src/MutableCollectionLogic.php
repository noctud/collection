<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

use Closure;

/**
 * Mutation methods for mutable collections.
 *
 * This trait provides mutation methods (add, remove, sort, etc.) for mutable collections.
 * It must be used alongside a type-specific Logic trait (SetLogic, ListLogic) which provides
 * the read operations.
 *
 * @template E
 * @mixin MutableCollection<E>
 * @implements MutableCollection<E>
 */
trait MutableCollectionLogic
{
	// --- Mutation: Add ---

	/**
	 * {@inheritDoc}
	 * @param E $element
	 */
	public function add(mixed $element): static
	{
		$this->store->add($element);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @param E $element
	 */
	public function addFirst(mixed $element): static
	{
		$this->store->addFirst($element);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @param iterable<E> $elements
	 */
	public function addAll(iterable $elements): static
	{
		$this->store->addAll($elements);
		return $this;
	}

	// --- Mutation: Remove ---

	/** {@inheritDoc} */
	public function removeIf(Closure $predicate): static
	{
		$i = 0;
		$this->store->removeIf(function ($v) use ($predicate, &$i) {
			return $predicate($v, $i++);
		});
		return $this;
	}

	/** {@inheritDoc} */
	public function removeAll(iterable $elements): static
	{
		$this->store->removeIf(new StrictElementLookup($elements, $this->store->count())->predicate());
		return $this;
	}

	/** {@inheritDoc} */
	public function removeElement(mixed $element): static
	{
		$this->store->removeFirstOccurrence($element);
		return $this;
	}

	/** {@inheritDoc} */
	public function removeFirst(): static
	{
		$this->store->removeFirst();
		return $this;
	}

	/** {@inheritDoc} */
	public function removeLast(): static
	{
		$this->store->removeLast();
		return $this;
	}

	/** {@inheritDoc} */
	public function retainAll(iterable $elements): static
	{
		$this->store->removeIf(new StrictElementLookup($elements, $this->store->count())->predicate(negate: true));
		return $this;
	}

	/** {@inheritDoc} */
	public function clear(): static
	{
		$this->store->clear();
		return $this;
	}

	// --- Mutation: Order ---

	/** {@inheritDoc} */
	public function sort(): static
	{
		$this->store->sort();
		return $this;
	}

	/** {@inheritDoc} */
	public function sortDesc(): static
	{
		$this->store->sort(static fn ($a, $b) => $b <=> $a);
		return $this;
	}

	/** {@inheritDoc} */
	public function sortBy(Closure $selector): static
	{
		$this->store->sortBy($selector);
		return $this;
	}

	/** {@inheritDoc} */
	public function sortByDesc(Closure $selector): static
	{
		$this->store->sortBy($selector, descending: true);
		return $this;
	}

	/** {@inheritDoc} */
	public function sortWith(Closure $comparator): static
	{
		$this->store->sort($comparator);
		return $this;
	}

	/** {@inheritDoc} */
	public function reverse(): static
	{
		$this->store->reverse();
		return $this;
	}

	/** {@inheritDoc} */
	public function shuffle(): static
	{
		$this->store->shuffle();
		return $this;
	}
}
