<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set;

use Closure;
use NoDiscard;

/**
 * Self-preserving variant of {@see ImmutableSetLogic}.
 *
 * Same behaviour as {@see ImmutableSetLogic}, but every shape-preserving operation
 * (filter, sorted, set operations, immutable mutations, ...) is narrowed to return
 * `static` — i.e. your own subtype — instead of the base `ImmutableSet`. This lets a
 * domain collection expose its own type through the fluent API without a baseline:
 *
 * ```php
 * class OrderItemCollection implements ImmutableSet
 * {
 *     use SelfPreservingImmutableSetLogic;
 *
 *     public function onlySwapped(): self
 *     {
 *         return $this->filter(static fn (OrderItem $i) => $i->isSwapped());
 *     }
 * }
 * ```
 *
 * The using class only needs a constructor that accepts an `iterable` — the bundled
 * `newCollectionOf()` builds derived collections with `new static(...)`, so there is
 * no factory method to override.
 *
 * Three consequences of the `static` promise, all intentional:
 * - Mutations are **strict**: unlike the widening base `add(NE): ImmutableSet<E|NE>`,
 *   here `add(E): static`. A fixed-type collection cannot widen its element type.
 * - `union()` is **strict** for the same reason: unlike the widening base
 *   `union(iterable<NE>): Set<E|NE>`, here `union(iterable<E>): static`.
 * - Type-changing methods (map, flatMap, flatten, filterInstanceOf, groupBy, the
 *   to* conversions) are **not** narrowed — they still return the base type, because
 *   their result is no longer a collection of `E`. (`groupBy` additionally cannot be
 *   narrowed because `ImmutableMap`'s value parameter is invariant.) Transforms that
 *   produce new element values (map, mapNotNull, flatMap, flatten) also build a plain
 *   base set at runtime, so a constructor invariant on the subtype never sees
 *   transformed elements.
 *
 * Each override delegates to the base implementation; the per-method return-type
 * suppression is sound because `newCollectionOf()` returns `new static(...)` at runtime.
 *
 * @template E
 * @implements ImmutableSet<E>
 */
trait SelfPreservingImmutableSetLogic
{
	// --- Self-Preserving Start (auto-generated) ---

