<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List;

use Closure;
use Noctud\Collection\CollectionLogic;
use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\Map\HashMap\HashKeyValueStore;
use Noctud\Collection\Exception\UnsupportedOperationException;
use Noctud\Collection\Operation\DistinctOperation;
use Noctud\Collection\Operation\DropOperation;
use Noctud\Collection\Operation\FilterOperation;
use Noctud\Collection\Operation\FlatMapOperation;
use Noctud\Collection\Operation\FlattenOperation;
use Noctud\Collection\Operation\MapKeyValueOperation;
use Noctud\Collection\Operation\TakeOperation;
use Noctud\Collection\Store\ReadWriteElementStore;
use Noctud\Collection\Store\ReadOnlyIndexedStore;
use Noctud\Collection\Map\ImmutableMap;
use NoDiscard;
use function Noctud\Collection\intMapOf;
use function Noctud\Collection\listOf;

/**
 * Shared logic for all List implementations.
 *
 * Transformation methods (filter, map, sorted, etc.) always return ImmutableList.
 * Both MutableList and ImmutableList use this trait, ensuring consistent behavior.
 *
 * @template E
 * @mixin ListInterface<E>
 * @property ReadOnlyIndexedStore<E> $store
 */
trait ListLogic
{
	/** @use CollectionLogic<E> */
	use CollectionLogic;

	// --- Element Access ---

	/** {@inheritDoc} */
	public function get(int $index)
	{
		return $this->store->get($index, true);
	}

	/** {@inheritDoc} */
	public function getOrNull(int $index): mixed
	{
		return $this->store->get($index);
	}

	/** {@inheritDoc} */
	public function getOrDefault(int $index, mixed $default): mixed
	{
		try {
			return $this->store->get($index, true);
		} catch (IndexOutOfBoundsException) {
			return $default;
		}
	}

	/** {@inheritDoc} */
	public function getOrCompute(int $index, Closure $compute): mixed
	{
		try {
			return $this->store->get($index, true);
		} catch (IndexOutOfBoundsException) {
			return $compute();
		}
	}

	/** {@inheritDoc} */
	public function indexOf(mixed $element): int
	{
		return $this->store->indexOf($element);
	}

	/** {@inheritDoc} */
	public function lastIndexOf(mixed $element): int
	{
		return $this->store->lastIndexOf($element);
	}

	/** {@inheritDoc} */
	public function indexOfFirst(Closure $predicate): int
	{
		foreach ($this->store as $i => $v) {
			if ($predicate($v, $i)) {
				return $i;
			}
		}

		return -1;
	}

