<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\StringMap;

use Closure;
use Generator;
use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\ImmutableMapLogic;
use Noctud\Collection\Map\KeyCollisionStrategy;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Operation\FlipKeyValueOperation;
use Noctud\Collection\Operation\MapKeyValueOperation;
use NoDiscard;
use ReflectionClass;
use function Noctud\Collection\mapOf;
use function Noctud\Collection\mutableStringMapOf;
use function Noctud\Collection\stringMapOf;

/**
 * Immutable string-key map with optimized single-array storage.
 * Only accepts string keys; non-string keys throw InvalidKeyTypeException.
 * If the given data is Closure, the map will be lazily initialized when first accessed.
 *
 * The class is empty for easy extendability, if you want your own ImmutableStringMap,
 * use ImmutableMapLogic trait in your own class; this way you are not tied
 * to our class hierarchy (you can extend your own base class).
 *
 * @template V
 * @implements ImmutableMap<string,V>
 */
final class ImmutableStringMap implements ImmutableMap
{
	/** @use ImmutableMapLogic<string,V> */
	use ImmutableMapLogic;

	/**
	 * @param iterable<string|int,V>|Closure():iterable<string|int,V> $data
	 */
	public function __construct(iterable|Closure $data = [])
	{
		if ($data instanceof Closure) {
			$reflector = new ReflectionClass(StringKeyValueStore::class);
			$this->store = $reflector->newLazyProxy(fn (StringKeyValueStore $object): StringKeyValueStore => $object::fromAssoc($data()));
		} elseif ($data instanceof StringKeyValueStore) {
			$this->store = clone $data;
		} elseif ($data instanceof self || $data instanceof MutableStringMap) {
			$this->store = clone $data->__internalCollectionStore(); // @phpstan-ignore assign.propertyType
		} else {
			$this->store = StringKeyValueStore::fromAssoc($data);
		}
	}

	/**
	 * Puts a key-value pair. If the key is not a string, falls back to HashMap.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 * @param NK $key
	 * @param NV $value
	 * @return ImmutableMap<string|NK, V|NV>
	 */
	#[NoDiscard]
	public function put(string|int|bool|float|object $key, mixed $value): ImmutableMap
	{
		/** @var StringKeyValueStore<V> $entries */
		$entries = clone $this->store;

		try {
			$entries->put($key, $value);
			return $this->newMapOf($entries); // @phpstan-ignore return.type
		} catch (InvalidKeyTypeException) {
			return mapOf($this->store)->put($key, $value);
		}
	}

	/**
	 * Puts a key-value pair at the beginning. If the key is not a string, falls back to HashMap.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 * @param NK $key
	 * @param NV $value
	 * @return ImmutableMap<string|NK, V|NV>
	 */
	#[NoDiscard]
	public function putFirst(string|int|bool|float|object $key, mixed $value): ImmutableMap
	{
		/** @var StringKeyValueStore<V> $entries */
		$entries = clone $this->store;

		try {
			$entries->putFirst($key, $value);
			return $this->newMapOf($entries); // @phpstan-ignore return.type
		} catch (InvalidKeyTypeException) {
			return mapOf($this->store)->putFirst($key, $value);
		}
	}

	/**
	 * Puts all entries from an iterable. If any key is not a string, falls back to HashMap.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 * @param iterable<NK, NV> $data
	 * @return ImmutableMap<string|NK, V|NV>
	 */
	#[NoDiscard]
	public function putAll(iterable $data): ImmutableMap
	{
		// Buffer generators since they can't be rewound if an exception is thrown mid-iteration
		if ($data instanceof Generator) {
			$pairs = [];
			foreach ($data as $k => $v) {
				$pairs[] = [$k, $v];
			}

			return $this->putAllPairs($pairs);
		}

		/** @var StringKeyValueStore<V> $entries */
		$entries = clone $this->store;

		try {
			$entries->putAllFromAssoc($data);
			return $this->newMapOf($entries); // @phpstan-ignore return.type
		} catch (InvalidKeyTypeException) {
			return mapOf($this->store)->putAll($data);
		}
	}

	/**
	 * Puts all entries from key-value pairs. If any key is not a string, falls back to HashMap.
	 *
	 * @template NK of string|int|bool|float|object
	 * @template NV
	 * @param iterable<array{0: NK, 1: NV}> $data
	 * @return ImmutableMap<string|NK, V|NV>
	 */
	#[NoDiscard]
	public function putAllPairs(iterable $data): ImmutableMap
	{
		// Buffer generators since they can't be rewound if an exception is thrown mid-iteration
		if ($data instanceof Generator) {
			$data = iterator_to_array($data, false);
		}

		/** @var StringKeyValueStore<V> $entries */
		$entries = clone $this->store;

		try {
			$entries->putAllFromPairs($data);
			return $this->newMapOf($entries); // @phpstan-ignore return.type
		} catch (InvalidKeyTypeException) {
			return mapOf($this->store)->putAllPairs($data);
		}
	}

	/**
	 * Returns HashMap since transformed keys may be non-string.
	 *
	 * @template NK of string|int|bool|float|object
	 * @param Closure(V, string):NK $transform
	 * @return ImmutableMap<NK,V>
	 */
	#[NoDiscard]
	public function mapKeys(Closure $transform): ImmutableMap
	{
		return mapOf(new MapKeyValueOperation($this->store)->keys($transform));
	}

	/**
	 * Returns HashMap since values become keys and may be non-string.
	 *
	 * @return ImmutableMap<V,string>
	 */
	#[NoDiscard] // @phpstan-ignore generics.notSubtype
	public function flip(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): ImmutableMap
	{
		return mapOf(new FlipKeyValueOperation($this->store)->items($onCollision)); // @phpstan-ignore return.type, argument.type, argument.templateType
	}

	/**
	 * @template NV
	 * @param iterable<string,NV> $data
	 * @return ImmutableMap<string,NV>
	 */
	protected function newMapOf(iterable $data): ImmutableMap
	{
		return stringMapOf($data);
	}

	/**
	 * @template NV
	 * @param iterable<string,NV> $data
	 * @return ImmutableMap<string,NV>
	 */
	protected function newValueTransformedMapOf(iterable $data): ImmutableMap
	{
		return stringMapOf($data);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toImmutable(): ImmutableMap
	{
		return $this;
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toMutable(): MutableMap
	{
		return mutableStringMapOf($this->store);
	}
}
