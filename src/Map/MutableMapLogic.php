<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

use Closure;
use Noctud\Collection\Map\HashMap\HashKeyValueStore;
use Noctud\Collection\Map\HashMap\MutableTrackedHashMap;
use NoDiscard;

/**
 * @template K of string|int|bool|float|object
 * @template V
 * @mixin MutableMap<K,V>
 */
trait MutableMapLogic
{
	/** @use MapLogic<K,V> */
	use MapLogic;

	// --- Tracking ---

	/** @inheritDoc */
	#[NoDiscard]
	public function tracked(): MutableTrackedMap
	{
		/** @var HashKeyValueStore<K,V> $store */
		$store = $this->store;
		return new MutableTrackedHashMap($store);
	}

	// --- Element Access ---

	/** {@inheritDoc} */
	#[NoDiscard]
	public function getOrPut(string|int|bool|float|object $key, Closure $compute): mixed
	{
		if ($this->store->containsKey($key)) { // @phpstan-ignore argument.type
			return $this->store->get($key, true); // @phpstan-ignore argument.type
		}

		$value = $compute();
		$this->store->put($key, $value); // @phpstan-ignore argument.type

		return $value;
	}

	// --- Mutation: Add ---

	/** {@inheritDoc} */
	public function put(string|int|bool|float|object $key, mixed $value): MutableMap
	{
		$this->store->put($key, $value); // @phpstan-ignore argument.type
		return $this;
	}

	/** {@inheritDoc} */
	public function putIfAbsent(string|int|bool|float|object $key, mixed $value): MutableMap
	{
		if (!$this->store->containsKey($key)) { // @phpstan-ignore argument.type
			$this->store->put($key, $value); // @phpstan-ignore argument.type
		}

		return $this;
	}

	/** {@inheritDoc} */
	public function putAll(iterable $data): MutableMap
	{
		$this->store->putAllFromAssoc($data);
		return $this;
	}

	/** {@inheritDoc} */
	public function putAllPairs(iterable $data): MutableMap
	{
		$this->store->putAllFromPairs($data);
		return $this;
	}

	/** {@inheritDoc} */
	public function putFirst(string|int|bool|float|object $key, mixed $value): MutableMap
	{
		$this->store->putFirst($key, $value); // @phpstan-ignore argument.type
		return $this;
	}

	// --- Mutation: Remove ---

	/** {@inheritDoc} */
	public function remove(string|int|bool|float|object $key): MutableMap
	{
		$this->store->remove($key); // @phpstan-ignore argument.type
		return $this;
	}

	/** {@inheritDoc} */
	public function removeFirst(): MutableMap
	{
		$this->store->removeFirst();
		return $this;
	}

	/** {@inheritDoc} */
	public function removeLast(): MutableMap
	{
		$this->store->removeLast();
		return $this;
	}

	/** {@inheritDoc} */
	public function removeIf(Closure $predicate): MutableMap
	{
		$this->store->removeIf($predicate);
		return $this;
	}

	/** {@inheritDoc} */
	public function removeIfKey(Closure $predicate): MutableMap
	{
		$this->store->removeIfKey($predicate);
		return $this;
	}

	/** {@inheritDoc} */
	public function removeIfValue(Closure $predicate): MutableMap
	{
		$this->store->removeIfValue($predicate);
		return $this;
	}

	/** {@inheritDoc} */
	public function removeNullValues(): MutableMap
	{
		$this->store->removeIfValue(fn ($v) => $v === null);
		return $this;
	}

	/** {@inheritDoc} */
	public function clear(): MutableMap
	{
		$this->store->clear();

		return $this;
	}

	// --- Mutation: Order ---

	/** {@inheritDoc} */
	public function sortByKey(?Closure $selector = null): MutableMap
	{
		$sel = $selector ?? static fn ($k) => $k;
		$this->store->sortByPairs(static fn ($x, $y) => $sel($x[0]) <=> $sel($y[0]));

		return $this;
	}

	/** {@inheritDoc} */
	public function sortByKeyDesc(?Closure $selector = null): MutableMap
	{
		$sel = $selector ?? static fn ($k) => $k;
		$this->store->sortByPairs(static fn ($x, $y) => $sel($y[0]) <=> $sel($x[0]));

		return $this;
	}

	/** {@inheritDoc} */
	public function sortByValue(?Closure $selector = null): MutableMap
	{
		$sel = $selector ?? static fn ($v) => $v;
		$this->store->sortByPairs(static fn ($x, $y) => $sel($x[1]) <=> $sel($y[1]));

		return $this;
	}

	/** {@inheritDoc} */
	public function sortByValueDesc(?Closure $selector = null): MutableMap
	{
		$sel = $selector ?? static fn ($v) => $v;
		$this->store->sortByPairs(static fn ($x, $y) => $sel($y[1]) <=> $sel($x[1]));

		return $this;
	}

	/** {@inheritDoc} */
	public function sortBy(Closure $selector): MutableMap
	{
		$this->store->sortByPairs(static fn ($x, $y) => $selector($x[1], $x[0]) <=> $selector($y[1], $y[0]));

		return $this;
	}

	/** {@inheritDoc} */
	public function sortByDesc(Closure $selector): MutableMap
	{
		$this->store->sortByPairs(static fn ($x, $y) => $selector($y[1], $y[0]) <=> $selector($x[1], $x[0]));

		return $this;
	}

	/** {@inheritDoc} */
	public function sortWithKey(Closure $comparator): MutableMap
	{
		$this->store->sortByPairs(static fn ($x, $y) => $comparator($x[0], $y[0]));

		return $this;
	}

	/** {@inheritDoc} */
	public function sortWithValue(Closure $comparator): MutableMap
	{
		$this->store->sortByPairs(static fn ($x, $y) => $comparator($x[1], $y[1]));

		return $this;
	}

	/** {@inheritDoc} */
	public function sortWith(Closure $comparator): MutableMap
	{
		$this->store->sortByPairs(
			static fn ($x, $y) => $comparator(
				new SimpleMapEntry($x[0], $x[1]),
				new SimpleMapEntry($y[0], $y[1])
			)
		);

		return $this;
	}

	/** {@inheritDoc} */
	public function reverse(): MutableMap
	{
		$this->store->reverse();

		return $this;
	}

	/** {@inheritDoc} */
	public function shuffle(): MutableMap
	{
		$this->store->shuffle();

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
		$this->store->remove($offset);
	}
}
