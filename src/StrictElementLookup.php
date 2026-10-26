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
 * removeAll() into O(n * m). Once enough lookups have scanned them, the elements are indexed by
 * KeyHasher::hashSetKey() instead: identical values share a hash, so comparing with === inside
 * a hash bucket gives the same answers in O(1) per lookup. Arrays are the exception, since
 * [0.0] === [-0.0] while their serializations differ: they all share one bucket.
 *
 * @internal
 */
final class StrictElementLookup
{
	/**
	 * Indexing hashes every element in PHP, while in_array() compares in C, about this factor
	 * faster: the elements are indexed once that many lookups have scanned them, by then costing
	 * about as much as indexing them. Fewer elements than that are always scanned.
	 */
	private const int IndexingFactor = 128;

	/** @var array<int, mixed> */
	private array $elements;

	/** @var array<int|string, array<int, mixed>>|null */
	private ?array $buckets = null;

	private int $count;

	/** 0 once the elements are indexed, or are to be scanned for good. */
	private int $scansBeforeIndexing;

	/**
	 * @param iterable<mixed> $elements
	 */
	public function __construct(iterable $elements)
	{
		$this->elements = $elements instanceof Traversable ? iterator_to_array($elements, false) : array_values($elements);
		$this->count = count($this->elements);
		$this->scansBeforeIndexing = $this->count > self::IndexingFactor ? self::IndexingFactor : 0;
	}

	public function contains(mixed $element): bool
	{
		if ($this->scanning()) {
			return in_array($element, $this->elements, true);
		}

		$hash = self::hashOrNull($element);

		return $hash !== null && in_array($element, $this->buckets[$hash] ?? [], true);
	}

	/**
	 * The same test as contains(), or its negation, as a closure to hand to a filter: when the
	 * elements are scanned for good, it calls in_array() directly, sparing a method call per
	 * tested element.
	 *
	 * @return Closure(mixed): bool
	 */
	public function predicate(bool $negate = false): Closure
	{
		if ($this->buckets === null && $this->scansBeforeIndexing === 0) {
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
		if ($this->scanning()) {
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
	 * Counts one more lookup, indexing the elements when it is the one that makes it worth it.
	 */
	private function scanning(): bool
	{
		if ($this->buckets === null && $this->scansBeforeIndexing > 0 && --$this->scansBeforeIndexing === 0) {
			$this->buckets = self::index($this->elements);
		}

		return $this->buckets === null;
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
	 * An object is only identical to itself, so hashing its instance is enough: Hashable::identity()
	 * is user code, which must not start running once the elements get indexed.
	 *
	 * @throws InvalidKeyTypeException
	 */
	private static function hash(mixed $element): int|string
	{
		return match (true) {
			// Unlike a hash set, a bucket compares with ===, so 1 and '1' may share one.
			is_string($element), is_int($element) => $element,
			is_object($element) => 'o:' . spl_object_id($element),
			is_array($element) => 'a',
			default => KeyHasher::hashSetKey($element),
		};
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
