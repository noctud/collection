<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

use Noctud\Collection\KeyHasher;
use Noctud\Collection\Set\HashSet\HashElementStore;
use Noctud\Collection\Set\HashSet\ImmutableHashSet;
use Noctud\Collection\Set\HashSet\MutableHashSet;

/**
 * Set algebra on arrays keyed by KeyHasher::hashSetKey(): PHP intersects, subtracts and merges
 * those in C, and the resulting set is built from them without hashing anything twice.
 *
 * @internal
 * @template V
 * @extends AbstractOperation<int,V>
 */
final class SetOperation extends AbstractOperation
{
	/**
	 * @param iterable<mixed> $other
	 * @return HashElementStore<V>
	 */
	public function intersect(iterable $other): HashElementStore
	{
		return HashElementStore::fromHashed(array_intersect_key(self::hashed($this->data), self::hashed($other)));
	}

	/**
	 * @template U
	 * @param iterable<U> $other
	 * @return HashElementStore<V|U>
	 */
	public function union(iterable $other): HashElementStore
	{
		return HashElementStore::fromHashed(self::hashed($this->data) + self::hashed($other));
	}

	/**
	 * @param iterable<mixed> $other
	 * @return HashElementStore<V>
	 */
	public function subtract(iterable $other): HashElementStore
	{
		return HashElementStore::fromHashed(array_diff_key(self::hashed($this->data), self::hashed($other)));
	}

	/**
	 * Keys each element by its hash, keeping its first occurrence. A hash set already holds them so.
	 *
	 * @template T
	 * @param iterable<T> $data
	 * @return array<int|string,T>
	 */
	private static function hashed(iterable $data): array
	{
		if ($data instanceof ImmutableHashSet || $data instanceof MutableHashSet) {
			$data = $data->__internalCollectionStore();
		}

		if ($data instanceof HashElementStore) {
			return $data->toHashedArray();
		}

		$hashed = [];
		foreach ($data as $v) {
			$hash = KeyHasher::hashSetKey($v);
			if (!array_key_exists($hash, $hashed)) {
				$hashed[$hash] = $v;
			}
		}

		return $hashed;
	}
}
