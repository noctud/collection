<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set;

use Closure;
use Noctud\Collection\CollectionLogic;
use Noctud\Collection\Operation\DistinctOperation;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Operation\DropOperation;
use Noctud\Collection\Operation\FilterOperation;
use Noctud\Collection\Operation\FlatMapOperation;
use Noctud\Collection\Operation\FlattenOperation;
use Noctud\Collection\Map\HashMap\HashKeyValueStore;
use Noctud\Collection\Operation\MapKeyValueOperation;
use Noctud\Collection\Operation\SetOperation;
use Noctud\Collection\Operation\TakeOperation;
use NoDiscard;
use function Noctud\Collection\mutableSetOf;
use function Noctud\Collection\setOf;

/**
 * Shared logic for all Set implementations.
 *
 * Transformation methods (filter, map, sorted, etc.) always return ImmutableSet.
 * Both MutableSet and ImmutableSet use this trait, ensuring consistent behavior.
 *
 * @template E
 * @mixin Set<E>
 */
trait SetLogic
{
	/** @use CollectionLogic<E> */
	use CollectionLogic;

	// --- Transformation ---

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): ImmutableSet
	{
		$i = 0;
		return $this->newCollectionOf(new FilterOperation($this->store)->byValue(function ($v) use ($predicate, &$i) {
			return $predicate($v, $i++);
		}));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<(E is null ? never : E)>
	 */
	#[NoDiscard] // @phpstan-ignore conditionalType.subjectNotFound, conditionalType.alwaysFalse (in classes with a concrete or non-nullable E the conditional is already decided)
	public function filterNotNull(): ImmutableSet
	{
		return $this->newCollectionOf(new FilterOperation($this->store)->byValue(fn ($v) => $v !== null));
	}

	/** {@inheritDoc} */
	#[NoDiscard] // @phpstan-ignore missingType.generics
	public function filterInstanceOf(string $type): ImmutableSet
	{
		return $this->newCollectionOf(new FilterOperation($this->store)->byValue(fn ($v) => $v instanceof $type));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R
	 * @param Closure(E, int):R $transform
	 * @return ImmutableSet<R>
	 */
	#[NoDiscard]
	public function map(Closure $transform): ImmutableSet
	{
		return $this->newTransformedCollectionOf(new MapKeyValueOperation($this->store)->items(fn ($v, $k) => $transform($v, $k)));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R
	 * @param Closure(E, int):(R|null) $transform
	 * @return ImmutableSet<R>
	 */
	#[NoDiscard]
	public function mapNotNull(Closure $transform): ImmutableSet
	{
		return $this->newTransformedCollectionOf(new MapKeyValueOperation($this->store)->itemsNotNull(fn ($v, $k) => $transform($v, $k)));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R
	 * @param Closure(E, int):iterable<R> $transform
	 * @return ImmutableSet<R>
	 */
	#[NoDiscard]
	public function flatMap(Closure $transform): ImmutableSet
	{
		$i = 0;
		return $this->newTransformedCollectionOf(new FlatMapOperation($this->store)->items(function ($v) use ($transform, &$i) {
			return $transform($v, $i++);
		}));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<(E is iterable<mixed> ? value-of<E|array{}> : E)>
	 */
	#[NoDiscard] // @phpstan-ignore conditionalType.subjectNotFound, return.unresolvableType (in classes with a concrete E the conditional subject is already substituted and stays unevaluated)
	public function flatten(): ImmutableSet
	{
		return $this->newTransformedCollectionOf(new FlattenOperation($this->store)->items());
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): ImmutableSet
	{
		return $this->newCollectionOf(new TakeOperation($this->store)->first($n));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): ImmutableSet
	{
		return $this->newCollectionOf(new DropOperation($this->store)->first($n));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): ImmutableSet
	{
		return $this->newCollectionOf(new TakeOperation($this->store)->last($n));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): ImmutableSet
	{
		return $this->newCollectionOf(new DropOperation($this->store)->last($n));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): ImmutableSet
	{
		$i = 0;
		return $this->newCollectionOf(new TakeOperation($this->store)->byPredicate(function ($v) use ($predicate, &$i) {
			return $predicate($v, $i++);
		}));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): ImmutableSet
	{
		$i = 0;
		return $this->newCollectionOf(new DropOperation($this->store)->byPredicate(function ($v) use ($predicate, &$i) {
			return $predicate($v, $i++);
		}));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): ImmutableSet
	{
		return $this->newCollectionOf(new TakeOperation($this->store)->lastByPredicate($predicate));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): ImmutableSet
	{
		return $this->newCollectionOf(new DropOperation($this->store)->lastByPredicate($predicate));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function distinct(): ImmutableSet
	{
		return $this->toImmutable();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function distinctBy(Closure $selector): ImmutableSet
	{
		return $this->newCollectionOf(new DistinctOperation($this->store)->bySelector($selector));
	}

	// --- Ordering ---

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function sorted(): ImmutableSet
	{
		$arr = $this->store->toArray();
		sort($arr);
		return $this->newCollectionOf($arr);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function sortedDesc(): ImmutableSet
	{
		$arr = $this->store->toArray();
		rsort($arr);
		return $this->newCollectionOf($arr);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): ImmutableSet
	{
		$arr = $this->store->toArray();
		usort($arr, static fn ($a, $b) => $selector($a) <=> $selector($b));
		return $this->newCollectionOf($arr);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): ImmutableSet
	{
		$arr = $this->store->toArray();
		usort($arr, static fn ($a, $b) => $selector($b) <=> $selector($a));
		return $this->newCollectionOf($arr);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): ImmutableSet
	{
		$arr = $this->store->toArray();
		usort($arr, $comparator);
		return $this->newCollectionOf($arr);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function reversed(): ImmutableSet
	{
		return $this->newCollectionOf(array_reverse($this->store->toArray()));
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function shuffled(): ImmutableSet
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
	 * @return ImmutableMap<GK, ImmutableSet<E>>
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
			if ($valueTransform !== null) {
				$store->put($k, $this->newListOf($bucket)); // @phpstan-ignore argument.type
			} else {
				$store->put($k, $this->newCollectionOf($bucket)); // @phpstan-ignore argument.type
			}
		}

		return $this->newMapOf($store); // @phpstan-ignore return.type
	}

	// --- Set Operations ---

	/**
	 * {@inheritDoc}
	 *
	 * Routed through newCollectionOf (rather than the free setOf) so that subtypes
	 * which override the factory preserve their own type. Defaults to ImmutableSet.
	 *
	 * @template U
	 * @param iterable<U> $other
	 * @return ImmutableSet<E&U>
	 */
	#[NoDiscard]
	public function intersect(iterable $other): ImmutableSet
	{
		return $this->newCollectionOf(new SetOperation($this->store)->intersect($other));
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
		return $this->newCollectionOf(new SetOperation($this->store)->union($other));
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
		return $this->newCollectionOf(new SetOperation($this->store)->subtract($other));
	}

	// --- Conversion ---

	/**
	 * @return MutableSet<E>
	 */
	#[NoDiscard]
	public function toMutable(): MutableSet
	{
		return mutableSetOf($this->store);
	}

	/**
	 * @return ImmutableSet<E>
	 */
	#[NoDiscard]
	public function toImmutable(): ImmutableSet
	{
		if ($this instanceof ImmutableSet) {
			return $this;
		}

		return setOf($this->store); // @phpstan-ignore return.type, argument.templateType
	}

	// --- Factory Methods ---

	/**
	 * Creates a new immutable set from the given data.
	 *
	 * @template NE
	 * @param iterable<NE> $data
	 * @return ImmutableSet<NE>
	 */
	protected function newCollectionOf(iterable $data): ImmutableSet
	{
		return setOf($data);
	}

	/**
	 * Creates the result of an element-type-changing operation (map, flatMap, flatten).
	 *
	 * Not routed through newCollectionOf: a self-preserving subtype rebuilds itself
	 * there, and a transform result no longer holds elements of E — it must not go
	 * through the subtype's constructor.
	 *
	 * @template NE
	 * @param iterable<NE> $data
	 * @return ImmutableSet<NE>
	 */
	protected function newTransformedCollectionOf(iterable $data): ImmutableSet
	{
		return setOf($data);
	}
}
