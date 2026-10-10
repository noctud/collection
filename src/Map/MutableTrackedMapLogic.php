<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

use Closure;
use Noctud\Collection\TrackedResult;
use NoDiscard;

/**
 * Tracking trait for mutable maps.
 * Overrides mutation methods to compute $changed before delegating to the store.
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @mixin MutableTrackedMap<K,V>
 */
trait MutableTrackedMapLogic
{
	/** @use MapLogic<K,V> */
	use MapLogic;

	// --- Tracking ---

	private bool $_changed = false; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

	public bool $changed {
		get => $this->_changed; // phpcs:ignore PSR2.Classes.PropertyDeclaration.ScopeMissing
	}

	// --- Element Access ---

	/** {@inheritDoc} */
	#[NoDiscard]
	public function getOrPut(string|int|bool|float|object $key, Closure $compute): mixed
	{
		if ($this->store->containsKey($key)) { // @phpstan-ignore argument.type
			$this->_changed = false;
			return $this->store->get($key, true); // @phpstan-ignore argument.type
		}

		$value = $compute();
		$this->store->put($key, $value); // @phpstan-ignore argument.type
		$this->_changed = true;

		return $value;
	}

	// --- Mutation: Add ---

	/** {@inheritDoc} */
	public function putIfAbsent(string|int|bool|float|object $key, mixed $value): MutableTrackedMap&TrackedResult
	{
		$this->_changed = !$this->store->containsKey($key); // @phpstan-ignore argument.type
		if ($this->_changed) {
			$this->store->put($key, $value); // @phpstan-ignore argument.type
		}

		return $this;
	}

	/** {@inheritDoc} */
	public function put(string|int|bool|float|object $key, mixed $value): MutableTrackedMap&TrackedResult
	{
		// Check if key exists and if value differs
		if ($this->store->containsKey($key)) { // @phpstan-ignore argument.type
			$currentValue = $this->store->get($key, false); // @phpstan-ignore argument.type
			$this->_changed = $currentValue !== $value;
		} else {
			$this->_changed = true;
		}
		$this->store->put($key, $value); // @phpstan-ignore argument.type
		return $this;
	}

	/** {@inheritDoc} */
	public function putAll(iterable $data): MutableTrackedMap&TrackedResult
	{
		$this->_changed = false;
		foreach ($data as $key => $value) {
			if ($this->store->containsKey($key)) {
				$currentValue = $this->store->get($key, false);
				if ($currentValue !== $value) {
					$this->_changed = true;
				}
			} else {
				$this->_changed = true;
			}
			$this->store->put($key, $value);
		}
		return $this;
	}

	/** {@inheritDoc} */
	public function putAllPairs(iterable $data): MutableTrackedMap&TrackedResult
	{
		$this->_changed = false;
		foreach ($data as [$key, $value]) {
			if ($this->store->containsKey($key)) {
				$currentValue = $this->store->get($key, false);
				if ($currentValue !== $value) {
					$this->_changed = true;
				}
			} else {
				$this->_changed = true;
			}
			$this->store->put($key, $value);
		}
		return $this;
	}

	/** {@inheritDoc} */
	public function putFirst(string|int|bool|float|object $key, mixed $value): MutableTrackedMap&TrackedResult
	{
		$first = $this->store->first();
		if ($first !== null && $first->key === $key && $first->value === $value) {
			$this->_changed = false;
		} else {
			$this->_changed = true;
		}
		$this->store->putFirst($key, $value); // @phpstan-ignore argument.type
		return $this;
	}

	// --- Mutation: Remove ---

	/** {@inheritDoc} */
	public function remove(string|int|bool|float|object $key): MutableTrackedMap&TrackedResult
	{
		$this->_changed = $this->store->containsKey($key); // @phpstan-ignore argument.type
		$this->store->remove($key); // @phpstan-ignore argument.type
		return $this;
	}

	/** {@inheritDoc} */
	public function removeFirst(): MutableTrackedMap&TrackedResult
	{
		$this->_changed = !$this->store->isEmpty();
		$this->store->removeFirst();
		return $this;
	}

	/** {@inheritDoc} */
	public function removeLast(): MutableTrackedMap&TrackedResult
	{
		$this->_changed = !$this->store->isEmpty();
		$this->store->removeLast();
		return $this;
	}

	/** {@inheritDoc} */
	public function removeIf(Closure $predicate): MutableTrackedMap&TrackedResult
	{
		$prevCount = $this->store->count();
		$this->store->removeIf($predicate);
		$this->_changed = $this->store->count() !== $prevCount;
		return $this;
	}

	/** {@inheritDoc} */
	public function removeIfKey(Closure $predicate): MutableTrackedMap&TrackedResult
	{
		$prevCount = $this->store->count();
		$this->store->removeIfKey($predicate);
		$this->_changed = $this->store->count() !== $prevCount;
		return $this;
	}

