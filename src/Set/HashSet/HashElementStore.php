<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Set\HashSet;

use ArrayIterator;
use Noctud\Collection\KeyHasher;
use Noctud\Collection\Store\AbstractElementStore;
use Noctud\Collection\Store\ReadWriteElementStore;
use Traversable;

/**
 * Internal storage for HashSet.
 *
 * @template E
 * @extends AbstractElementStore<E>
 * @implements ReadWriteElementStore<E>
 */
final class HashElementStore extends AbstractElementStore implements ReadWriteElementStore
{
	/**
	 * @param iterable<E> $source
	 */
	public function __construct(iterable $source = [])
	{
		$this->addAll($source);
	}

	/**
	 * Builds the store from elements already keyed by KeyHasher::hashSetKey().
	 *
	 * @template NE
	 * @param array<int|string,NE> $elements
	 * @return self<NE>
	 */
	public static function fromHashed(array $elements): self
	{
		/** @var self<NE> $store */
		$store = new self();
		$store->elements = $elements;

		return $store;
	}

	/**
	 * Returns the elements keyed by KeyHasher::hashSetKey().
	 *
	 * @return array<int|string,E>
	 */
	public function toHashedArray(): array
	{
		return $this->elements;
	}

	public function addAll(iterable $source): void
	{
		foreach ($source as $element) {
			$this->elements[KeyHasher::hashSetKey($element)] = $element;
		}
	}

	/**
	 * @param E $element
	 */
	public function add(mixed $element): void
	{
		$this->elements[KeyHasher::hashSetKey($element)] = $element;
	}

	/**
	 * Adds the element to the beginning of the store.
	 * If the element already exists, it is moved to the first position.
	 *
	 * @param E $element
	 */
	public function addFirst(mixed $element): void
	{
		$hash = KeyHasher::hashSetKey($element);
		unset($this->elements[$hash]);
		$this->elements = [$hash => $element] + $this->elements;
	}

	/**
	 * @param E $element
	 */
	public function removeFirstOccurrence(mixed $element): void
	{
		unset($this->elements[KeyHasher::hashSetKey($element)]);
	}

	/**
	 * @param E $element
	 */
	public function contains(mixed $element): bool
	{
		return array_key_exists(KeyHasher::hashSetKey($element), $this->elements);
	}

	public function removeFirst(): void
	{
		if (!$this->isEmpty()) {
			unset($this->elements[array_key_first($this->elements)]);
		}
	}

	public function removeIf(callable $predicate): void
	{
		foreach ($this->elements as $hash => $element) {
			if ($predicate($element)) {
				unset($this->elements[$hash]);
			}
		}
	}

	public function sort(?callable $comparator = null): void
	{
		if ($comparator !== null) {
			uasort($this->elements, $comparator);
		} else {
			asort($this->elements);
		}
	}

	public function sortBy(callable $selector, bool $descending = false): void
	{
		$this->elements = self::orderedBy($this->elements, $selector, $descending);
	}

	public function reverse(): void
	{
		$this->elements = array_reverse($this->elements, true);
	}

	public function shuffle(): void
	{
		$keys = array_keys($this->elements);
		shuffle($keys);
		$shuffled = [];
		foreach ($keys as $k) {
			$shuffled[$k] = $this->elements[$k];
		}
		$this->elements = $shuffled;
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator(array_values($this->elements)); // @phpstan-ignore return.type
	}

	public function toArray(): array
	{
		return array_values($this->elements);
	}
}
