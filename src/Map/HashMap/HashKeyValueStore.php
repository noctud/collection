<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\HashMap;

use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\Exception\NoSuchElementException;
use Noctud\Collection\KeyHasher;
use Noctud\Collection\Map\KeyCollisionStrategy;
use Noctud\Collection\Map\SimpleMapEntry;
use Noctud\Collection\Store\KeyValueStore;
use Traversable;

/**
 * Internal dual-array entry storage for maps with normalized keys.
 * Maintains two arrays: $values[normalizedKey] = value and $keys[normalizedKey] = originalKey
 * to prevent PHP's auto-casting of keys.
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @implements KeyValueStore<K,V>
 */
final class HashKeyValueStore implements KeyValueStore
{
	/** @var array<int|string,K> */
	private array $keys = [];

	/** @var array<int|string,V> */
	private array $values = [];

	private function __construct()
	{
	}

	/**
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 * @param iterable<NK,NV> $source
	 * @return self<NK,NV>
	 */
	public static function fromAssoc(iterable $source): self
	{
		/** @var self<NK,NV> $store */
		$store = new self();
		foreach ($source as $k => $v) {
			$nk = KeyHasher::hashMapKey($k);
			$store->values[$nk] = $v;
			$store->keys[$nk] = $k;
		}
		return $store;
	}

	/**
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 * @param iterable<array{0:NK,1:NV}> $source
	 * @return self<NK,NV>
	 */
	public static function fromPairs(iterable $source): self
	{
		/** @var self<NK,NV> $store */
		$store = new self();
		foreach ($source as [$k, $v]) {
			$nk = KeyHasher::hashMapKey($k);
			$store->values[$nk] = $v;
			$store->keys[$nk] = $k;
		}
		return $store;
	}