	/** {@inheritDoc} */
	public function removeIfValue(Closure $predicate): MutableTrackedMap&TrackedResult
	{
		$prevCount = $this->store->count();
		$this->store->removeIfValue($predicate);
		$this->_changed = $this->store->count() !== $prevCount;
		return $this;
	}

	/** {@inheritDoc} */
	public function removeNullValues(): MutableTrackedMap&TrackedResult
	{
		$prevCount = $this->store->count();
		$this->store->removeIfValue(fn ($v) => $v === null);
		$this->_changed = $this->store->count() !== $prevCount;
		return $this;
	}

	/** {@inheritDoc} */
	public function clear(): MutableTrackedMap&TrackedResult
	{
		$this->_changed = !$this->store->isEmpty();
		$this->store->clear();
		return $this;
	}

	// --- Mutation: Order ---

	/** {@inheritDoc} */
	public function sortByKey(?Closure $selector = null): MutableTrackedMap&TrackedResult
	{
		$prevPairs = $this->store->toPairs();
		$sel = $selector ?? static fn ($k) => $k;
		$this->store->sortByPairs(static fn ($x, $y) => $sel($x[0]) <=> $sel($y[0]));
		$this->_changed = $this->store->toPairs() !== $prevPairs;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortByKeyDesc(?Closure $selector = null): MutableTrackedMap&TrackedResult
	{
		$prevPairs = $this->store->toPairs();
		$sel = $selector ?? static fn ($k) => $k;
		$this->store->sortByPairs(static fn ($x, $y) => $sel($y[0]) <=> $sel($x[0]));
		$this->_changed = $this->store->toPairs() !== $prevPairs;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortByValue(?Closure $selector = null): MutableTrackedMap&TrackedResult
	{
		$prevPairs = $this->store->toPairs();
		$sel = $selector ?? static fn ($v) => $v;
		$this->store->sortByPairs(static fn ($x, $y) => $sel($x[1]) <=> $sel($y[1]));
		$this->_changed = $this->store->toPairs() !== $prevPairs;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortByValueDesc(?Closure $selector = null): MutableTrackedMap&TrackedResult
	{
		$prevPairs = $this->store->toPairs();
		$sel = $selector ?? static fn ($v) => $v;
		$this->store->sortByPairs(static fn ($x, $y) => $sel($y[1]) <=> $sel($x[1]));
		$this->_changed = $this->store->toPairs() !== $prevPairs;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortBy(Closure $selector): MutableTrackedMap&TrackedResult
	{
		$prevPairs = $this->store->toPairs();
		$this->store->sortByPairs(static fn ($x, $y) => $selector($x[1], $x[0]) <=> $selector($y[1], $y[0]));
		$this->_changed = $this->store->toPairs() !== $prevPairs;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortByDesc(Closure $selector): MutableTrackedMap&TrackedResult
	{
		$prevPairs = $this->store->toPairs();
		$this->store->sortByPairs(static fn ($x, $y) => $selector($y[1], $y[0]) <=> $selector($x[1], $x[0]));
		$this->_changed = $this->store->toPairs() !== $prevPairs;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortWithKey(Closure $comparator): MutableTrackedMap&TrackedResult
	{
		$prevPairs = $this->store->toPairs();
		$this->store->sortByPairs(static fn ($x, $y) => $comparator($x[0], $y[0]));
		$this->_changed = $this->store->toPairs() !== $prevPairs;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortWithValue(Closure $comparator): MutableTrackedMap&TrackedResult
	{
		$prevPairs = $this->store->toPairs();
		$this->store->sortByPairs(static fn ($x, $y) => $comparator($x[1], $y[1]));
		$this->_changed = $this->store->toPairs() !== $prevPairs;
		return $this;
	}

	/** {@inheritDoc} */
	public function sortWith(Closure $comparator): MutableTrackedMap&TrackedResult
	{
		$prevPairs = $this->store->toPairs();
		$this->store->sortByPairs(
			static fn ($x, $y) => $comparator(
				new SimpleMapEntry($x[0], $x[1]),
				new SimpleMapEntry($y[0], $y[1])
			)
		);
		$this->_changed = $this->store->toPairs() !== $prevPairs;
		return $this;
	}

	/** {@inheritDoc} */
	public function reverse(): MutableTrackedMap&TrackedResult
	{
		$prevPairs = $this->store->toPairs();
		$this->store->reverse();
		$this->_changed = $this->store->toPairs() !== $prevPairs;
		return $this;
	}

	/** {@inheritDoc} */
	public function shuffle(): MutableTrackedMap&TrackedResult
	{
		$prevPairs = $this->store->toPairs();
		$this->store->shuffle();
		$this->_changed = $this->store->toPairs() !== $prevPairs;
		return $this;
	}

	// --- Internal ---

	/**
	 * @param K $offset
	 * @param V $value
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->put($offset, $value);
	}

	/**
	 * @param K $offset
	 */
	public function offsetUnset(mixed $offset): void
	{
		$this->remove($offset);
	}
}
