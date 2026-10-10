<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set;

use Closure;
use Noctud\Collection\Store\ReadWriteElementStore;
use Noctud\Collection\StrictElementLookup;
use Noctud\Collection\TrackedResult;

/**
 * Tracking trait for mutable sets.
 * Overrides mutation methods to compute $changed before delegating to the store.
 *
 * @template E
 * @mixin MutableTrackedSet<E>
 */
trait MutableTrackedSetLogic
{
	/** @var ReadWriteElementStore<E> */
	protected ReadWriteElementStore $store;

	/** @use SetLogic<E> */
	use SetLogic;

	// --- Tracking ---

	private bool $_changed = false; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

	public bool $changed {
		get => $this->_changed; // phpcs:ignore PSR2.Classes.PropertyDeclaration.ScopeMissing
	}

	// --- Mutation: Add ---

	/**
	 * {@inheritDoc}
	 * @param E $element
	 * @return static
	 */
	public function add(mixed $element): MutableTrackedSet&TrackedResult
	{
		// For Set: changes only if item doesn't exist
		$this->_changed = !$this->store->contains($element);
		$this->store->add($element);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * For sets, addFirst changes if element is new or moved from a non-first position.
	 * @param E $element
	 * @return static
	 */
	public function addFirst(mixed $element): MutableTrackedSet&TrackedResult
	{
		$prevCount = $this->store->count();
		$prevFirst = $this->store->first();
		$this->store->addFirst($element);
		$this->_changed = $this->store->count() !== $prevCount || $this->store->first() !== $prevFirst;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @param iterable<E> $elements
	 * @return static
	 */
	public function addAll(iterable $elements): MutableTrackedSet&TrackedResult
	{
		$this->_changed = false;
		foreach ($elements as $element) {
			if (!$this->store->contains($element)) {
				$this->_changed = true;
			}
			$this->store->add($element);
		}
		return $this;
	}

	// --- Mutation: Remove ---

	/**
	 * {@inheritDoc}
	 * @param E $element
	 * @return static
	 */
	public function removeElement(mixed $element): MutableTrackedSet&TrackedResult
	{
		$this->_changed = $this->store->contains($element);
		$this->store->removeFirstOccurrence($element);
		return $this;
	}

	/** {@inheritDoc} */
	public function removeFirst(): MutableTrackedSet&TrackedResult
	{
		$this->_changed = !$this->store->isEmpty();
		$this->store->removeFirst();
		return $this;
	}

	/** {@inheritDoc} */
	public function removeLast(): MutableTrackedSet&TrackedResult
	{
		$this->_changed = !$this->store->isEmpty();
		$this->store->removeLast();
		return $this;
	}

	/** {@inheritDoc} */
	public function removeIf(Closure $predicate): MutableTrackedSet&TrackedResult
	{
		$prevCount = $this->store->count();
		$i = 0;
		$this->store->removeIf(function ($v) use ($predicate, &$i) {
			return $predicate($v, $i++);
		});
		$this->_changed = $this->store->count() !== $prevCount;
		return $this;
	}

	/** {@inheritDoc} */
	public function removeAll(iterable $elements): MutableTrackedSet&TrackedResult
	{
		$prevCount = $this->store->count();
		$this->store->removeIf(new StrictElementLookup($elements, $this->store->count())->predicate());
		$this->_changed = $this->store->count() !== $prevCount;
		return $this;
	}

	/** {@inheritDoc} */
	public function retainAll(iterable $elements): MutableTrackedSet&TrackedResult
	{
		$prevCount = $this->store->count();
		$this->store->removeIf(new StrictElementLookup($elements, $this->store->count())->predicate(negate: true));
		$this->_changed = $this->store->count() !== $prevCount;
		return $this;
	}

	/** {@inheritDoc} */
	public function clear(): MutableTrackedSet&TrackedResult
	{
		$this->_changed = !$this->store->isEmpty();
		$this->store->clear();
		return $this;
	}

	// --- Mutation: Order ---

	/** {@inheritDoc} */
	public function sort(): MutableTrackedSet&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->sort();
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortDesc(): MutableTrackedSet&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->sort(static fn ($a, $b) => $b <=> $a);
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortBy(Closure $selector): MutableTrackedSet&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->sortBy($selector);
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortByDesc(Closure $selector): MutableTrackedSet&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->sortBy($selector, descending: true);
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortWith(Closure $comparator): MutableTrackedSet&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->sort($comparator);
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function reverse(): MutableTrackedSet&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->reverse();
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function shuffle(): MutableTrackedSet&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->shuffle();
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}
}
