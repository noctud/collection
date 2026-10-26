<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Unit;

use Closure;
use Noctud\Collection\Collection;
use Noctud\Collection\List\ArrayList\ArrayIndexStore;
use Noctud\Collection\Map\View\MapValueCollection;
use Noctud\Collection\Tests\Collection\Case\AbstractCollectionTestCase;
use PHPUnit\Framework\Attributes\Test;
use function Noctud\Collection\mapOf;

final class ImmutableMapValueCollectionTest extends AbstractCollectionTestCase
{
	/**
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return Collection<E>
	 */
	public function collectionOf(iterable|Closure $data): Collection
	{
		return mapOf($data)->values; // @phpstan-ignore argument.templateType
	}

	/**
	 * @template E
	 * @param iterable<E>|Closure():iterable<E> $data
	 * @return Collection<E>
	 */
	public function enumerableOf(iterable|Closure $data): Collection
	{
		return $this->collectionOf($data);
	}

	#[Test]
	public function sortedBy_sorts_a_copy_of_an_element_store(): void
	{
		$store = new ArrayIndexStore(['ccc', 'a', 'bb']);
		$values = new MapValueCollection($store);

		$this->assertSame(['a', 'bb', 'ccc'], $values->sortedBy(strlen(...))->toArray());
		$this->assertSame(['ccc', 'bb', 'a'], $values->sortedByDesc(strlen(...))->toArray());
		$this->assertSame(['ccc', 'a', 'bb'], $store->toArray());
	}
}
