<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

use Closure;
use Noctud\Collection\Collection;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\Map\View\MapEntrySet;
use Noctud\Collection\Map\View\MapKeySet;
use Noctud\Collection\Map\View\MapValueCollection;
use Noctud\Collection\Operation\FilterOperation;
use Noctud\Collection\Operation\FlatMapKeyValueOperation;
use Noctud\Collection\Operation\FlipKeyValueOperation;
use Noctud\Collection\Operation\MapKeyValueOperation;
use Noctud\Collection\Set\Set;
use Noctud\Collection\Store\KeyValueStore;
use NoDiscard;
use Traversable;
use function Noctud\Collection\listOf;
use function Noctud\Collection\mapOf;
use function Noctud\Collection\mutableMapOf;

/**
 * Shared logic for all Map implementations.
 *
 * Transformation methods (filter, mapKeys, sorted, etc.) always return ImmutableMap.
 * Both MutableMap and ImmutableMap use this trait, ensuring consistent behavior.
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @property KeyValueStore<K,V> $store
 */
trait MapLogic
{
	// --- Properties ---

	/** @var Set<K> */
	public Set $keys {
		get => $this->keys ??= new MapKeySet($this->store); // phpcs:ignore PSR2.Classes.PropertyDeclaration.ScopeMissing
	}

	/** @var Collection<V> */
	public Collection $values {
		get => $this->values ??= new MapValueCollection($this->store); // phpcs:ignore PSR2.Classes.PropertyDeclaration.ScopeMissing
	}

	/** @var Set<MapEntry<K,V>> */
	public Set $entries {
		get => $this->entries ??= new MapEntrySet($this->store); // phpcs:ignore PSR2.Classes.PropertyDeclaration.ScopeMissing
	}

	// --- Element Access ---

	/** {@inheritDoc} */
	public function get(string|int|bool|float|object $key)
	{
		return $this->store->get($key, true); // @phpstan-ignore argument.type
	}

	/** {@inheritDoc} */
	public function getOrNull(string|int|bool|float|object $key): mixed
	{
		return $this->store->get($key); // @phpstan-ignore argument.type
	}

	/** {@inheritDoc} */
	public function getOrDefault(string|int|bool|float|object $key, mixed $default): mixed
	{
		try {
			return $this->store->get($key, true); // @phpstan-ignore argument.type
		} catch (NoSuchElementException) {
			return $default;
		}
	}

	/** {@inheritDoc} */
	public function getOrCompute(string|int|bool|float|object $key, Closure $compute): mixed
	{
		try {
			return $this->store->get($key, true); // @phpstan-ignore argument.type
		} catch (NoSuchElementException) {
			return $compute();
		}
	}

	// --- Querying ---

	/**
	 * {@inheritDoc}
	 * @param K $key
	 */
	public function containsKey(string|int|bool|float|object $key): bool
	{
		return $this->store->containsKey($key); // @phpstan-ignore argument.type
	}

	/**
	 * {@inheritDoc}
	 * @param V $value
	 */
	public function containsValue(mixed $value): bool
	{
		return $this->store->contains($value);
	}

	/** {@inheritDoc} */
	public function all(Closure $predicate): bool
	{
		foreach ($this->store as $k => $v) {
			if (!$predicate($v, $k)) {
				return false;
			}
		}

		return true;
	}

	/** {@inheritDoc} */
	public function any(Closure $predicate): bool
	{
		foreach ($this->store as $k => $v) {
			if ($predicate($v, $k)) {
				return true;
			}
		}

		return false;
	}

	/** {@inheritDoc} */
	public function none(Closure $predicate): bool
	{
		return !$this->any($predicate);
	}

	/** {@inheritDoc} */
	public function isEmpty(): bool
	{
		return $this->store->isEmpty();
	}

	/** {@inheritDoc} */
	public function isNotEmpty(): bool
	{
		return !$this->isEmpty();
	}

	/**
	 * {@inheritDoc}
	 * @return int<0, max>
	 */
	public function count(): int
	{
		/** @var int<0, max> $storeCount */
		$storeCount = $this->store->count();

		return $storeCount;
	}

