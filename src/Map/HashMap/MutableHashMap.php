<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map\HashMap;

use Closure;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Map\MutableMapLogic;
use ReflectionClass;

/**
 * Mutable hash map whose keys keep their type: strings, ints, floats, bools and objects.
 * Null, array and resource keys throw InvalidKeyTypeException. Float keys are compared to
 * 12 decimal places, Hashable objects by identity(), other objects by instance.
 * If the given data is Closure, the map will be lazily initialized when first accessed.
 *
 * To write your own MutableMap outside this class hierarchy, use the MutableMapLogic trait
 * in your own class and fill $store (e.g. with HashKeyValueStore::fromAssoc()); this way you
 * can extend your own base class.
 *
 * @template K of string|int|bool|float|object
 * @template V
 * @implements MutableMap<K,V>
 */
final class MutableHashMap implements MutableMap
{
    /** @use MutableMapLogic<K,V> */
    use MutableMapLogic;

    public function __construct()
    {
        $this->store = HashKeyValueStore::empty(); // @phpstan-ignore assign.propertyType
    }

    /**
     * @template NK of string|int|bool|float|object
     * @template NV
     * @param iterable<NK,NV>|Closure():iterable<NK,NV> $data
     * @return MutableMap<NK,NV>
     */
    public static function of(iterable|Closure $data = []): MutableMap
    {
        /** @var MutableHashMap<NK,NV> $map */
        $map = new MutableHashMap();

        if ($data instanceof Closure) {
            $reflector = new ReflectionClass(HashKeyValueStore::class);
            $map->store = $reflector->newLazyProxy(fn (HashKeyValueStore $object): HashKeyValueStore => $object::fromAssoc($data())); // @phpstan-ignore assign.propertyType
        } elseif ($data instanceof HashKeyValueStore) {
            $map->store = clone $data; // @phpstan-ignore assign.propertyType
        } elseif ($data instanceof self || $data instanceof ImmutableHashMap) {
            $map->store = clone $data->__internalCollectionStore(); // @phpstan-ignore assign.propertyType
        } else {
            $map->store = HashKeyValueStore::fromAssoc($data);
        }

        return $map;
    }

    /**
     * @template NK of string|int|bool|float|object
     * @template NV
     * @param iterable<array{0:NK,1:NV}>|Closure():iterable<array{0:NK,1:NV}> $data
     * @return MutableMap<NK,NV>
     */
    public static function ofPairs(iterable|Closure $data = []): MutableMap
    {
        /** @var MutableHashMap<NK,NV> $map */
        $map = new MutableHashMap();

        if ($data instanceof Closure) {
            $reflector = new ReflectionClass(HashKeyValueStore::class);
            $map->store = $reflector->newLazyProxy(fn (HashKeyValueStore $object): HashKeyValueStore => $object::fromPairs($data())); // @phpstan-ignore assign.propertyType
        } elseif ($data instanceof HashKeyValueStore) {
            $map->store = clone $data; // @phpstan-ignore assign.propertyType
        } elseif ($data instanceof self || $data instanceof ImmutableHashMap) {
            $map->store = clone $data->__internalCollectionStore(); // @phpstan-ignore assign.propertyType
        } else {
            $map->store = HashKeyValueStore::fromPairs($data);
        }

        return $map;
    }
}
