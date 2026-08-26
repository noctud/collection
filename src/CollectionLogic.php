<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

use Closure;
use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Exception\UnsupportedOperationException;
use Stringable;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\List\MutableList;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Operation\ChunkOperation;
use Noctud\Collection\Operation\DistinctOperation;
use Noctud\Collection\Operation\DropOperation;
use Noctud\Collection\Operation\FilterOperation;
use Noctud\Collection\Operation\FlatMapOperation;
use Noctud\Collection\Operation\FlattenOperation;
use Noctud\Collection\Operation\MapKeyValueOperation;
use Noctud\Collection\Operation\PartitionOperation;
use Noctud\Collection\Map\HashMap\HashKeyValueStore;
use Noctud\Collection\Operation\SetOperation;
use Noctud\Collection\Operation\TakeOperation;
use Noctud\Collection\Store\ReadWriteElementStore;
use Noctud\Collection\Operation\ZipOperation;
use Noctud\Collection\Operation\ZipWithNextOperation;
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

	/** {@inheritDoc} */
	public function single()
	{
		$found = false;
		$result = null;

		foreach ($this as $v) {
			if ($found) {
				throw new NoSuchElementException('Collection contains more than one element');
			}

			$result = $v;
			$found = true;
		}

		if (!$found) {
			throw new NoSuchElementException('Collection is empty');
		}

		return $result; // @phpstan-ignore return.type
	}

	/** {@inheritDoc} */
	public function singleOrNull(): mixed
	{
		try {
			return $this->single();
		} catch (NoSuchElementException) {
			return null;
		}
	}

	/** {@inheritDoc} */
	public function find(Closure $predicate): mixed
	{
		foreach ($this as $i => $v) {
			if ($predicate($v, $i)) {
				return $v;
			}
		}

		return null;
	}

	/** {@inheritDoc} */
	public function findLast(Closure $predicate): mixed
	{
		$result = null;

		foreach ($this as $i => $v) {
			if ($predicate($v, $i)) {
				$result = $v;
			}
		}

		return $result;
	}

	/** {@inheritDoc} */
	public function expect(Closure $predicate)
	{
		foreach ($this as $i => $v) {
			if ($predicate($v, $i)) {
				return $v;
			}
		}

		throw new NoSuchElementException('No element matching the predicate was found');
	}

	/** {@inheritDoc} */
	public function expectLast(Closure $predicate)
	{
		$found = false;
		$result = null;

		foreach ($this as $i => $v) {
			if ($predicate($v, $i)) {
				$result = $v;
				$found = true;
			}
		}

		if (!$found) { // @phpstan-ignore booleanNot.alwaysTrue
			throw new NoSuchElementException('No element matching the predicate was found');
		}

		return $result; // @phpstan-ignore return.type
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
		foreach ($elements as $x) {
			if (!$this->contains($x)) {
				return false;
			}
		}

		return true;
	}

	/** {@inheritDoc} */
	public function all(Closure $predicate): bool
	{
		foreach ($this as $i => $v) {
			if (!$predicate($v, $i)) {
				return false;
			}
		}

		return true;
	}

	/** {@inheritDoc} */
	public function any(Closure $predicate): bool
	{
		foreach ($this as $i => $v) {
			if ($predicate($v, $i)) {
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
		foreach ($this as $i => $v) {
			if ($predicate($v, $i)) {
				$count++;
			}
		}

		return $count;
	}

	// --- Aggregation ---

	/** {@inheritDoc} */
	public function fold(mixed $initial, Closure $operation): mixed
	{
		$acc = $initial;
		foreach ($this as $v) {
			$acc = $operation($acc, $v);
		}
		return $acc;
	}

	/** {@inheritDoc} */
	public function reduce(Closure $operation): mixed
	{
		$first = true;
		$acc = null;

		foreach ($this as $v) {
			if ($first) {
				$acc = $v;
				$first = false;
			} else {
				$acc = $operation($acc, $v);
			}
		}

		if ($first) {
			throw new UnsupportedOperationException();
		}

		return $acc;
	}

	/** {@inheritDoc} */
	public function reduceOrNull(Closure $operation): mixed
	{
		try {
			return $this->reduce($operation);
		} catch (UnsupportedOperationException) {
			return null;
		}
	}

	/** {@inheritDoc} */
	// @phpstan-ignore conditionalType.subjectNotFound (E is concrete, not a template, in extending fixtures)
	public function sum(?Closure $selector = null): int|float
	{
		$sum = 0;
		foreach ($this as $i => $v) {
			$sum += $selector !== null ? $selector($v, $i) : $v; // @phpstan-ignore assignOp.invalid
		}

		return $sum;
	}

	/** {@inheritDoc} */
	public function avg(?Closure $selector = null): float
	{
		return $this->avgOrNull($selector) ?? throw new UnsupportedOperationException('Cannot compute average of empty collection');
	}

	/** {@inheritDoc} */
	public function avgOrNull(?Closure $selector = null): float|null
	{
		$sum = 0;
		$count = 0;
		foreach ($this as $i => $v) {
			$sum += $selector !== null ? $selector($v, $i) : $v; // @phpstan-ignore assignOp.invalid
			$count++;
		}

		return $count > 0 ? $sum / $count : null;
	}

	/** {@inheritDoc} */
	public function min(?Closure $selector = null): mixed
	{
		$minValue = null;
		$minElement = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector !== null ? $selector($v, $i) : $v;
			if (!$found || $value < $minValue) {
				$minValue = $value;
				$minElement = $v;
				$found = true;
			}
		}

		if (!$found) {
			throw new NoSuchElementException('Collection is empty');
		}

		return $minElement;
	}

	/** {@inheritDoc} */
	public function minOrNull(?Closure $selector = null): mixed
	{
		try {
			return $this->min($selector);
		} catch (NoSuchElementException) {
			return null;
		}
	}

	/** {@inheritDoc} */
	public function max(?Closure $selector = null): mixed
	{
		$maxValue = null;
		$maxElement = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector !== null ? $selector($v, $i) : $v;
			if (!$found || $value > $maxValue) {
				$maxValue = $value;
				$maxElement = $v;
				$found = true;
			}
		}

		if (!$found) {
			throw new NoSuchElementException('Collection is empty');
		}

		return $maxElement;
	}

	/** {@inheritDoc} */
	public function maxOrNull(?Closure $selector = null): mixed
	{
		try {
			return $this->max($selector);
		} catch (NoSuchElementException) {
			return null;
		}
	}

	/** {@inheritDoc} */
	public function minOf(Closure $selector): mixed
	{
		$minValue = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector($v, $i);
			if (!$found || $value < $minValue) {
				$minValue = $value;
				$found = true;
			}
		}

		if (!$found) {
			throw new NoSuchElementException('Collection is empty');
		}

		return $minValue;
	}

	/** {@inheritDoc} */
	public function minOfOrNull(Closure $selector): mixed
	{
		try {
			return $this->minOf($selector);
		} catch (NoSuchElementException) {
			return null;
		}
	}

	/** {@inheritDoc} */
	public function maxOf(Closure $selector): mixed
	{
		$maxValue = null;
		$found = false;

		foreach ($this as $i => $v) {
			$value = $selector($v, $i);
			if (!$found || $value > $maxValue) {
				$maxValue = $value;
				$found = true;
			}
		}

		if (!$found) {
			throw new NoSuchElementException('Collection is empty');
		}

		return $maxValue;
	}

	/** {@inheritDoc} */
	public function maxOfOrNull(Closure $selector): mixed
	{
		try {
			return $this->maxOf($selector);
		} catch (NoSuchElementException) {
			return null;
		}
	}

	/** {@inheritDoc} */
	public function joinToString(string $separator = ', ', string $prefix = '', string $postfix = '', int $limit = -1, string $truncated = '...', ?Closure $transform = null): string
	{
		$parts = [];
		$i = 0;
		foreach ($this as $v) {
			if ($limit >= 0 && $i >= $limit) {
				$parts[] = $truncated;
				break;
			}

			if ($transform !== null) {
				$parts[] = $transform($v, $i);
			} elseif (is_scalar($v) || $v === null || $v instanceof Stringable) {
				$parts[] = (string) $v;
			} else {
				throw new ConversionException(sprintf(
					'Value of type "%s" at index %d cannot be converted to string. Provide a $transform closure to resolve.',
					get_debug_type($v),
					$i
				));
			}

			$i++;
		}

		return $prefix . implode($separator, $parts) . $postfix;
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function countBy(Closure $keySelector): ImmutableMap
	{
		/** @var HashKeyValueStore<string|int|bool|float|object, int> $store */
		$store = HashKeyValueStore::empty();

		foreach ($this as $i => $element) {
			$key = $keySelector($element, $i);
			/** @var int $count */
			$count = $store->get($key) ?? 0;
			$store->put($key, $count + 1);
		}

		return $this->newMapOf($store); // @phpstan-ignore return.type
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
		return $this->newCollectionOf(new FilterOperation($this->store)->byValue(fn ($v) => $v instanceof $type));
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
		return $this->newCollectionOf(new TakeOperation($this->store)->first($n));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function dropFirst(int $n = 1): ImmutableCollection
	{
		return $this->newCollectionOf(new DropOperation($this->store)->first($n));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function takeLast(int $n = 1): ImmutableCollection
	{
		return $this->newCollectionOf(new TakeOperation($this->store)->last($n));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function dropLast(int $n = 1): ImmutableCollection
	{
		return $this->newCollectionOf(new DropOperation($this->store)->last($n));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): ImmutableCollection
	{
		$i = 0;
		return $this->newCollectionOf(new TakeOperation($this->store)->byPredicate(function ($v) use ($predicate, &$i) {
			return $predicate($v, $i++);
		}));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): ImmutableCollection
	{
		$i = 0;
		return $this->newCollectionOf(new DropOperation($this->store)->byPredicate(function ($v) use ($predicate, &$i) {
			return $predicate($v, $i++);
		}));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): ImmutableCollection
	{
		return $this->newCollectionOf(new TakeOperation($this->store)->lastByPredicate($predicate));
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): ImmutableCollection
	{
		return $this->newCollectionOf(new DropOperation($this->store)->lastByPredicate($predicate));
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
			$store->sort(static fn ($a, $b) => $selector($a) <=> $selector($b));
			return $this->newCollectionOf($store);
		}

		$arr = $this->store->toArray();
		usort($arr, static fn ($a, $b) => $selector($a) <=> $selector($b));
		return $this->newCollectionOf($arr);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): ImmutableCollection
	{
		if ($this->store instanceof ReadWriteElementStore) { // @phpstan-ignore instanceof.alwaysTrue
			$store = clone $this->store;
			$store->sort(static fn ($a, $b) => $selector($b) <=> $selector($a));
			return $this->newCollectionOf($store);
		}

		$arr = $this->store->toArray();
		usort($arr, static fn ($a, $b) => $selector($b) <=> $selector($a));
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
	public function forEach(Closure $action): static
	{
		foreach ($this as $i => $v) {
			$action($v, $i);
		}

		return $this;
	}

	// --- Conversion ---

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
			$chunks = new ChunkOperation($this->store)->ofSize($size);
			foreach ($chunks as $chunk) {
				yield $this->newListOf($chunk);
			}
		})());
	}

	/**
	 * {@inheritDoc}
	 *
	 * The params are typed `int` (not the interface's `positive-int`) so the
	 * defensive non-positive guard below stays a live runtime safety net.
	 *
	 * @param int $size
	 * @param int $step
	 */
	#[NoDiscard]
	public function windowed(int $size, int $step = 1, bool $partialWindows = false): ImmutableList
	{
		if ($size <= 0 || $step <= 0) {
			return $this->newListOf();
		}

		$arr = $this->store->toArray();
		$count = count($arr);

		return $this->newListOf((function () use ($arr, $count, $size, $step, $partialWindows) {
			for ($i = 0; $i < $count; $i += $step) {
				$windowSize = min($size, $count - $i);
				if (!$partialWindows && $windowSize < $size) {
					break;
				}

				yield $this->newListOf(array_slice($arr, $i, $windowSize));
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
		$first = [];
		$second = [];
		foreach ($this->store as $pair) {
			if (!is_array($pair)) {
				throw new UnsupportedOperationException('unzip() requires a collection of pairs (arrays with indices 0 and 1)');
			}

			$a = $pair[0] ?? null;
			$b = $pair[1] ?? null;

			if ($a === null && !array_key_exists(0, $pair) || $b === null && !array_key_exists(1, $pair)) {
				throw new UnsupportedOperationException('unzip() requires a collection of pairs (arrays with indices 0 and 1)');
			}

			$first[] = $a;
			$second[] = $b;
		}

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
		/** @var HashKeyValueStore<K, array<int, E|V>> $store */
		$store = HashKeyValueStore::empty();

		foreach ($this as $i => $v) {
			$k = $keySelector($v, $i);
			/** @var array<int, E|V> $bucket */
			$bucket = $store->get($k) ?? [];
			$bucket[] = $valueTransform !== null ? $valueTransform($v, $i) : $v;
			$store->put($k, $bucket);
		}

		foreach ($store as $k => $bucket) {
			$store->put($k, $this->newListOf($bucket)); // @phpstan-ignore argument.type
		}

		return $this->newMapOf($store); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template U
	 * @param iterable<U> $other
	 * @return ImmutableSet<E&U>
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
	 * Creates the result of an element-type-changing operation (map, flatMap, flatten).
	 *
	 * Kept separate from newCollectionOf so that concrete Logic traits can bypass a
	 * self-preserving newCollectionOf override: a transform result no longer holds
	 * elements of E and must not go through the subtype's constructor.
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
