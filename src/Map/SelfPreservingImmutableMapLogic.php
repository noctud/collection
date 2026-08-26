<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

use Closure;
use NoDiscard;

/**
 * Self-preserving variant of {@see ImmutableMapLogic}.
 *
 * Same behaviour as {@see ImmutableMapLogic}, but every shape-preserving operation
 * (filter, sorting, slicing, immutable mutations, ...) is narrowed to return
 * `static` — your own subtype — instead of the base `ImmutableMap`. This lets a
 * domain map expose its own type through the fluent API without a baseline:
 *
 * ```php
 * class Headers implements ImmutableMap
 * {
 *     use SelfPreservingImmutableMapLogic;
 *
 *     public function withoutHopByHop(): self
 *     {
 *         return $this->filterKeys(static fn (string $name) => !HopByHop::has($name));
 *     }
 * }
 * ```
 *
 * The using class needs a constructor that accepts an `iterable<K,V>` (the bundled
 * `newMapOf()` builds derived maps with `new static(...)`), e.g. by populating its
 * store with `HashKeyValueStore::fromAssoc($data)`.
 *
 * Two consequences of the `static` promise, both intentional:
 * - Mutations are **strict**: unlike the widening base `put(NK,NV): ImmutableMap<K|NK,V|NV>`,
 *   here `put(K,V): static`. A fixed-type map cannot widen its key/value type.
 * - Key/value-changing methods (mapKeys, mapValues, mapValuesNotNull,
 *   filterValuesInstanceOf, flip, the keys/values/entries views, sortedWith over
 *   MapEntry, and the to* conversions) are **not** narrowed; they still return the
 *   base type, because their result is no longer a map of `K => V`. Transforms that
 *   produce new keys or values (mapKeys, mapValues, mapValuesNotNull, flip) also
 *   build a plain base map at runtime, so a constructor invariant on the subtype
 *   never sees transformed entries.
 *
 * Each override delegates to the base implementation; the per-method return-type
 * suppression is sound because `newMapOf()` returns `new static(...)` at runtime.
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @implements ImmutableMap<K,V>
 */
trait SelfPreservingImmutableMapLogic
{
	// --- Self-Preserving Start (auto-generated) ---

