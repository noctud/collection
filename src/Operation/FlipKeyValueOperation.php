<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Operation;

use Noctud\Collection\Exception\ConversionException;
use Noctud\Collection\KeyHasher;
use Noctud\Collection\Map\HashMap\HashKeyValueStore;
use Noctud\Collection\Map\KeyCollisionStrategy;

/**
 * @internal
 * @template K of string|int|bool|float|object
 * @template V
 * @extends AbstractKeyValueOperation<K,V>
 */
final class FlipKeyValueOperation extends AbstractKeyValueOperation
{
	/**
	 * @return HashKeyValueStore<V, K>
	 */
	public function items(KeyCollisionStrategy $strategy): HashKeyValueStore // @phpstan-ignore generics.notSubtype
	{
		// Each value is hashed once, straight into the arrays the store is built from: going
		// through containsKey() and put() hashed it twice, behind two method calls.
		$keys = [];
		$values = [];

		foreach ($this->data as $k => $v) {
			$hash = KeyHasher::hashMapKey($v); // throws InvalidKeyTypeException for unsupported values

			if ($strategy !== KeyCollisionStrategy::KeepLast && isset($keys[$hash])) {
				if ($strategy === KeyCollisionStrategy::Throw) {
					throw new ConversionException(sprintf(
						'Key collision detected during flip. Value "%s" appears multiple times and would cause a key collision.',
						is_scalar($v) ? (string) $v : get_debug_type($v)
					));
				}

				// KeepFirst: skip duplicate
				continue;
			}

			$keys[$hash] = $v;
			$values[$hash] = $k;
		}

		/** @var HashKeyValueStore<V, K> $store */
		$store = HashKeyValueStore::fromHashed($keys, $values); // @phpstan-ignore argument.type, argument.templateType, generics.notSubtype

		return $store;
	}
}