	/** {@inheritDoc} */
	public function indexOfLast(Closure $predicate): int
	{
		$elements = $this->store->toArray();

		for ($i = count($elements) - 1; $i >= 0; $i--) {
			if ($predicate($elements[$i], $i)) {
				return $i;
			}
		}

		return -1;
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function slice(int $from, int $to): ImmutableList
	{
		$from = max(0, $from);
		$to = max($from, $to);

		if ($to > $this->store->count()) {
			throw new IndexOutOfBoundsException();
		}

		return $this->newCollectionOf(array_slice($this->store->toArray(), $from, $to - $from));
	}

	// --- ArrayAccess ---

	/**
	 * {@inheritDoc}
	 * @param mixed $offset
	 */
	public function offsetExists(mixed $offset): bool
	{
		return is_int($offset) && $offset >= 0 && $offset < $this->store->count();
	}

	/** {@inheritDoc} */
	public function offsetGet(mixed $offset): mixed
	{
		return $this->get($offset);
	}

	/**
	 * @param int|null $offset
	 * @param E $value
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new UnsupportedOperationException('Cannot modify a read-only List via array access');
	}

	public function offsetUnset(mixed $offset): void
	{
		throw new UnsupportedOperationException('Cannot modify a read-only List via array access');
	}

	// --- Transformation ---

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): ImmutableList
	{
		$i = 0;
		return $this->newCollectionOf(new FilterOperation($this->store)->byValue(function ($v) use ($predicate, &$i) {
			return $predicate($v, $i++);
		}));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function filterNotNull(): ImmutableList
	{
		return $this->newCollectionOf(new FilterOperation($this->store)->byValue(fn ($v) => $v !== null));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function filterInstanceOf(string $type): ImmutableList
	{
		return $this->newCollectionOf(new FilterOperation($this->store)->byValue(fn ($v) => $v instanceof $type)); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R
	 * @param Closure(E, int):R $transform
	 * @return ImmutableList<R>
	 */
	#[NoDiscard]
	public function map(Closure $transform): ImmutableList
	{
		return $this->newCollectionOf(new MapKeyValueOperation($this->store)->items(fn ($v, $k) => $transform($v, $k)));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R
	 * @param Closure(E, int):(R|null) $transform
	 * @return ImmutableList<R>
	 */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): ImmutableList
	{
		return $this->newCollectionOf(new MapKeyValueOperation($this->store)->itemsNotNull(fn ($v, $k) => $transform($v, $k)));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R
	 * @param Closure(E, int):iterable<R> $transform
	 * @return ImmutableList<R>
	 */
	#[NoDiscard]
	public function flatMap(Closure $transform): ImmutableList
	{
		$i = 0;
		return $this->newCollectionOf(new FlatMapOperation($this->store)->items(function ($v) use ($transform, &$i) {
			return $transform($v, $i++);
		}));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<mixed>
	 */
	#[NoDiscard]
	public function flatten(): ImmutableList
	{
		return $this->newCollectionOf(new FlattenOperation($this->store)->items());
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): ImmutableList
	{
		return $this->newCollectionOf(new TakeOperation($this->store)->first($n));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): ImmutableList
	{
		return $this->newCollectionOf(new DropOperation($this->store)->first($n));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): ImmutableList
	{
		return $this->newCollectionOf(new TakeOperation($this->store)->last($n));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): ImmutableList
	{
		return $this->newCollectionOf(new DropOperation($this->store)->last($n));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): ImmutableList
	{
		$i = 0;
		return $this->newCollectionOf(new TakeOperation($this->store)->byPredicate(function ($v) use ($predicate, &$i) {
			return $predicate($v, $i++);
		}));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): ImmutableList
	{
		$i = 0;
		return $this->newCollectionOf(new DropOperation($this->store)->byPredicate(function ($v) use ($predicate, &$i) {
			return $predicate($v, $i++);
		}));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): ImmutableList
	{
		return $this->newCollectionOf(new TakeOperation($this->store)->lastByPredicate($predicate));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): ImmutableList
	{
		return $this->newCollectionOf(new DropOperation($this->store)->lastByPredicate($predicate));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function distinct(): ImmutableList
	{
		return $this->newCollectionOf(new DistinctOperation($this->store)->items());
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function distinctBy(Closure $selector): ImmutableList
	{
		return $this->newCollectionOf(new DistinctOperation($this->store)->bySelector($selector));
	}

	// --- Ordering ---

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function sorted(): ImmutableList
	{
		/** @var ReadWriteElementStore<E> $store */
		$store = clone $this->store;
		$store->sort();
		return $this->newCollectionOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function sortedDesc(): ImmutableList
	{
		/** @var ReadWriteElementStore<E> $store */
		$store = clone $this->store;
		$store->sort(static fn ($a, $b) => $b <=> $a);
		return $this->newCollectionOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): ImmutableList
	{
		/** @var ReadWriteElementStore<E> $store */
		$store = clone $this->store;
		$store->sort(static fn ($a, $b) => $selector($a) <=> $selector($b));
		return $this->newCollectionOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): ImmutableList
	{
		/** @var ReadWriteElementStore<E> $store */
		$store = clone $this->store;
		$store->sort(static fn ($a, $b) => $selector($b) <=> $selector($a));
		return $this->newCollectionOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): ImmutableList
	{
		/** @var ReadWriteElementStore<E> $store */
		$store = clone $this->store;
		$store->sort($comparator);
		return $this->newCollectionOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function reversed(): ImmutableList
	{
		/** @var ReadWriteElementStore<E> $store */
		$store = clone $this->store;
		$store->reverse();
		return $this->newCollectionOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function shuffled(): ImmutableList
	{
		$arr = $this->store->toArray();
		shuffle($arr);
		return $this->newCollectionOf($arr);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template GK of string|int|bool|float|object
	 * @template GV
	 * @param Closure(E, int):GK $keySelector
	 * @param (Closure(E, int):GV)|null $valueTransform
	 * @return ImmutableMap<GK, ImmutableList<E>>
	 * @phpstan-ignore-next-line method.childReturnType
	 */
	#[NoDiscard]
	public function groupBy(Closure $keySelector, ?Closure $valueTransform = null): ImmutableMap
	{
		/** @var HashKeyValueStore<GK, array<int, E|GV>> $store */
		$store = HashKeyValueStore::empty();

		foreach ($this as $i => $v) {
			$k = $keySelector($v, $i);
			/** @var array<int, E|GV> $bucket */
			$bucket = $store->get($k) ?? [];
			$bucket[] = $valueTransform !== null ? $valueTransform($v, $i) : $v;
			$store->put($k, $bucket);
		}

		foreach ($store as $k => $bucket) {
			$store->put($k, $this->newCollectionOf($bucket)); // @phpstan-ignore argument.type
		}

		return $this->newMapOf($store); // @phpstan-ignore return.type
	}

	// --- Conversion ---

	/**
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function toImmutable(): ImmutableList
	{
		if ($this instanceof ImmutableList) {
			return $this;
		}

		return listOf($this->store); // @phpstan-ignore return.type, argument.templateType
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toIndexedMap(?Closure $valueTransform = null): ImmutableMap
	{
		if ($valueTransform === null) {
			return intMapOf($this->store->toArray());
		}

		return intMapOf((function () use ($valueTransform) {
			foreach ($this->store as $i => $v) {
				yield $i => $valueTransform($v, $i);
			}
		})());
	}

	// --- Factory Methods ---

	/**
	 * Creates a new immutable list from the given data.
	 *
	 * @template NE
	 * @param iterable<NE> $data
	 * @return ImmutableList<NE>
	 */
	protected function newCollectionOf(iterable $data): ImmutableList
	{
		return listOf($data);
	}
}
