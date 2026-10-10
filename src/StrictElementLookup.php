<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

use Closure;
use Noctud\Collection\Exception\InvalidKeyTypeException;
use Traversable;

/**
 * Strict (===) membership test against a list of elements.
 *
 * Scanning with in_array() costs O(elements) per lookup, which turns bulk operations like
 * removeAll() into O(n * m). Once enough lookups are expected, the elements are indexed by
 * KeyHasher::hashSetKey() instead: identical values share a hash, so comparing with === inside
 * a hash bucket gives the same answers in O(1) per lookup. Arrays are the exception, since
 * [0.0] === [-0.0] while their serializations differ: they all share one bucket.
 *
 * @internal
 */
final class StrictElementLookup
{
	/**
	 * Indexing hashes every element in PHP, while in_array() compares in C: the index only
	 * pays off when elements * lookups / (elements + lookups) exceeds this factor.
	 */
	private const int IndexingFactor = 128;

	/** @var array<int, mixed> */
	private array $elements;

	/** @var array<int|string, array<int, mixed>>|null */
	private ?array $buckets = null;

	private int $count;

	/**
	 * @param iterable<mixed> $elements
	 * @param int $expectedLookups How many times contains() or remove() is going to be called,
	 *                             PHP_INT_MAX when unknown
	 */
	public function __construct(iterable $elements, int $expectedLookups)
	{
		$this->elements = $elements instanceof Traversable ? iterator_to_array($elements, false) : array_values($elements);

		$this->count = $count = count($this->elements);
		if ($count * $expectedLookups > self::IndexingFactor * ($count + $expectedLookups)) {
			$this->buckets = self::index($this->elements);
		}
	}

	public function contains(mixed $element): bool
	{
		if ($this->buckets === null) {
			return in_array($element, $this->elements, true);
		}

		$hash = self::hashOrNull($element);
		if ($hash === null) {
			return false;
		}

		foreach ($this->buckets[$hash] ?? [] as $candidate) {
			if ($candidate === $element) {
				return true;
			}
		}

		return false;
	}

	/**
	 * The same test as contains(), or its negation, as a closure to hand to a filter: in the
	 * scanning mode it calls in_array() directly, sparing a method call per tested element.
	 *
	 * @return Closure(mixed): bool
	 */
	public function predicate(bool $negate = false): Closure
	{
		if ($this->buckets === null) {
			$elements = $this->elements;
			return $negate
				? static fn (mixed $v): bool => !in_array($v, $elements, true)
				: static fn (mixed $v): bool => in_array($v, $elements, true);
		}

		return $negate
			? fn (mixed $v): bool => !$this->contains($v)
			: $this->contains(...);
	}

	/**
	 * Removes every element identical to the given one.
	 */
	public function remove(mixed $element): void
	{
		if ($this->buckets === null) {
			foreach (array_keys($this->elements, $element, true) as $key) {
				unset($this->elements[$key]);
				$this->count--;
			}

			return;
		}

		$hash = self::hashOrNull($element);
		if ($hash === null || !isset($this->buckets[$hash])) {
			return;
		}

		foreach (array_keys($this->buckets[$hash], $element, true) as $key) {
			unset($this->buckets[$hash][$key]);
			$this->count--;
		}

		if ($this->buckets[$hash] === []) {
			unset($this->buckets[$hash]);
		}
	}

	public function isEmpty(): bool
	{
		return $this->count === 0;
	}

	/**
	 * Every indexed element could be hashed, so an element that cannot be is identical to none of them.
	 */
	private static function hashOrNull(mixed $element): int|string|null
	{
		try {
			return self::hash($element);
		} catch (InvalidKeyTypeException) {
			return null;
		}
	}

	/**
	 * @throws InvalidKeyTypeException
	 */
	private static function hash(mixed $element): int|string
	{
		return is_array($element) ? 'a' : KeyHasher::hashSetKey($element);
	}

	/**
	 * @param array<int, mixed> $elements
	 * @return array<int|string, array<int, mixed>>|null Null when an element cannot be hashed (e.g. a closed resource)
	 */
	private static function index(array $elements): ?array
	{
		$buckets = [];
		try {
			foreach ($elements as $element) {
				$buckets[self::hash($element)][] = $element;
			}
		} catch (InvalidKeyTypeException) {
			return null;
		}

		return $buckets;
	}
}
