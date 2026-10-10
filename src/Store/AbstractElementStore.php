<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Store;

use Noctud\Collection\Exception\NoSuchElementException;

/**
 * Base class for collection stores.
 *
 * @internal
 * @template V
 * @implements ReadWriteElementStore<V>
 */
abstract class AbstractElementStore implements ReadWriteElementStore
{
	// --- Properties ---

	/** @var array<int|string,V> */
	protected array $elements = [];

	// --- Element Access ---

	public function first(bool $throw = false): mixed
	{
		if ($this->isEmpty()) {
			if (!$throw) {
				return null;
			}

			throw new NoSuchElementException();
		}

		return $this->elements[array_key_first($this->elements)];
	}

	public function last(bool $throw = false): mixed
	{
		if ($this->isEmpty()) {
			if (!$throw) {
				return null;
			}

			throw new NoSuchElementException();
		}

		return $this->elements[array_key_last($this->elements)];
	}

	public function random(bool $throw = false): mixed
	{
		if ($this->isEmpty()) {
			if (!$throw) {
				return null;
			}

			throw new NoSuchElementException();
		}

		return $this->elements[array_rand($this->elements)];
	}

	// --- Querying ---

	abstract public function contains(mixed $element): bool;

	public function isEmpty(): bool
	{
		return $this->elements === [];
	}

	public function count(): int
	{
		return count($this->elements);
	}

	// --- Mutation ---

	/**
	 * Adds all elements from source. Must be implemented by subclasses.
	 *
	 * @param iterable<V> $source
	 */
	abstract public function addAll(iterable $source): void;

	public function removeFirst(): void
	{
		if (!$this->isEmpty()) {
			array_shift($this->elements);
		}
	}

	public function removeLast(): void
	{
		if (!$this->isEmpty()) {
			array_pop($this->elements);
		}
	}

	public function clear(): void
	{
		$this->elements = [];
	}

	public function removeIf(callable $predicate): void
	{
		$this->elements = array_values(array_filter($this->elements, static fn ($element) => !$predicate($element)));
	}

	// --- Ordering ---

	public function sort(?callable $comparator = null): void
	{
		if ($comparator !== null) {
			usort($this->elements, $comparator);
		} else {
			sort($this->elements);
		}
	}

	public function sortBy(callable $selector, bool $descending = false): void
	{
		$this->elements = array_values(self::orderedBy($this->elements, $selector, $descending));
	}

	/**
	 * Orders the elements by the value the selector returns for each of them, keeping their keys.
	 *
	 * The selector runs once per element and asort() compares the selected values in C: a usort()
	 * comparator calling the selector would run it twice per comparison. asort() compares like <=>
	 * and keeps equal values in their order, as usort() does.
	 *
	 * @template T
	 * @template TKey of array-key
	 * @param array<TKey,T> $elements
	 * @param callable(T):mixed $selector
	 * @return array<TKey,T>
	 */
	public static function orderedBy(array $elements, callable $selector, bool $descending = false): array
	{
		$selected = array_map($selector, $elements);
		if ($descending) {
			arsort($selected);
		} else {
			asort($selected);
		}

		return array_replace($selected, $elements);
	}

	public function reverse(): void
	{
		$this->elements = array_reverse($this->elements);
	}

	public function shuffle(): void
	{
		shuffle($this->elements);
	}
}
