<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List;

use Closure;
use Noctud\Collection\Store\ReadWriteIndexedStore;
use Noctud\Collection\StrictElementLookup;
use Noctud\Collection\TrackedResult;

/**
 * Tracking trait for mutable lists.
 * Overrides mutation methods to compute $changed before delegating to the store.
 *
 * @template E
 * @mixin MutableTrackedList<E>
 */
trait MutableTrackedListLogic
{
	/** @var ReadWriteIndexedStore<E> */
	protected ReadWriteIndexedStore $store;

	/** @use ListLogic<E> */
	use ListLogic;

	// --- Tracking ---

	private bool $_changed = false; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

	public bool $changed {
		get => $this->_changed; // phpcs:ignore PSR2.Classes.PropertyDeclaration.ScopeMissing
	}

	// --- Mutation: Add ---

	/**
	 * {@inheritDoc}
	 * For lists, add always changes (appends to end).
	 * @param E $element
	 * @return static
	 */
	public function add(mixed $element): MutableTrackedList&TrackedResult
	{
		$this->_changed = true;
		$this->store->add($element);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * For lists, addFirst always changes (prepends to beginning).
	 * @param E $element
	 * @return static
	 */
	public function addFirst(mixed $element): MutableTrackedList&TrackedResult
	{
		$this->_changed = true;
		$this->store->addFirst($element);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * For lists, addAll changes if at least one element is added.
	 * @param iterable<E> $elements
	 * @return static
	 */
	public function addAll(iterable $elements): MutableTrackedList&TrackedResult
	{
		$this->_changed = false;
		foreach ($elements as $element) {
			$this->store->add($element);
			$this->_changed = true;
		}
		return $this;
	}

	// --- Mutation: List ---

	/** {@inheritDoc} */
	public function set(int $index, mixed $element): MutableTrackedList&TrackedResult
	{
		$currentValue = $this->store->get($index, true);
		$this->_changed = $currentValue !== $element;
		$this->store->set($index, $element);
		return $this;
	}

	/** {@inheritDoc} */
	public function removeAt(int $index): MutableTrackedList&TrackedResult
	{
		// removeAt throws if index is invalid, so if we get here it always changes
		$this->store->removeAt($index);
		$this->_changed = true;
		return $this;
	}

	// --- Mutation: Remove ---

	/** {@inheritDoc} */
	public function removeElement(mixed $element): MutableTrackedList&TrackedResult
	{
		$this->_changed = $this->store->contains($element);
		$this->store->removeFirstOccurrence($element);
		return $this;
	}

	/** {@inheritDoc} */
	public function removeFirst(): MutableTrackedList&TrackedResult
	{
		$this->_changed = !$this->store->isEmpty();
		$this->store->removeFirst();
		return $this;
	}

	/** {@inheritDoc} */
	public function removeLast(): MutableTrackedList&TrackedResult
	{
		$this->_changed = !$this->store->isEmpty();
		$this->store->removeLast();
		return $this;
	}

	/** {@inheritDoc} */
	public function removeEvery(mixed $element): MutableTrackedList&TrackedResult
	{
		$this->_changed = $this->store->contains($element);
		$this->store->removeEvery($element);
		return $this;
	}

	/** {@inheritDoc} */
	public function removeIf(Closure $predicate): MutableTrackedList&TrackedResult
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
	public function removeAll(iterable $elements): MutableTrackedList&TrackedResult
	{
		$prevCount = $this->store->count();
		$this->store->removeIf(new StrictElementLookup($elements, $this->store->count())->predicate());
		$this->_changed = $this->store->count() !== $prevCount;
		return $this;
	}

	/** {@inheritDoc} */
	public function retainAll(iterable $elements): MutableTrackedList&TrackedResult
	{
		$prevCount = $this->store->count();
		$this->store->removeIf(new StrictElementLookup($elements, $this->store->count())->predicate(negate: true));
		$this->_changed = $this->store->count() !== $prevCount;
		return $this;
	}

	/** {@inheritDoc} */
	public function clear(): MutableTrackedList&TrackedResult
	{
		$this->_changed = !$this->store->isEmpty();
		$this->store->clear();
		return $this;
	}

	// --- Mutation: Order ---

	/** {@inheritDoc} */
	public function sort(): MutableTrackedList&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->sort();
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortDesc(): MutableTrackedList&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->sort(static fn ($a, $b) => $b <=> $a);
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortBy(Closure $selector): MutableTrackedList&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->sortBy($selector);
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortByDesc(Closure $selector): MutableTrackedList&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->sortBy($selector, descending: true);
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortWith(Closure $comparator): MutableTrackedList&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->sort($comparator);
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function reverse(): MutableTrackedList&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->reverse();
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	/** {@inheritDoc} */
	public function shuffle(): MutableTrackedList&TrackedResult
	{
		$prevItems = $this->store->toArray();
		$this->store->shuffle();
		$this->_changed = $this->store->toArray() !== $prevItems;
		return $this;
	}

	// --- ArrayAccess ---

	/**
	 * @param int|null $offset
	 * @param E $value
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		if ($offset === null) {
			$this->add($value);
		} else {
			$this->set($offset, $value);
		}
	}

	public function offsetUnset(mixed $offset): void
	{
		$this->removeAt($offset);
	}
}
