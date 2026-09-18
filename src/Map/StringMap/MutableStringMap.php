<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\StringMap;

use Closure;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\KeyCollisionStrategy;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Map\MutableMapLogic;
use Noctud\Collection\Operation\FlipKeyValueOperation;
use Noctud\Collection\Operation\MapKeyValueOperation;
use NoDiscard;
use ReflectionClass;
use function Noctud\Collection\mapOf;
use function Noctud\Collection\mutableStringMapOf;
use function Noctud\Collection\stringMapOf;

/**
 * Mutable string-key map with optimized single-array storage.
 * Only accepts string keys; non-string keys throw InvalidKeyTypeException.
 * If the given data is Closure, the map will be lazily initialized when first accessed.
 *
 * The class is empty for easy extendability, if you want your own MutableStringMap,
 * use MutableMapLogic trait in your own class; this way you are not tied
 * to our class hierarchy (you can extend your own base class).
 *
 * @template V
 * @implements MutableMap<string,V>
 */
final class MutableStringMap implements MutableMap
{
	/** @use MutableMapLogic<string,V> */
	use MutableMapLogic;

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
		} elseif ($data instanceof self || $data instanceof ImmutableStringMap) {
			$this->store = clone $data->__internalCollectionStore(); // @phpstan-ignore assign.propertyType
		} else {
			$this->store = StringKeyValueStore::fromAssoc($data);
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
		return stringMapOf($this->store);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toMutable(): MutableMap
	{
		return mutableStringMapOf($this->store);
	}
}