	/** @use ImmutableSetLogic<E> */
	use ImmutableSetLogic {
		add as private addImpl;
		addFirst as private addFirstImpl;
		addAll as private addAllImpl;
		removeIf as private removeIfImpl;
		removeAll as private removeAllImpl;
		removeElement as private removeElementImpl;
		removeFirst as private removeFirstImpl;
		removeLast as private removeLastImpl;
		retainAll as private retainAllImpl;
		filter as private filterImpl;
		filterNotNull as private filterNotNullImpl;
		takeFirst as private takeFirstImpl;
		dropFirst as private dropFirstImpl;
		takeLast as private takeLastImpl;
		dropLast as private dropLastImpl;
		takeWhile as private takeWhileImpl;
		dropWhile as private dropWhileImpl;
		takeLastWhile as private takeLastWhileImpl;
		dropLastWhile as private dropLastWhileImpl;
		distinct as private distinctImpl;
		distinctBy as private distinctByImpl;
		sorted as private sortedImpl;
		sortedDesc as private sortedDescImpl;
		sortedBy as private sortedByImpl;
		sortedByDesc as private sortedByDescImpl;
		sortedWith as private sortedWithImpl;
		reversed as private reversedImpl;
		shuffled as private shuffledImpl;
		intersect as private intersectImpl;
		union as private unionImpl;
		subtract as private subtractImpl;
		partition as private partitionImpl;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param E $element The element to add
	 * @return static
	 */
	#[NoDiscard]
	public function add(mixed $element): static
	{
		return $this->addImpl($element); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param E $element The element to add
	 * @return static
	 */
	#[NoDiscard]
	public function addFirst(mixed $element): static
	{
		return $this->addFirstImpl($element); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param iterable<E> $elements The elements to add
	 * @return static
	 */
	#[NoDiscard]
	public function addAll(iterable $elements): static
	{
		return $this->addAllImpl($elements); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return static
	 */
	#[NoDiscard]
	public function removeIf(Closure $predicate): static
	{
		return $this->removeIfImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param iterable<E> $elements The elements to remove
	 * @return static
	 */
	#[NoDiscard]
	public function removeAll(iterable $elements): static
	{
		return $this->removeAllImpl($elements); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param E $element The element to remove
	 * @return static
	 */
	#[NoDiscard]
	public function removeElement(mixed $element): static
	{
		return $this->removeElementImpl($element); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return static
	 */
	#[NoDiscard]
	public function removeFirst(): static
	{
		return $this->removeFirstImpl(); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return static
	 */
	#[NoDiscard]
	public function removeLast(): static
	{
		return $this->removeLastImpl(); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param iterable<E> $elements The elements to retain
	 * @return static
	 */
	#[NoDiscard]
	public function retainAll(iterable $elements): static
	{
		return $this->retainAllImpl($elements); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return static
	 */
	#[NoDiscard]
	public function filter(Closure $predicate): static
	{
		return $this->filterImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return static
	 */
	#[NoDiscard]
	public function filterNotNull(): static
	{
		return $this->filterNotNullImpl(); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param non-negative-int $n
	 * @return static
	 */
	#[NoDiscard]
	public function takeFirst(int $n = 1): static
	{
		return $this->takeFirstImpl($n); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param non-negative-int $n
	 * @return static
	 */
	#[NoDiscard]
	public function dropFirst(int $n = 1): static
	{
		return $this->dropFirstImpl($n); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param non-negative-int $n
	 * @return static
	 */
	#[NoDiscard]
	public function takeLast(int $n = 1): static
	{
		return $this->takeLastImpl($n); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param non-negative-int $n
	 * @return static
	 */
	#[NoDiscard]
	public function dropLast(int $n = 1): static
	{
		return $this->dropLastImpl($n); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return static
	 */
	#[NoDiscard]
	public function takeWhile(Closure $predicate): static
	{
		return $this->takeWhileImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return static
	 */
	#[NoDiscard]
	public function dropWhile(Closure $predicate): static
	{
		return $this->dropWhileImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return static
	 */
	#[NoDiscard]
	public function takeLastWhile(Closure $predicate): static
	{
		return $this->takeLastWhileImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return static
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): static
	{
		return $this->dropLastWhileImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return static
	 */
	#[NoDiscard]
	public function distinct(): static
	{
		return $this->distinctImpl(); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template K
	 * @param Closure(E, int):K $selector
	 * @return static
	 */
	#[NoDiscard]
	public function distinctBy(Closure $selector): static
	{
		return $this->distinctByImpl($selector); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return static
	 */
	#[NoDiscard]
	public function sorted(): static
	{
		return $this->sortedImpl(); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return static
	 */
	#[NoDiscard]
	public function sortedDesc(): static
	{
		return $this->sortedDescImpl(); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return static
	 */
	#[NoDiscard]
	public function sortedBy(Closure $selector): static
	{
		return $this->sortedByImpl($selector); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R of mixed
	 * @param Closure(E):R $selector Selector to extract comparable from the element.
	 * @return static
	 */
	#[NoDiscard]
	public function sortedByDesc(Closure $selector): static
	{
		return $this->sortedByDescImpl($selector); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(E, E):int $comparator
	 * @return static
	 */
	#[NoDiscard]
	public function sortedWith(Closure $comparator): static
	{
		return $this->sortedWithImpl($comparator); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return static
	 */
	#[NoDiscard]
	public function reversed(): static
	{
		return $this->reversedImpl(); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return static
	 */
	#[NoDiscard]
	public function shuffled(): static
	{
		return $this->shuffledImpl(); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template V
	 * @param iterable<V> $other
	 * @return static
	 */
	#[NoDiscard]
	public function intersect(iterable $other): static
	{
		return $this->intersectImpl($other); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param iterable<E> $other
	 * @return static
	 */
	#[NoDiscard]
	public function union(iterable $other): static
	{
		return $this->unionImpl($other); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param iterable<mixed> $other
	 * @return static
	 */
	#[NoDiscard]
	public function subtract(iterable $other): static
	{
		return $this->subtractImpl($other); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(E, int):bool $predicate
	 * @return array{static, static}
	 */
	#[NoDiscard]
	public function partition(Closure $predicate): array
	{
		return $this->partitionImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * Builds derived instances as the using subtype.
	 *
	 * @param iterable<E> $data
	 * @return static
	 */
	protected function newCollectionOf(iterable $data): ImmutableSet
	{
		return new static($data); // @phpstan-ignore return.type
	}

	// --- Self-Preserving End (auto-generated) ---
}
