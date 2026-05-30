<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

use Closure;
use Noctud\Collection\List\ArrayList\ImmutableArrayList;
use Noctud\Collection\List\ArrayList\MutableArrayList;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\List\MutableList;
use Noctud\Collection\Map\HashMap\ImmutableHashMap;
use Noctud\Collection\Map\HashMap\MutableHashMap;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\IntMap\ImmutableIntMap;
use Noctud\Collection\Map\IntMap\MutableIntMap;
use Noctud\Collection\Map\MutableMap;
use Noctud\Collection\Map\StringMap\ImmutableStringMap;
use Noctud\Collection\Map\StringMap\MutableStringMap;
use Noctud\Collection\Set\HashSet\ImmutableHashSet;
use Noctud\Collection\Set\HashSet\MutableHashSet;
use Noctud\Collection\Set\ImmutableSet;
use Noctud\Collection\Set\MutableSet;
use function function_exists;

// Avoid redeclaration errors
if (!function_exists('Noctud\Collection\listOf')) {
	/**
	 * Creates an immutable list.
	 * If the given data is Closure, the list will be lazily initialized when first accessed.
	 *
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return ImmutableList<E>
	 */
	function listOf(iterable|Closure $data = []): ImmutableList
	{
		return new ImmutableArrayList($data);
	}

	/**
	 * Creates a mutable list.
	 * If the given data is Closure, the list will be lazily initialized when first accessed.
	 *
	 * @template E = mixed
	 * @param iterable<E>|Closure():iterable<E>|null $data
	 * @return MutableList<E>
	 */
	function mutableListOf(iterable|Closure|null $data = null): MutableList
	{
		return new MutableArrayList($data ?? []);
	}

	/**
	 * Creates an immutable set.
	 * If the given data contains duplicate values, only the first occurrence is kept.
	 * If the given data is Closure, the set will be lazily initialized when first accessed.
	 *
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return ImmutableSet<E>
	 */
	function setOf(iterable|Closure $data = []): ImmutableSet
	{
		return new ImmutableHashSet($data);
	}

	/**
	 * Creates a mutable set.
	 * If the given data contains duplicate values, only the first occurrence is kept.
	 * If the given data is Closure, the set will be lazily initialized when first accessed.
	 *
	 * @template E = mixed
	 * @param iterable<E>|Closure():iterable<E>|null $data
	 * @return MutableSet<E>
	 */
	function mutableSetOf(iterable|Closure|null $data = null): MutableSet
	{
		return new MutableHashSet($data ?? []);
	}

	/**
	 * Creates an immutable map.
	 * If the given data is Closure, the map will be lazily initialized when first accessed.
	 *
	 * @template K of string|int|bool|float|object
	 * @template V
	 * @param iterable<K,V>|Closure():iterable<K,V> $data
	 * @return ImmutableMap<K,V>
	 */
	function mapOf(iterable|Closure $data = []): ImmutableMap
	{
		return ImmutableHashMap::of($data);
	}

	/**
	 * Creates an immutable map from pairs like [['key1', 'value1'], ['key2', 'value2']].
	 * If the given data is Closure, the map will be lazily initialized when first accessed.
	 *
	 * @template K of string|int|bool|float|object
	 * @template V
	 * @param iterable<array{0:K,1:V}>|Closure():iterable<array{0:K,1:V}> $data
	 * @return ImmutableMap<K,V>
	 */
	function mapOfPairs(iterable|Closure $data = []): ImmutableMap
	{
		return ImmutableHashMap::ofPairs($data);
	}

	/**
	 * Creates a mutable map.
	 * If the given data is Closure, the map will be lazily initialized when first accessed.
	 *
	 * @template K of string|int|bool|float|object = string|int|bool|float|object
	 * @template V = mixed
	 * @param iterable<K,V>|Closure():iterable<K,V>|null $data
	 * @return MutableMap<K,V>
	 */
	function mutableMapOf(iterable|Closure|null $data = null): MutableMap
	{
		return MutableHashMap::of($data ?? []);
	}

	/**
	 * Creates a mutable map from pairs like [['key1', 'value1'], ['key2', 'value2']].
	 * If the given data is Closure, the map will be lazily initialized when first accessed.
	 *
	 * @template K of string|int|bool|float|object
	 * @template V
	 * @param iterable<array{0:K,1:V}>|Closure():iterable<array{0:K,1:V}> $data
	 * @return MutableMap<K,V>
	 */
	function mutableMapOfPairs(iterable|Closure $data): MutableMap
	{
		return MutableHashMap::ofPairs($data);
	}

	/**
	 * Creates an immutable string-key map with optimized single-array storage.
	 * Int keys are automatically cast to string during construction (handles PHP's numeric string casting).
	 * If the given data is Closure, the map will be lazily initialized when first accessed.
	 *
	 * @template V
	 * @param iterable<string|int,V>|Closure():iterable<string|int,V> $data
	 * @return ImmutableMap<string,V>
	 */
	function stringMapOf(iterable|Closure $data = []): ImmutableMap
	{
		return new ImmutableStringMap($data);
	}

	/**
	 * Creates a mutable string-key map with optimized single-array storage.
	 * Int keys are automatically cast to string during construction (handles PHP's numeric string casting).
	 * If the given data is Closure, the map will be lazily initialized when first accessed.
	 *
	 * @template V = mixed
	 * @param iterable<string|int,V>|Closure():iterable<string|int,V>|null $data
	 * @return MutableMap<string,V>
	 */
	function mutableStringMapOf(iterable|Closure|null $data = null): MutableMap
	{
		return new MutableStringMap($data ?? []);
	}

	/**
	 * Creates an immutable int-key map with optimized single-array storage.
	 * Only accepts int keys; non-int keys throw InvalidKeyTypeException.
	 * If the given data is Closure, the map will be lazily initialized when first accessed.
	 *
	 * @template V
	 * @param iterable<int,V>|Closure():iterable<int,V> $data
	 * @return ImmutableMap<int,V>
	 */
	function intMapOf(iterable|Closure $data = []): ImmutableMap
	{
		return new ImmutableIntMap($data);
	}

	/**
	 * Creates a mutable int-key map with optimized single-array storage.
	 * Only accepts int keys; non-int keys throw InvalidKeyTypeException.
	 * If the given data is Closure, the map will be lazily initialized when first accessed.
	 *
	 * @template V = mixed
	 * @param iterable<int,V>|Closure():iterable<int,V>|null $data
	 * @return MutableMap<int,V>
	 */
	function mutableIntMapOf(iterable|Closure|null $data = null): MutableMap
	{
		return new MutableIntMap($data ?? []);
	}
}
