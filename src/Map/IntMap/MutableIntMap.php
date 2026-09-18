<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\IntMap;

use Closure;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\KeyCollisionStrategy;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Map\MutableMapLogic;
use Noctud\Collection\Operation\FlipKeyValueOperation;
use Noctud\Collection\Operation\MapKeyValueOperation;
use NoDiscard;
use ReflectionClass;
use function Noctud\Collection\intMapOf;
use function Noctud\Collection\mapOf;
use function Noctud\Collection\mutableIntMapOf;

/**
 * Mutable int-key map with optimized single-array storage.
 * Only accepts int keys; non-int keys throw InvalidKeyTypeException.
 * If the given data is Closure, the map will be lazily initialized when first accessed.
 *
 * The class is empty for easy extendability, if you want your own MutableIntMap,
 * use MutableIntMapLogic trait in your own class; this way you are not tied
 * to our class hierarchy (you can extend your own base class).
 *
 * @template V
 * @implements MutableMap<int,V>
 */
final class MutableIntMap implements MutableMap
{
	/** @use MutableMapLogic<int,V> */
	use MutableMapLogic;

	/**
	 * @param iterable<int,V>|Closure():iterable<int,V> $data
	 */
	public function __construct(iterable|Closure $data = [])
	{
		if ($data instanceof Closure) {
			$reflector = new ReflectionClass(IntKeyValueStore::class);
			$this->store = $reflector->newLazyProxy(fn (IntKeyValueStore $object): IntKeyValueStore => $object::fromAssoc($data()));
		} elseif ($data instanceof IntKeyValueStore) {
			$this->store = clone $data;
		} elseif ($data instanceof self || $data instanceof ImmutableIntMap) {
			$this->store = clone $data->__internalCollectionStore(); // @phpstan-ignore assign.propertyType
		} else {
			$this->store = IntKeyValueStore::fromAssoc($data);
		}
	}

	/**
	 * Returns HashMap since transformed keys may be non-int.
	 *
	 * @template NK of string|int|bool|float|object
	 * @param Closure(V, int):NK $transform
	 * @return ImmutableMap<NK,V>
	 */
	#[NoDiscard]
	public function mapKeys(Closure $transform): ImmutableMap
	{
		return mapOf(new MapKeyValueOperation($this->store)->keys($transform));
	}

	/**
	 * Returns HashMap since values become keys and may be non-int.
	 *
	 * @return ImmutableMap<V,int>
	 */
	#[NoDiscard] // @phpstan-ignore generics.notSubtype
	public function flip(KeyCollisionStrategy $onCollision = KeyCollisionStrategy::Throw): ImmutableMap
	{
		return mapOf(new FlipKeyValueOperation($this->store)->items($onCollision)); // @phpstan-ignore return.type, argument.type, argument.templateType
	}

	/**
	 * @template NV
	 * @param iterable<int,NV> $data
	 * @return ImmutableMap<int,NV>
	 */
	protected function newMapOf(iterable $data): ImmutableMap
	{
		return intMapOf($data);
	}

	/**
	 * @template NV
	 * @param iterable<int,NV> $data
	 * @return ImmutableMap<int,NV>
	 */
	protected function newValueTransformedMapOf(iterable $data): ImmutableMap
	{
		return intMapOf($data);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toImmutable(): ImmutableMap
	{
		return intMapOf($this->store);
	}

	/** {@inheritDoc} */
	#[NoDiscard]
	public function toMutable(): MutableMap
	{
		return mutableIntMapOf($this->store);
	}
}
