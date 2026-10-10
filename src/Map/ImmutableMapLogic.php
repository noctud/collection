<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

use Closure;
use Noctud\Collection\Exception\UnsupportedOperationException;
use Noctud\Collection\Operation\FilterOperation;

/**
 * @template K of string|int|bool|float|object
 * @template V
 * @mixin ImmutableMap<K,V>
 */
trait ImmutableMapLogic
{
	/** @use MapLogic<K,V> */
	use MapLogic;

	// --- Mutation (returns new) ---

	/** {@inheritDoc} */
	public function put(string|int|bool|float|object $key, mixed $value): ImmutableMap
	{
		$entries = clone $this->store;
		$entries->put($key, $value);

		return $this->newMapOf($entries); // @phpstan-ignore return.type
	}

	/** {@inheritDoc} */
	public function putIfAbsent(string|int|bool|float|object $key, mixed $value): ImmutableMap
	{
		if ($this->store->containsKey($key)) { // @phpstan-ignore argument.type
			return $this; // @phpstan-ignore return.type
		}

		$entries = clone $this->store;
		$entries->put($key, $value);

		return $this->newMapOf($entries); // @phpstan-ignore return.type
	}

	/** {@inheritDoc} */
	public function putAll(iterable $data): ImmutableMap
	{
		$entries = clone $this->store;
		$entries->putAllFromAssoc($data);

		return $this->newMapOf($entries); // @phpstan-ignore return.type
	}

	/** {@inheritDoc} */
	public function putAllPairs(iterable $data): ImmutableMap
	{
		$entries = clone $this->store;
		$entries->putAllFromPairs($data);

		return $this->newMapOf($entries); // @phpstan-ignore return.type
	}

	/** {@inheritDoc} */
	public function putFirst(string|int|bool|float|object $key, mixed $value): ImmutableMap
	{
		$entries = clone $this->store;
		$entries->putFirst($key, $value);

		return $this->newMapOf($entries); // @phpstan-ignore return.type
	}

	/** {@inheritDoc} */
	public function remove(string|int|bool|float|object $key): ImmutableMap
	{
		$entries = clone $this->store;
		$entries->remove($key); // @phpstan-ignore argument.type
		return $this->newMapOf($entries);
	}

	/** {@inheritDoc} */
	public function removeFirst(): ImmutableMap
	{
		if ($this->store->isEmpty()) {
			return $this;
		}

		$entries = clone $this->store;
		$entries->removeFirst();
		return $this->newMapOf($entries);
	}

	/** {@inheritDoc} */
	public function removeLast(): ImmutableMap
	{
		if ($this->store->isEmpty()) {
			return $this;
		}

		$entries = clone $this->store;
		$entries->removeLast();
		return $this->newMapOf($entries);
	}

	/** {@inheritDoc} */
	public function removeIf(Closure $predicate): ImmutableMap
	{
		return $this->newMapOf(new FilterOperation($this->store)->byPredicate(fn ($v, $k) => !$predicate($v, $k)));
	}

	/** {@inheritDoc} */
	public function removeIfKey(Closure $predicate): ImmutableMap
	{
		return $this->newMapOf(new FilterOperation($this->store)->byKey(fn ($k) => !$predicate($k)));
	}

	/** {@inheritDoc} */
	public function removeIfValue(Closure $predicate): ImmutableMap
	{
		return $this->newMapOf(new FilterOperation($this->store)->byValue(fn ($v) => !$predicate($v)));
	}

	/** {@inheritDoc} */
	public function removeNullValues(): ImmutableMap
	{
		return $this->newMapOf(new FilterOperation($this->store)->notNullValues());
	}

	// --- Internal ---

	/**
	 * @param K $offset
	 * @param V $value
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new UnsupportedOperationException('Cannot modify ImmutableMap via array access');
	}

	/**
	 * @param K $offset
	 */
	public function offsetUnset(mixed $offset): void
	{
		throw new UnsupportedOperationException('Cannot modify ImmutableMap via array access');
	}
}