	/** {@inheritDoc} */
	public function countWhere(Closure $predicate): int
	{
		/** @var int<0, max> $count */
		$count = 0;
		foreach ($this->store as $k => $v) {
			if ($predicate($v, $k)) {
				$count++;
			}
		}

		return $count;
	}

	// --- Transformation ---

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): ImmutableMap
	{
		return $this->newMapOf(
			new FilterOperation($this->store)->byPredicate($predicate)
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function filterKeys(Closure $predicate): ImmutableMap
	{
		return $this->newMapOf(
			new FilterOperation($this->store)->byKey($predicate)
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function filterValues(Closure $predicate): ImmutableMap
	{
		return $this->newMapOf(
			new FilterOperation($this->store)->byValue($predicate)
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K, (V is null ? never : V)>
	 */
	#[NoDiscard] // @phpstan-ignore conditionalType.subjectNotFound (in classes with a concrete V the conditional subject is already substituted)
	public function filterValuesNotNull(): ImmutableMap
	{
		return $this->newMapOf(
			new FilterOperation($this->store)->notNullValues()
		);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function filterValuesInstanceOf(string $type): ImmutableMap
	{
		return $this->newMapOf( // @phpstan-ignore return.type
			new FilterOperation($this->store)->byValue(fn ($v) => $v instanceof $type)
		);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function map(Closure $transform): ImmutableList
	{
		return $this->newListOf(new MapKeyValueOperation($this->store)->items($transform));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): ImmutableList
	{
		return $this->newListOf(new MapKeyValueOperation($this->store)->itemsNotNull($transform));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function flatMap(Closure $transform): ImmutableList
	{
		return $this->newListOf(new FlatMapKeyValueOperation($this->store)->items($transform));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template NK of string|int|bool|float|object
	 * @param Closure(V, K):NK $transform
	 * @return ImmutableMap<NK,V>
	 */
	#[NoDiscard] // @phpstan-ignore generics.notSubtype
	public function mapKeys(Closure $transform): ImmutableMap
	{
		return $this->newTransformedMapOf(new MapKeyValueOperation($this->store)->keys($transform)); // @phpstan-ignore return.type, argument.templateType
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template NV
	 * @param Closure(V, K):NV $transform
	 * @return ImmutableMap<K,NV>
	 */
	#[NoDiscard]
	public function mapValues(Closure $transform): ImmutableMap
	{
		return $this->newTransformedMapOf(new MapKeyValueOperation($this->store)->values($transform));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template NV
	 * @param Closure(V, K):(NV|null) $transform
	 * @return ImmutableMap<K,NV>
	 */
	#[NoDiscard]
	public function mapValuesNotNull(Closure $transform): ImmutableMap
	{
		return $this->newTransformedMapOf(new MapKeyValueOperation($this->store)->valuesNotNull($transform));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<V,K>
	 */
	#[NoDiscard] // @phpstan-ignore generics.notSubtype
	public function flip(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): ImmutableMap
	{
		return $this->newTransformedMapOf( // @phpstan-ignore return.type, argument.templateType
			new FlipKeyValueOperation($this->store)->items($onCollision) // @phpstan-ignore argument.type
		);
	}


	// --- Ordering ---

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedByKey(?Closure $selector = null): ImmutableMap
	{
		$store = clone $this->store;
		$sel = $selector ?? static fn ($k) => $k;
		$store->sortByPairs(static fn ($x, $y) => $sel($x[0]) <=> $sel($y[0]));
		return $this->newMapOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedByKeyDesc(?Closure $selector = null): ImmutableMap
	{
		$store = clone $this->store;
		$sel = $selector ?? static fn ($k) => $k;
		$store->sortByPairs(static fn ($x, $y) => $sel($y[0]) <=> $sel($x[0]));
		return $this->newMapOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedByValue(?Closure $selector = null): ImmutableMap
	{
		$store = clone $this->store;
		$sel = $selector ?? static fn ($v) => $v;
		$store->sortByPairs(static fn ($x, $y) => $sel($x[1]) <=> $sel($y[1]));
		return $this->newMapOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedByValueDesc(?Closure $selector = null): ImmutableMap
	{
		$store = clone $this->store;
		$sel = $selector ?? static fn ($v) => $v;
		$store->sortByPairs(static fn ($x, $y) => $sel($y[1]) <=> $sel($x[1]));
		return $this->newMapOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): ImmutableMap
	{
		$store = clone $this->store;
		$store->sortByPairs(static fn ($x, $y) => $selector($x[1], $x[0]) <=> $selector($y[1], $y[0]));
		return $this->newMapOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): ImmutableMap
	{
		$store = clone $this->store;
		$store->sortByPairs(static fn ($x, $y) => $selector($y[1], $y[0]) <=> $selector($x[1], $x[0]));
		return $this->newMapOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedWithKey(Closure $comparator): ImmutableMap
	{
		$store = clone $this->store;
		$store->sortByPairs(static fn ($x, $y) => $comparator($x[0], $y[0]));
		return $this->newMapOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedWithValue(Closure $comparator): ImmutableMap
	{
		$store = clone $this->store;
		$store->sortByPairs(static fn ($x, $y) => $comparator($x[1], $y[1]));
		return $this->newMapOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): ImmutableMap
	{
		$store = clone $this->store;
		$store->sortByPairs(
			static fn ($x, $y) => $comparator(
				new SimpleMapEntry($x[0], $x[1]),
				new SimpleMapEntry($y[0], $y[1])
			)
		);
		return $this->newMapOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function reversed(): ImmutableMap
	{
		$store = clone $this->store;
		$store->reverse();
		return $this->newMapOf($store);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function shuffled(): ImmutableMap
	{
		$store = clone $this->store;
		$store->shuffle();
		return $this->newMapOf($store);
	}

	// --- Slicing ---

	/**
	 * {@inheritDoc}
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): ImmutableMap
	{
		if ($n <= 0) {
			return $this->newMapOf([]);
		}

		$pairs = $this->store->toPairs();

		return $this->newMapOf((function () use ($pairs, $n) {
			foreach (array_slice($pairs, 0, $n) as [$k, $v]) {
				yield $k => $v;
			}
		})());
	}

	/**
	 * {@inheritDoc}
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): ImmutableMap
	{
		if ($n <= 0) {
			return $this->newMapOf([]);
		}

		$pairs = $this->store->toPairs();

		return $this->newMapOf((function () use ($pairs, $n) {
			foreach (array_slice($pairs, -$n) as [$k, $v]) {
				yield $k => $v;
			}
		})());
	}

	/**
	 * {@inheritDoc}
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): ImmutableMap
	{
		if ($n <= 0) {
			return $this->newMapOf($this->store);
		}

		$pairs = $this->store->toPairs();

		return $this->newMapOf((function () use ($pairs, $n) {
			foreach (array_slice($pairs, $n) as [$k, $v]) {
				yield $k => $v;
			}
		})());
	}

	/**
	 * {@inheritDoc}
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): ImmutableMap
	{
		if ($n <= 0) {
			return $this->newMapOf($this->store);
		}

		$pairs = $this->store->toPairs();
		$length = count($pairs) - $n;

		if ($length <= 0) {
			return $this->newMapOf([]);
		}

		return $this->newMapOf((function () use ($pairs, $length) {
			foreach (array_slice($pairs, 0, $length) as [$k, $v]) {
				yield $k => $v;
			}
		})());
	}

	/**
	 * {@inheritDoc}
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): ImmutableMap
	{
		return $this->newMapOf((function () use ($predicate) {
			foreach ($this->store as $k => $v) {
				if (!$predicate($v, $k)) {
					break;
				}
				yield $k => $v;
			}
		})());
	}

	/**
	 * {@inheritDoc}
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): ImmutableMap
	{
		return $this->newMapOf((function () use ($predicate) {
			$dropping = true;
			foreach ($this->store as $k => $v) {
				if ($dropping && $predicate($v, $k)) {
					continue;
				}
				$dropping = false;
				yield $k => $v;
			}
		})());
	}

	/**
	 * {@inheritDoc}
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): ImmutableMap
	{
		$pairs = $this->store->toPairs();
		$i = count($pairs) - 1;
		while ($i >= 0 && $predicate($pairs[$i][1], $pairs[$i][0])) {
			$i--;
		}

		return $this->newMapOf((function () use ($pairs, $i) {
			foreach (array_slice($pairs, $i + 1) as [$k, $v]) {
				yield $k => $v;
			}
		})());
	}

	/**
	 * {@inheritDoc}
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): ImmutableMap
	{
		$pairs = $this->store->toPairs();
		$i = count($pairs) - 1;
		while ($i >= 0 && $predicate($pairs[$i][1], $pairs[$i][0])) {
			$i--;
		}

		return $this->newMapOf((function () use ($pairs, $i) {
			foreach (array_slice($pairs, 0, $i + 1) as [$k, $v]) {
				yield $k => $v;
			}
		})());
	}

	// --- Iteration ---

	/** {@inheritDoc} */
	public function forEach(Closure $action): static
	{
		foreach ($this->store as $k => $v) {
			$action($v, $k);
		}

		return $this;
	}

	/** {@inheritDoc} */
	public function forEachKey(Closure $action): static
	{
		foreach ($this->store as $k => $_) {
			$action($k);
		}

		return $this;
	}

	/** {@inheritDoc} */
	public function forEachValue(Closure $action): static
	{
		foreach ($this->store as $v) {
			$action($v);
		}

		return $this;
	}

	// --- Conversion ---

	/**
	 * {@inheritDoc}
	 * @return array<array-key,V>
	 */
	#[NoDiscard]
	public function toArray(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): array
	{
		return $this->store->toArray($onCollision);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toPairs(): array
	{
		return $this->store->toPairs();
	}

	/**
	 * {@inheritDoc}
	 * @return ImmutableMap<K,V>
	 */
	#[NoDiscard]
	public function toImmutable(): ImmutableMap
	{
		if ($this instanceof ImmutableMap) {
			return $this;
		}

		return $this->newMapOf($this->store);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toMutable(): MutableMap
	{
		return mutableMapOf($this->store);
	}

	// --- Factory Methods ---

	/**
	 * Creates a new immutable map from the given data.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 * @param iterable<NK,NV> $data
	 * @return ImmutableMap<NK,NV>
	 */
	protected function newMapOf(iterable $data): ImmutableMap
	{
		return mapOf($data);
	}

	/**
	 * Creates the result of a key- or value-type-changing operation (mapKeys, mapValues, flip).
	 *
	 * Not routed through newMapOf: a self-preserving subtype rebuilds itself there,
	 * and a transformed result no longer holds K/V entries — it must not go through
	 * the subtype's constructor.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 * @param iterable<NK,NV> $data
	 * @return ImmutableMap<NK,NV>
	 */
	protected function newTransformedMapOf(iterable $data): ImmutableMap
	{
		return mapOf($data);
	}

	/**
	 * Creates a new immutable list from the given data.
	 *
	 * @template NT
	 * @param iterable<NT> $data
	 * @return ImmutableList<NT>
	 */
	protected function newListOf(iterable $data): ImmutableList
	{
		return listOf($data);
	}

	// --- Internal ---

	/** {@inheritDoc} */
	public function getIterator(): Traversable
	{
		return $this->store->getIterator();
	}

	/** @return array<array-key, V> */
	public function jsonSerialize(): array
	{
		return $this->store->toArray();
	}

	/** {@inheritDoc} */
	public function offsetExists(mixed $offset): bool
	{
		return $this->containsKey($offset);
	}

	/** {@inheritDoc} */
	public function offsetGet(mixed $offset): mixed
	{
		return $this->get($offset);
	}

	public function __debugInfo(): array
	{
		return $this->store->toPairs();
	}

	/**
	 * @internal
	 * @return KeyValueStore<K,V>
	 */
	public function __internalCollectionStore(): KeyValueStore // phpcs:ignore Generic.NamingConventions.CamelCapsFunctionName.MethodDoubleUnderscore
	{
		return $this->store;
	}
}
