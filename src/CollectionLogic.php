<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

use Closure;
use LogicException;
use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\List\MutableList;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Sequence\Sequence;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\Set;
use Noctud\Collection\Operation\ChunkOperation;
use Noctud\Collection\Operation\DistinctOperation;
use Noctud\Collection\Operation\DropOperation;
use Noctud\Collection\Operation\FilterOperation;
use Noctud\Collection\Operation\FlatMapOperation;
use Noctud\Collection\Operation\FlattenOperation;
use Noctud\Collection\Operation\GroupOperation;
use Noctud\Collection\Operation\MapKeyValueOperation;
use Noctud\Collection\Operation\PartitionOperation;
use Noctud\Collection\Operation\SetOperation;
use Noctud\Collection\Operation\TakeOperation;
use Noctud\Collection\Operation\UnzipOperation;
use Noctud\Collection\Store\ReadWriteElementStore;
use Noctud\Collection\Operation\WindowOperation;
use Noctud\Collection\Operation\ZipOperation;
use Noctud\Collection\Operation\ZipWithNextOperation;
use Noctud\Collection\Store\AbstractElementStore;
use Noctud\Collection\Store\ReadOnlyElementStore;
use Traversable;
use NoDiscard;

/**
 * @phpstan-type PhpStormBugBypass array{0:NK,1:NV}
 * @template E
 * @property ReadOnlyElementStore<E> $store
 * @mixin Collection<E>
 */
trait CollectionLogic
{
	/** @use IterableTerminalsLogic<E> */
	use IterableTerminalsLogic;

	// --- Element Access ---

	/** {@inheritDoc} */
	public function first()
	{
		return $this->store->first(true);
	}

	/** {@inheritDoc} */
	public function firstOrNull(): mixed
	{
		return $this->store->first();
	}

	/** {@inheritDoc} */
	public function last()
	{
		return $this->store->last(true);
	}

	/** {@inheritDoc} */
	public function lastOrNull(): mixed
	{
		return $this->store->last();
	}

	/**
	 * {@inheritDoc}
	 *
	 * The size is known, so an index outside the bounds is rejected without walking.
	 */
	public function elementAt(int $index)
	{
		// @phpstan-ignore smaller.alwaysFalse (defensive guard: the phpdoc type does not bind untyped callers)
		if ($index < 0) {
			throw new IndexOutOfBoundsException('Cannot use a negative index.');
		}

		if ($index >= $this->count()) {
			throw new IndexOutOfBoundsException('Index out of bounds: ' . $index);
		}

		foreach ($this as $i => $v) {
			if ($i === $index) {
				return $v;
			}
		}

		// Unreachable: the bounds check above guarantees a match.
		throw new LogicException('Unreachable'); // @codeCoverageIgnore
	}

	/**
	 * {@inheritDoc}
	 *
	 * The size is known, so an index outside the bounds is answered without walking.
	 */
	public function elementAtOrNull(int $index): mixed
	{
		if ($index < 0 || $index >= $this->count()) {
			return null;
		}

		foreach ($this as $i => $v) {
			if ($i === $index) {
				return $v;
			}
		}

		// Unreachable: the bounds check above guarantees a match.
		throw new LogicException('Unreachable'); // @codeCoverageIgnore
	}

	/** {@inheritDoc} */
	public function random()
	{
		return $this->store->random(true);
	}

	/** {@inheritDoc} */
	public function randomOrNull(): mixed
	{
		return $this->store->random();
	}

	// --- Querying ---

	/** {@inheritDoc} */
	public function contains(mixed $element): bool
	{
		return $this->store->contains($element);
	}

	/** {@inheritDoc} */
	public function containsAll(iterable $elements): bool
	{
		if ($this instanceof Set) {
			foreach ($elements as $x) {
				if (!$this->contains($x)) {
					return false;
				}
			}

			return true;
		}

		// contains() scans the whole store here: look the elements up in it once instead.
		$wanted = $elements instanceof Traversable ? iterator_to_array($elements, false) : array_values($elements);
		$lookup = new StrictElementLookup($this->store->toArray(), count($wanted));
		foreach ($wanted as $x) {
			if (!$lookup->contains($x)) {
				return false;
			}
		}

		return true;
	}