	/**
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 * @return self<NK,NV>
	 */
	public static function empty(): self // @phpstan-ignore method.templateTypeNotInParameter, method.templateTypeNotInParameter
	{
		/** @var self<NK,NV> $store */
		$store = new self();

		return $store;
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator(): Traversable
	{
		$valueSnapshot = $this->values;

		foreach ($this->keys as $nk => $k) {
			yield $k => $valueSnapshot[$nk];
		}
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int
	{
		return count($this->values);
	}

	/**
	 * @inheritDoc
	 */
	public function isEmpty(): bool
	{
		return $this->values === [];
	}

	/**
	 * @inheritDoc
	 */
	public function containsKey(string|int|bool|float|object $key): bool
	{
		return array_key_exists(KeyHasher::hashMapKey($key), $this->values);
	}

	/**
	 * @inheritDoc
	 */
	public function contains(mixed $value): bool
	{
		return array_any($this->values, fn ($v) => $v === $value);
	}

	/**
	 * @inheritDoc
	 */
	public function get(string|int|bool|float|object $key, bool $throw = false): mixed
	{
		$hash = KeyHasher::hashMapKey($key);

		if (!array_key_exists($hash, $this->values)) {
			if (!$throw) {
				return null;
			}

			throw new NoSuchElementException('Key not found in map');
		}

		return $this->values[$hash];
	}

	/**
	 * @return ($throw is true ? SimpleMapEntry<K,V> : SimpleMapEntry<K,V>|null)
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

		$nk = array_key_first($this->values);
		return new SimpleMapEntry($this->keys[$nk], $this->values[$nk]);
	}

	/**
	 * @return ($throw is true ? SimpleMapEntry<K,V> : SimpleMapEntry<K,V>|null)
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

		$nk = array_key_last($this->values);
		return new SimpleMapEntry($this->keys[$nk], $this->values[$nk]);
	}

	/**
	 * @return ($throw is true ? SimpleMapEntry<K,V> : SimpleMapEntry<K,V>|null)
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

		$nk = array_rand($this->values);
		return new SimpleMapEntry($this->keys[$nk], $this->values[$nk]);
	}

	/**
	 * @inheritDoc
	 */
	public function put(string|int|bool|float|object $key, mixed $value): void
	{
		$nk = KeyHasher::hashMapKey($key);
		$this->values[$nk] = $value;
		$this->keys[$nk] = $key;
	}

	/**
	 * @inheritDoc
	 */
	public function putAllFromAssoc(iterable $source): void
	{
		foreach ($source as $k => $v) {
			$nk = KeyHasher::hashMapKey($k);
			$this->values[$nk] = $v; // @phpstan-ignore assign.propertyType
			$this->keys[$nk] = $k; // @phpstan-ignore assign.propertyType
		}
	}

	/**
	 * @inheritDoc
	 */
	public function putAllFromPairs(iterable $source): void
	{
		foreach ($source as [$k, $v]) {
			$nk = KeyHasher::hashMapKey($k);
			$this->values[$nk] = $v; // @phpstan-ignore assign.propertyType
			$this->keys[$nk] = $k; // @phpstan-ignore assign.propertyType
		}
	}

	/**
	 * @inheritDoc
	 */
	public function putFirst(string|int|bool|float|object $key, mixed $value): void
	{
		$nk = KeyHasher::hashMapKey($key);
		unset($this->values[$nk], $this->keys[$nk]);
		$this->values = [$nk => $value] + $this->values;
		$this->keys = [$nk => $key] + $this->keys;
	}

	/**
	 * @param K $key
	 */
	public function remove(string|int|bool|float|object $key): void
	{
		$nk = KeyHasher::hashMapKey($key);
		unset($this->values[$nk], $this->keys[$nk]);
	}

	public function removeFirst(): void
	{
		if (!$this->isEmpty()) {
			$nk = array_key_first($this->values);
			unset($this->values[$nk], $this->keys[$nk]);
		}
	}

	public function removeLast(): void
	{
		if (!$this->isEmpty()) {
			$nk = array_key_last($this->values);
			unset($this->values[$nk], $this->keys[$nk]);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function clear(): void
	{
		$this->values = [];
		$this->keys = [];
	}

	/** @inheritDoc */
	public function removeIf(callable $predicate): void
	{
		foreach ($this->keys as $hash => $key) {
			if ($predicate($this->values[$hash], $key)) {
				unset($this->keys[$hash], $this->values[$hash]);
			}
		}
	}

	/** @inheritDoc */
	public function removeIfKey(callable $predicate): void
	{
		foreach ($this->keys as $hash => $key) {
			if ($predicate($key)) {
				unset($this->keys[$hash], $this->values[$hash]);
			}
		}
	}

	/** @inheritDoc */
	public function removeIfValue(callable $predicate): void
	{
		foreach ($this->values as $hash => $value) {
			if ($predicate($value)) {
				unset($this->keys[$hash], $this->values[$hash]);
			}
		}
	}

	/** @inheritDoc */
	public function sortByPairs(callable $comparator): void
	{
		$pairs = $this->toPairs();
		usort($pairs, $comparator);
		$this->keys = [];
		$this->values = [];
		foreach ($pairs as [$k, $v]) {
			$nk = KeyHasher::hashMapKey($k);
			$this->keys[$nk] = $k;
			$this->values[$nk] = $v;
		}
	}

	/** @inheritDoc */
	public function reverse(): void
	{
		$this->keys = array_reverse($this->keys, true);
		$this->values = array_reverse($this->values, true);
	}

	/** @inheritDoc */
	public function shuffle(): void
	{
		$nks = array_keys($this->values);
		shuffle($nks);
		$keys = [];
		$values = [];
		foreach ($nks as $nk) {
			$keys[$nk] = $this->keys[$nk];
			$values[$nk] = $this->values[$nk];
		}
		$this->keys = $keys;
		$this->values = $values;
	}

	/**
	 * @return list<array{0:K,1:V}>
	 */
	public function toPairs(): array
	{
		$out = [];
		foreach ($this->values as $nk => $v) {
			$out[] = [$this->keys[$nk], $v];
		}

		return $out;
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): array
	{
		$out = [];
		foreach ($this->values as $nk => $v) {
			$realKey = $this->keys[$nk];

			if ($realKey === null) { // @phpstan-ignore identical.alwaysFalse
				$nativeKey = '';
			} elseif (is_scalar($realKey)) {
				if (is_float($realKey) && $realKey !== (float) (int) $realKey) {
					throw new ConversionException(sprintf(
						'Float key %s cannot be converted to array key without precision loss. Use mapKeys(fn($v, $k) => (string) $k)->toArray() or mapKeys(fn($v, $k) => (int) $k)->toArray() to resolve.',
						$realKey
					));
				}

				$nativeKey = is_bool($realKey) || is_float($realKey) ? (int) $realKey : $realKey;
			} else {
				throw new ConversionException(sprintf(
					'Object key of type "%s" cannot be converted to array key. Use mapKeys(fn($v, $k) => ...)->toArray() to resolve.',
					get_debug_type($realKey)
				));
			}

			if (array_key_exists($nativeKey, $out)) {
				if ($onCollision === KeyCollisionStrategy::Throw) {
					throw new ConversionException(sprintf(
						'Key collision detected during toArray conversion. Key "%s" collides with an existing key. Use toArray(fn($k) => ...) to resolve.',
						is_scalar($realKey) ? (string) $nativeKey : get_debug_type($realKey) // @phpstan-ignore function.alreadyNarrowedType
					));
				}

				if ($onCollision === KeyCollisionStrategy::KeepFirst) {
					continue;
				}
			}

			$out[$nativeKey] = $v;
		}

		return $out;
	}
}
