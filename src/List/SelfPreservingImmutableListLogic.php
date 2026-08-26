<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\List;

use Closure;
use NoDiscard;

/**
 * Self-preserving variant of {@see ImmutableListLogic}.
 *
 * Same behaviour as {@see ImmutableListLogic}, but every shape-preserving operation
 * (filter, sorted, slice, immutable mutations, ...) is narrowed to return `static` —
 * your own subtype — instead of the base `ImmutableList`. This lets a domain list
 * expose its own type through the fluent API without a baseline:
 *
 * ```php
 * class LineItems implements ImmutableList
 * {
 *     use SelfPreservingImmutableListLogic;
 *
 *     public function billable(): self
 *     {
 *         return $this->filter(static fn (LineItem $i) => $i->isBillable());
 *     }
 * }
 * ```
 *
 * The using class only needs a constructor that accepts an `iterable` — the bundled
 * `newCollectionOf()` builds derived lists with `new static(...)`, so there is no
 * factory method to override.
 *
 * Two consequences of the `static` promise, both intentional:
 * - Mutations are **strict**: unlike the widening base `add(NE): ImmutableList<E|NE>`,
 *   here `add(E): static`. A fixed-type list cannot widen its element type.
 * - Type-changing methods (map, flatMap, flatten, filterInstanceOf, groupBy, the
 *   set operations intersect/union/subtract — which produce a Set — and the to*
 *   conversions) are **not** narrowed; they still return the base type, because
 *   their result is no longer a list of `E`. Transforms that produce new element
 *   values (map, mapNotNull, flatMap, flatten) also build a plain base list at
 *   runtime, so a constructor invariant on the subtype never sees transformed
 *   elements.
 *
 * Each override delegates to the base implementation; the per-method return-type
 * suppression is sound because `newCollectionOf()` returns `new static(...)` at runtime.
 *
 * @template E
 * @implements ImmutableList<E>
 */
trait SelfPreservingImmutableListLogic
{
	// --- Self-Preserving Start (auto-generated) ---

	/** @use ImmutableListLogic<E> */
	use ImmutableListLogic {
		set as private setImpl;
		removeEvery as private removeEveryImpl;
		removeAt as private removeAtImpl;
		slice as private sliceImpl;
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
		partition as private partitionImpl;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param int $index The index at which to set the element
	 * @param E $element The element to set
	 * @return static
	 */
	#[NoDiscard]
	public function set(int $index, mixed $element): static
	{
		return $this->setImpl($index, $element); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param E $element The element to remove
	 * @return static
	 */
	#[NoDiscard]
	public function removeEvery(mixed $element): static
	{
		return $this->removeEveryImpl($element); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param int $index The index of the element to remove
	 * @return static
	 */
	#[NoDiscard]
	public function removeAt(int $index): static
	{
		return $this->removeAtImpl($index); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param non-negative-int $from Starting index (inclusive)
	 * @param non-negative-int $to Ending index (exclusive)
	 * @return static
	 */
	#[NoDiscard]
	public function slice(int $from, int $to): static
	{
		return $this->sliceImpl($from, $to); // @phpstan-ignore return.type
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
	protected function newCollectionOf(iterable $data): ImmutableList
	{
		return new static($data); // @phpstan-ignore return.type
	}

	// --- Self-Preserving End (auto-generated) ---
}