	/** {@inheritDoc} */
	public function isEmpty(): bool
	{
		return $this->store->isEmpty();
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

	// --- Aggregation ---

	/** {@inheritDoc} */
	#[NoDiscard]
	public function countBy(Closure $keySelector): ImmutableMap
	{
		return $this->newMapOf(new GroupOperation($this)->countByKey($keySelector)); // @phpstan-ignore return.type
	}

	// --- Transformation ---

	/** {@inheritDoc} */
	#[NoDiscard]
	public function filter(Closure $predicate): ImmutableCollection
	{
		$i = 0;
		return $this->newCollectionOf(new FilterOperation($this->store)->byValue(function ($v) use ($predicate, &$i) {
			return $predicate($v, $i++);
		}));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function filterNotNull(): ImmutableCollection
	{
		return $this->newCollectionOf(new FilterOperation($this->store)->byValue(fn ($v) => $v !== null));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function filterInstanceOf(string $type): ImmutableCollection
	{
		return $this->newTransformedCollectionOf(new FilterOperation($this->store)->byValue(fn ($v) => $v instanceof $type));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function map(Closure $transform): ImmutableCollection
	{
		return $this->newTransformedCollectionOf(new MapKeyValueOperation($this->store)->items(fn ($v, $k) => $transform($v, $k)));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): ImmutableCollection
	{
		return $this->newTransformedCollectionOf(new MapKeyValueOperation($this->store)->itemsNotNull(fn ($v, $k) => $transform($v, $k)));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function flatMap(Closure $transform): ImmutableCollection
	{
		$i = 0;
		return $this->newTransformedCollectionOf(new FlatMapOperation($this->store)->items(function ($v) use ($transform, &$i) {
			return $transform($v, $i++);
		}));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function flatten(): ImmutableCollection
	{
		return $this->newTransformedCollectionOf(new FlattenOperation($this->store)->items());
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function takeFirst(int $n = 1): ImmutableCollection
	{
		return $this->newCollectionOf(new TakeOperation($this->store->toArray())->first($n));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function dropFirst(int $n = 1): ImmutableCollection
	{
		return $this->newCollectionOf(new DropOperation($this->store->toArray())->first($n));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function takeLast(int $n = 1): ImmutableCollection
	{
		return $this->newCollectionOf(new TakeOperation($this->store->toArray())->last($n));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function dropLast(int $n = 1): ImmutableCollection
	{
		return $this->newCollectionOf(new DropOperation($this->store->toArray())->last($n));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): ImmutableCollection
	{
		return $this->newCollectionOf(new TakeOperation($this->store)->byPredicate($predicate));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): ImmutableCollection
	{
		return $this->newCollectionOf(new DropOperation($this->store)->byPredicate($predicate));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): ImmutableCollection
	{
		return $this->newCollectionOf(new TakeOperation($this->store->toArray())->lastByPredicate($predicate));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): ImmutableCollection
	{
		return $this->newCollectionOf(new DropOperation($this->store->toArray())->lastByPredicate($predicate));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function distinct(): ImmutableCollection
	{
		return $this->newCollectionOf(new DistinctOperation($this->store)->items());
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function distinctBy(Closure $selector): ImmutableCollection
	{
		return $this->newCollectionOf(new DistinctOperation($this->store)->bySelector($selector));
	}

	// --- Ordering ---

	/** {@inheritDoc} */
	#[NoDiscard]
	public function sorted(): ImmutableCollection
	{
		if ($this->store instanceof ReadWriteElementStore) { // @phpstan-ignore instanceof.alwaysTrue
			$store = clone $this->store;
			$store->sort();
			return $this->newCollectionOf($store);
		}

		$arr = $this->store->toArray();
		sort($arr);
		return $this->newCollectionOf($arr);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function sortedDesc(): ImmutableCollection
	{
		if ($this->store instanceof ReadWriteElementStore) { // @phpstan-ignore instanceof.alwaysTrue
			$store = clone $this->store;
			$store->sort(static fn ($a, $b) => $b <=> $a);
			return $this->newCollectionOf($store);
		}

		$arr = $this->store->toArray();
		rsort($arr);
		return $this->newCollectionOf($arr);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function sortedBy(Closure $selector): ImmutableCollection
	{
		if ($this->store instanceof ReadWriteElementStore) { // @phpstan-ignore instanceof.alwaysTrue
			$store = clone $this->store;
			$store->sortBy($selector);
			return $this->newCollectionOf($store);
		}

		$arr = $this->store->toArray();
		$arr = array_values(AbstractElementStore::orderedBy($arr, $selector));
		return $this->newCollectionOf($arr);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): ImmutableCollection
	{
		if ($this->store instanceof ReadWriteElementStore) { // @phpstan-ignore instanceof.alwaysTrue
			$store = clone $this->store;
			$store->sortBy($selector, descending: true);
			return $this->newCollectionOf($store);
		}

		$arr = $this->store->toArray();
		$arr = array_values(AbstractElementStore::orderedBy($arr, $selector, descending: true));
		return $this->newCollectionOf($arr);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): ImmutableCollection
	{
		if ($this->store instanceof ReadWriteElementStore) { // @phpstan-ignore instanceof.alwaysTrue
			$store = clone $this->store;
			$store->sort($comparator);
			return $this->newCollectionOf($store);
		}

		$arr = $this->store->toArray();
		usort($arr, $comparator);
		return $this->newCollectionOf($arr);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function reversed(): ImmutableCollection
	{
		if ($this->store instanceof ReadWriteElementStore) { // @phpstan-ignore instanceof.alwaysTrue
			$store = clone $this->store;
			$store->reverse();
			return $this->newCollectionOf($store);
		}

		return $this->newCollectionOf(array_reverse($this->store->toArray()));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function shuffled(): ImmutableCollection
	{
		if ($this->store instanceof ReadWriteElementStore) { // @phpstan-ignore instanceof.alwaysTrue
			$store = clone $this->store;
			$store->shuffle();
			return $this->newCollectionOf($store);
		}

		$arr = $this->store->toArray();
		shuffle($arr);
		return $this->newCollectionOf($arr);
	}

	// --- Iteration ---

	/** {@inheritDoc} */
	public function onEach(Closure $action): static
	{
		$this->forEach($action);

		return $this;
	}

	// --- Conversion ---

	/** {@inheritDoc} */
	#[NoDiscard]
	public function asSequence(): Sequence
	{
		return sequenceOf($this);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toArray(): array
	{
		return $this->store->toArray();
	}

	/**
	 * {@inheritDoc}
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function toList(): ImmutableList
	{
		if ($this instanceof ImmutableList) {
			return $this;
		}

		return listOf($this->store); // @phpstan-ignore return.type, argument.templateType
	}

	/**
	 * {@inheritDoc}
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function toSet(): ImmutableSet
	{
		if ($this instanceof ImmutableSet) {
			return $this;
		}

		return setOf($this->store); // @phpstan-ignore return.type, argument.templateType
	}

	/**
	 * @return ImmutableList<E>
	 */
	#[NoDiscard]
	public function toImmutable(): ImmutableList
	{
		if ($this instanceof ImmutableList) {
			return $this;
		}

		return listOf($this->toArray());
	}

	/**
	 * @return MutableList<E>
	 */
	#[NoDiscard]
	public function toMutable(): MutableList
	{
		return mutableListOf($this->store);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toMap(Closure $keySelector, ?Closure $valueTransform = null): ImmutableMap
	{
		return $this->newMapOf((function () use ($keySelector, $valueTransform) {
			foreach ($this->store as $i => $v) {
				yield $keySelector($v, $i) => $valueTransform === null ? $v : $valueTransform($v, $i);
			}
		})());
	}

	/** @return list<E> */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function chunked(int $size): ImmutableList
	{
		return $this->newListOf((function () use ($size) {
			$chunks = new ChunkOperation($this->store->toArray())->ofSize($size);
			foreach ($chunks as $chunk) {
				yield $this->newListOf($chunk);
			}
		})());
	}

	/**
	 * {@inheritDoc}
	 *
	 * The params are typed `int` (not the interface's `positive-int`) so the
	 * defensive non-positive guard in WindowOperation stays a live runtime safety net.
	 *
	 * @param int $size
	 * @param int $step
	 */
	#[NoDiscard]
	public function windowed(int $size, int $step = 1, bool $partialWindows = false): ImmutableList
	{
		return $this->newListOf((function () use ($size, $step, $partialWindows) {
			$windows = new WindowOperation($this->store->toArray())->ofSize($size, $step, $partialWindows);
			foreach ($windows as $window) {
				yield $this->newListOf($window);
			}
		})());
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function zip(iterable $other): ImmutableList
	{
		return $this->newListOf(new ZipOperation($this->store)->with($other));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function zipWithNext(): ImmutableList
	{
		return $this->newListOf(new ZipWithNextOperation($this->store)->pairs());
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function unzip(): array
	{
		[$first, $second] = new UnzipOperation($this->store)->pairs();

		return [$this->newListOf($first), $this->newListOf($second)];
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function partition(Closure $predicate): array
	{
		[$matching, $nonMatching] = new PartitionOperation($this->store)->byPredicate($predicate);
		return [$this->newCollectionOf($matching), $this->newCollectionOf($nonMatching)];
	}

	/**
	 * {@inheritDoc}
	 * @template K of string|int|bool|float|object
	 * @template V
	 * @param Closure(E, int):K $keySelector
	 * @param (Closure(E, int):V)|null $valueTransform
	 * @return ImmutableMap<K, ImmutableCollection<E>>
	 */
	#[NoDiscard]
	public function groupBy(Closure $keySelector, ?Closure $valueTransform = null): ImmutableMap
	{
		$groups = new GroupOperation($this)->byKey($keySelector, $valueTransform);

		foreach ($groups as $k => $bucket) {
			$groups->put($k, $this->newListOf($bucket)); // @phpstan-ignore argument.type
		}

		return $this->newMapOf($groups); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param iterable<mixed> $other
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function intersect(iterable $other): ImmutableSet
	{
		return setOf(new SetOperation($this->store)->intersect($other));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template NE
	 * @param iterable<NE> $other
	 * @return ImmutableSet<E|NE>
	 */
	#[NoDiscard]
	public function union(iterable $other): ImmutableSet
	{
		return setOf(new SetOperation($this->store)->union($other));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param iterable<mixed> $other
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function subtract(iterable $other): ImmutableSet
	{
		return setOf(new SetOperation($this->store)->subtract($other));
	}

	// --- Factory Methods ---

	/**
	 * Creates a new immutable list from the given data.
	 *
	 * @template NE
	 * @param iterable<NE> $data
	 * @return ImmutableList<NE>
	 */
	protected function newListOf(iterable $data = []): ImmutableList
	{
		return listOf($data);
	}

	/**
	 * Creates a new immutable map from the given data.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 * @param iterable<NK,NV> $data
	 * @return ImmutableMap<NK,NV>
	 */
	protected function newMapOf(iterable $data = []): ImmutableMap
	{
		return mapOf($data);
	}

	/**
	 * Creates the result of an element-type-changing operation (map, flatMap, flatten, filterInstanceOf).
	 *
	 * Kept separate from newCollectionOf so that concrete Logic traits can bypass a
	 * self-preserving newCollectionOf override: a transform result no longer holds
	 * elements of E and must not go through the subtype's constructor. A class built
	 * on this trait directly that overrides newCollectionOf() to rebuild itself has
	 * to override this one as well.
	 *
	 * @template NE
	 * @param iterable<NE> $data
	 * @return ImmutableCollection<NE>
	 */
	protected function newTransformedCollectionOf(iterable $data): ImmutableCollection
	{
		return $this->newCollectionOf($data);
	}

	// --- Internal ---

	/**
	 * A store backed by an array hands it out as is. The views over a map keep walking their
	 * iterator: building all of their elements would cost a terminal that stops early.
	 *
	 * @return iterable<int, E>
	 */
	protected function terminalElements(): iterable
	{
		if (!$this->store instanceof AbstractElementStore) {
			return $this;
		}

		/** @var AbstractElementStore<E> $store */
		$store = $this->store;
		return $store->toArray();
	}

	public function getIterator(): Traversable
	{
		return $this->store->getIterator();
	}

	public function __debugInfo(): array
	{
		return $this->store->toArray();
	}

	/**
	 * @internal
	 * @return ReadOnlyElementStore<E>
	 */
	public function __internalCollectionStore(): ReadOnlyElementStore // phpcs:ignore Generic.NamingConventions.CamelCapsFunctionName.MethodDoubleUnderscore
	{
		return $this->store;
	}
}