	/** @use ImmutableMapLogic<K,V> */
	use ImmutableMapLogic {
		put as private putImpl;
		putIfAbsent as private putIfAbsentImpl;
		putFirst as private putFirstImpl;
		putAll as private putAllImpl;
		putAllPairs as private putAllPairsImpl;
		remove as private removeImpl;
		removeFirst as private removeFirstImpl;
		removeLast as private removeLastImpl;
		removeIf as private removeIfImpl;
		removeIfKey as private removeIfKeyImpl;
		removeIfValue as private removeIfValueImpl;
		removeNullValues as private removeNullValuesImpl;
		filter as private filterImpl;
		filterKeys as private filterKeysImpl;
		filterValues as private filterValuesImpl;
		filterValuesNotNull as private filterValuesNotNullImpl;
		sortedByKey as private sortedByKeyImpl;
		sortedByKeyDesc as private sortedByKeyDescImpl;
		sortedByValue as private sortedByValueImpl;
		sortedByValueDesc as private sortedByValueDescImpl;
		sortedBy as private sortedByImpl;
		sortedByDesc as private sortedByDescImpl;
		sortedWithKey as private sortedWithKeyImpl;
		sortedWithValue as private sortedWithValueImpl;
		reversed as private reversedImpl;
		shuffled as private shuffledImpl;
		takeFirst as private takeFirstImpl;
		takeLast as private takeLastImpl;
		dropFirst as private dropFirstImpl;
		dropLast as private dropLastImpl;
		takeWhile as private takeWhileImpl;
		dropWhile as private dropWhileImpl;
		takeLastWhile as private takeLastWhileImpl;
		dropLastWhile as private dropLastWhileImpl;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param K $key
	 * @param V $value
	 * @return static
	 */
	#[NoDiscard]
	public function put(string|int|bool|float|object $key, mixed $value): static
	{
		return $this->putImpl($key, $value); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param K $key
	 * @param V $value
	 * @return static
	 */
	#[NoDiscard]
	public function putIfAbsent(string|int|bool|float|object $key, mixed $value): static
	{
		return $this->putIfAbsentImpl($key, $value); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param K $key
	 * @param V $value
	 * @return static
	 */
	#[NoDiscard]
	public function putFirst(string|int|bool|float|object $key, mixed $value): static
	{
		return $this->putFirstImpl($key, $value); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param iterable<K,V> $data
	 * @return static
	 */
	#[NoDiscard]
	public function putAll(iterable $data): static
	{
		return $this->putAllImpl($data); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param iterable<array{0:K,1:V}> $data
	 * @return static
	 */
	#[NoDiscard]
	public function putAllPairs(iterable $data): static
	{
		return $this->putAllPairsImpl($data); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return static
	 */
	#[NoDiscard]
	public function remove(string|int|bool|float|object $key): static
	{
		return $this->removeImpl($key); // @phpstan-ignore return.type
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
	 * @param Closure(V, K):bool $predicate
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
	 * @param Closure(K):bool $predicate
	 * @return static
	 */
	#[NoDiscard]
	public function removeIfKey(Closure $predicate): static
	{
		return $this->removeIfKeyImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(V):bool $predicate
	 * @return static
	 */
	#[NoDiscard]
	public function removeIfValue(Closure $predicate): static
	{
		return $this->removeIfValueImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return static
	 */
	#[NoDiscard]
	public function removeNullValues(): static
	{
		return $this->removeNullValuesImpl(); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(V, K):bool $predicate
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
	 * @param Closure(K):bool $predicate
	 * @return static
	 */
	#[NoDiscard]
	public function filterKeys(Closure $predicate): static
	{
		return $this->filterKeysImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(V):bool $predicate
	 * @return static
	 */
	#[NoDiscard]
	public function filterValues(Closure $predicate): static
	{
		return $this->filterValuesImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return static
	 */
	#[NoDiscard]
	public function filterValuesNotNull(): static
	{
		return $this->filterValuesNotNullImpl(); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R of mixed
	 * @param Closure(K):R|null $selector Selector to extract comparable from the key.
	 * @return static
	 */
	#[NoDiscard]
	public function sortedByKey(?Closure $selector = null): static
	{
		return $this->sortedByKeyImpl($selector); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R of mixed
	 * @param Closure(K):R|null $selector
	 * @return static
	 */
	#[NoDiscard]
	public function sortedByKeyDesc(?Closure $selector = null): static
	{
		return $this->sortedByKeyDescImpl($selector); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R of mixed
	 * @param Closure(V):R|null $selector Selector to extract comparable from the value.
	 * @return static
	 */
	#[NoDiscard]
	public function sortedByValue(?Closure $selector = null): static
	{
		return $this->sortedByValueImpl($selector); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R of mixed
	 * @param Closure(V):R|null $selector
	 * @return static
	 */
	#[NoDiscard]
	public function sortedByValueDesc(?Closure $selector = null): static
	{
		return $this->sortedByValueDescImpl($selector); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template R of mixed
	 * @param Closure(V, K):R $selector Selector to extract comparable from the pair.
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
	 * @param Closure(V, K):R $selector Selector to extract comparable from the pair.
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
	 * @param Closure(K, K):int $comparator
	 * @return static
	 */
	#[NoDiscard]
	public function sortedWithKey(Closure $comparator): static
	{
		return $this->sortedWithKeyImpl($comparator); // @phpstan-ignore return.type
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Closure(V, V):int $comparator
	 * @return static
	 */
	#[NoDiscard]
	public function sortedWithValue(Closure $comparator): static
	{
		return $this->sortedWithValueImpl($comparator); // @phpstan-ignore return.type
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
	 * @param int $n Number of entries to take.
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
	 * @param int $n Number of entries to take.
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
	 * @param int $n Number of entries to drop.
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
	 * @param int $n Number of entries to drop.
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
	 * @param Closure(V, K):bool $predicate
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
	 * @param Closure(V, K):bool $predicate
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
	 * @param Closure(V, K):bool $predicate
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
	 * @param Closure(V, K):bool $predicate
	 * @return static
	 */
	#[NoDiscard]
	public function dropLastWhile(Closure $predicate): static
	{
		return $this->dropLastWhileImpl($predicate); // @phpstan-ignore return.type
	}

	/**
	 * Builds derived instances as the using subtype.
	 *
	 * @param iterable<K,V> $data
	 * @return static
	 */
	protected function newMapOf(iterable $data): ImmutableMap
	{
		return new static($data); // @phpstan-ignore return.type
	}

	// --- Self-Preserving End (auto-generated) ---
}
