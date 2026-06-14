<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\IntMap;

use ArrayIterator;
use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\Map\SimpleMapEntry;
use Noctud\Collection\Map\KeyCollisionStrategy;
use Noctud\Collection\Store\KeyValueStore;
use Traversable;

/**
 * Internal single-array entry storage for int-key maps.
 * Uses native PHP int array keys directly.
 *
 * @template V
 * @implements KeyValueStore<int,V>
 */
final class IntKeyValueStore implements KeyValueStore
{
	/** @var array<int,V> */
	private array $data = [];

	/**
	 * @template NV
	 * @param iterable<int,NV> $source
	 * @return self<NV>
	 */
	public static function fromAssoc(iterable $source): self
	{
		/** @var self<NV> $store */
		$store = new self();
		if (is_array($source)) {
			if (array_is_list($source) || array_all($source, fn (mixed $v, int|string $k): bool => is_int($k))) { // @phpstan-ignore function.alreadyNarrowedType
				$store->data = $source;
			} else {
				throw new InvalidKeyTypeException('IntMap requires int keys, got string');
			}
		} else {
			foreach ($source as $k => $v) {
				if (!is_int($k)) { // @phpstan-ignore function.alreadyNarrowedType
					throw new InvalidKeyTypeException(sprintf('IntMap requires int keys, got %s', get_debug_type($k)));
				}
				$store->data[$k] = $v;
			}
		}
		return $store;
	}

	/**
	 * @return self<mixed>
	 */
	public static function empty(): self
	{
		return new self();
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->data);
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int
	{
		return count($this->data);
	}

	/**
	 * @inheritDoc
	 */
	public function isEmpty(): bool
	{
		return $this->data === [];
	}

	/**
	 * @inheritDoc
	 */
	public function containsKey(string|int|bool|float|object $key): bool
	{
		if (!is_int($key)) {
			return false;
		}

		return array_key_exists($key, $this->data);
	}

	/**
	 * @inheritDoc
	 */
	public function contains(mixed $value): bool
	{
		return array_any($this->data, fn ($v) => $v === $value);
	}

	/**
	 * @inheritDoc
	 */
	public function get(string|int|bool|float|object $key, bool $throw = false): mixed
	{
		if (!is_int($key)) {
			if (!$throw) {
				return null;
			}

			throw new NoSuchElementException('Key not found in map');
		}

		if (!array_key_exists($key, $this->data)) {
			if (!$throw) {
				return null;
			}

			throw new NoSuchElementException('Key not found in map');
		}

		return $this->data[$key];
	}

	/**
	 * @return ($throw is true ? SimpleMapEntry<int,V> : SimpleMapEntry<int,V>|null)
	 * @throws NoSuchElementException
	 */
	public function first(bool $throw = false): ?SimpleMapEntry
	{
		if ($this->isEmpty()) {
			if (!$throw) {
				return null;
			}

			throw new NoSuchElementException();
		}

		$key = array_key_first($this->data);
		assert($key !== null);
		return new SimpleMapEntry($key, $this->data[$key]);
	}

	/**
	 * @return ($throw is true ? SimpleMapEntry<int,V> : SimpleMapEntry<int,V>|null)
	 * @throws NoSuchElementException
	 */
	public function last(bool $throw = false): ?SimpleMapEntry
	{
		if ($this->isEmpty()) {
			if (!$throw) {
				return null;
			}

			throw new NoSuchElementException();
		}

		$key = array_key_last($this->data);
		assert($key !== null);
		return new SimpleMapEntry($key, $this->data[$key]);
	}

	/**
	 * @return ($throw is true ? SimpleMapEntry<int,V> : SimpleMapEntry<int,V>|null)
	 * @throws NoSuchElementException
	 */
	public function random(bool $throw = false): ?SimpleMapEntry
	{
		if ($this->isEmpty()) {
			if (!$throw) {
				return null;
			}

			throw new NoSuchElementException();
		}

		$key = array_rand($this->data);
		return new SimpleMapEntry($key, $this->data[$key]);
	}

	/**
	 * @inheritDoc
	 */
	public function put(string|int|bool|float|object $key, mixed $value): void
	{
		if (!is_int($key)) {
			throw new InvalidKeyTypeException(sprintf('IntMap requires int keys, got %s', get_debug_type($key)));
		}

		$this->data[$key] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function putAllFromAssoc(iterable $source): void
	{
		foreach ($source as $k => $v) {
			if (!is_int($k)) {
				throw new InvalidKeyTypeException(sprintf('IntMap requires int keys, got %s', get_debug_type($k)));
			}
			$this->data[$k] = $v; // @phpstan-ignore assign.propertyType
		}
	}

	/**
	 * @inheritDoc
	 */
	public function putAllFromPairs(iterable $source): void
	{
		foreach ($source as [$k, $v]) {
			if (!is_int($k)) {
				throw new InvalidKeyTypeException(sprintf('IntMap requires int keys, got %s', get_debug_type($k)));
			}
			$this->data[$k] = $v; // @phpstan-ignore assign.propertyType
		}
	}

	/**
	 * @inheritDoc
	 */
	public function putFirst(string|int|bool|float|object $key, mixed $value): void
	{
		if (!is_int($key)) {
			throw new InvalidKeyTypeException(sprintf('IntMap requires int keys, got %s', get_debug_type($key)));
		}

		unset($this->data[$key]);
		$this->data = [$key => $value] + $this->data;
	}

	/**
	 * @param int $key
	 */
	public function remove(string|int|bool|float|object $key): void
	{
		if (!is_int($key)) {
			return;
		}

		unset($this->data[$key]);
	}

	public function removeFirst(): void
	{
		if (!$this->isEmpty()) {
			$key = array_key_first($this->data);
			unset($this->data[$key]);
		}
	}

	public function removeLast(): void
	{
		if (!$this->isEmpty()) {
			$key = array_key_last($this->data);
			unset($this->data[$key]);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function clear(): void
	{
		$this->data = [];
	}

	/** @inheritDoc */
	public function removeIf(callable $predicate): void
	{
		foreach ($this->data as $key => $value) {
			if ($predicate($value, $key)) {
				unset($this->data[$key]);
			}
		}
	}

	/** @inheritDoc */
	public function removeIfKey(callable $predicate): void
	{
		foreach ($this->data as $key => $value) {
			if ($predicate($key)) {
				unset($this->data[$key]);
			}
		}
	}

	/** @inheritDoc */
	public function removeIfValue(callable $predicate): void
	{
		foreach ($this->data as $key => $value) {
			if ($predicate($value)) {
				unset($this->data[$key]);
			}
		}
	}

	/** @inheritDoc */
	public function sortByPairs(callable $comparator): void
	{
		$pairs = $this->toPairs();
		usort($pairs, $comparator);
		$this->data = [];
		foreach ($pairs as [$k, $v]) {
			$this->data[$k] = $v;
		}
	}

	/** @inheritDoc */
	public function reverse(): void
	{
		$this->data = array_reverse($this->data, true);
	}

	/** @inheritDoc */
	public function shuffle(): void
	{
		$keys = array_keys($this->data);
		shuffle($keys);
		$shuffled = [];
		foreach ($keys as $k) {
			$shuffled[$k] = $this->data[$k];
		}
		$this->data = $shuffled;
	}

	/**
	 * @return list<array{0:int,1:V}>
	 */
	public function toPairs(): array
	{
		$out = [];
		foreach ($this->data as $k => $v) {
			$out[] = [$k, $v];
		}

		return $out;
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): array
	{
		return $this->data;
	}
}
