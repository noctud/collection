<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List;

use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\Exception\UnsupportedOperationException;
use Noctud\Collection\List\ImmutableList;
use Noctud\Collection\List\MutableList;
use PHPUnit\Framework\Attributes\Test;

trait ListMutate
{
	#[Test]
	public function addFirst_allows_duplicates(): void
	{
		$list = $this->collectionOf(['a', 'b', 'c']);

		if ($list instanceof MutableList) {
			$list->addFirst('b');
			$this->assertSame(['b', 'a', 'b', 'c'], $list->toArray());
		} else {
			$result = $list->addFirst('b');
			$this->assertSame(['b', 'a', 'b', 'c'], $result->toArray());
		}
	}

	#[Test]
	public function addFirst_reindexes(): void
	{
		$list = $this->collectionOf([10, 20, 30]);

		if ($list instanceof MutableList) {
			$list->addFirst(5);
			$this->assertSame(5, $list->get(0));
			$this->assertSame(10, $list->get(1));
			$this->assertSame(30, $list->get(3));
		} else {
			$result = $list->addFirst(5);
			$this->assertSame(5, $result->get(0));
			$this->assertSame(10, $result->get(1));
			$this->assertSame(30, $result->get(3));
		}
	}

	#[Test]
	public function set_element_at_index(): void
	{
		$list = $this->collectionOf(['a', 'b', 'c']);

		if ($list instanceof MutableList) {
			$tracked = $list->tracked();
			$result = $tracked->set(1, 'x');
			$this->assertSame($tracked, $result);
			$this->assertSame(['a', 'x', 'c'], $list->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $list->set(1, 'x');
			$this->assertNotSame($list, $result);
			$this->assertSame(['a', 'b', 'c'], $list->toArray());
			$this->assertSame(['a', 'x', 'c'], $result->toArray());
		}
	}

	#[Test]
	public function set_throws_on_invalid_index(): void
	{
		$list = $this->collectionOf(['a']);

		$this->expectException(IndexOutOfBoundsException::class);
		$list->set(5, 'x');
	}

	#[Test]
	public function removeEvery_occurrence(): void
	{
		$list = $this->collectionOf(['a', 'b', 'a', 'c', 'a']);

		if ($list instanceof MutableList) {
			$result = $list->removeEvery('a');
			$this->assertSame($list, $result);
			$this->assertSame(['b', 'c'], $list->toArray());

			$list2 = $this->collectionOf(['a', 'b', 'a', 'c', 'a']);
			if ($list2 instanceof MutableList) {
				$tracked = $list2->tracked();
				$result = $tracked->removeEvery('a');
				$this->assertSame(['b', 'c'], $list2->toArray());
				$this->assertTrue($result->changed);
			}
		} else {
			$result = $list->removeEvery('a');
			$this->assertNotSame($list, $result);
			$this->assertSame(['a', 'b', 'a', 'c', 'a'], $list->toArray());
			$this->assertSame(['b', 'c'], $result->toArray());
		}
	}

	#[Test]
	public function removeEvery_not_found(): void
	{
		$list = $this->collectionOf(['a', 'b']);

		if ($list instanceof MutableList) {
			$tracked = $list->tracked();
			$result = $tracked->removeEvery('z');
			$this->assertSame(['a', 'b'], $list->toArray());
			$this->assertSame(false, $result->changed);
		} else {
			$result = $list->removeEvery('z');
			$this->assertSame(['a', 'b'], $result->toArray());
		}
	}

	#[Test]
	public function removeEvery_compares_strictly_and_reindexes(): void
	{
		$list = $this->collectionOf([0, '0', 0, 0, false, 0]);
		$result = $list->removeEvery(0);
		$this->assertSame(['0', false], $result->toArray());
		$this->assertSame(1, $result->indexOf(false));

		$all = $this->collectionOf(['a', 'a', 'a']);
		$this->assertSame([], $all->removeEvery('a')->toArray());
	}

	#[Test]
	public function removeAt_index(): void
	{
		$list = $this->collectionOf(['a', 'b', 'c']);

		if ($list instanceof MutableList) {
			$tracked = $list->tracked();
			$result = $tracked->removeAt(1);
			$this->assertSame(['a', 'c'], $list->toArray());
			$this->assertTrue($result->changed);
		} else {
			$result = $list->removeAt(1);
			$this->assertNotSame($list, $result);
			$this->assertSame(['a', 'b', 'c'], $list->toArray());
			$this->assertSame(['a', 'c'], $result->toArray());
		}
	}

	#[Test]
	public function removeAt_throws_on_invalid_index(): void
	{
		$list = $this->collectionOf(['a']);

		$this->expectException(IndexOutOfBoundsException::class);
		$list->removeAt(5);
	}

	#[Test]
	public function removeAt_first_element(): void
	{
		$list = $this->collectionOf(['a', 'b', 'c']);

		if ($list instanceof MutableList) {
			$list->removeAt(0);
			$this->assertSame(['b', 'c'], $list->toArray());
		} else {
			$result = $list->removeAt(0);
			$this->assertSame(['b', 'c'], $result->toArray());
		}
	}

	#[Test]
	public function removeAt_last_element(): void
	{
		$list = $this->collectionOf(['a', 'b', 'c']);

		if ($list instanceof MutableList) {
			$list->removeAt(2);
			$this->assertSame(['a', 'b'], $list->toArray());
		} else {
			$result = $list->removeAt(2);
			$this->assertSame(['a', 'b'], $result->toArray());
		}
	}

	// --- ArrayAccess mutations ---

	#[Test]
	public function offsetSet_sets_element_at_index(): void
	{
		/** @var MutableList<string>|ImmutableList<string> $list */
		$list = $this->collectionOf(['a', 'b', 'c']);

		if ($list instanceof MutableList) {
			$list[1] = 'x';
			$this->assertSame(['a', 'x', 'c'], $list->toArray());

			$tracked = $list->tracked();
			$tracked[0] = 'z';
			$this->assertSame(['z', 'x', 'c'], $list->toArray());
		} else {
			$this->expectException(UnsupportedOperationException::class);
			$list[1] = 'x';
		}
	}

	#[Test]
	public function offsetSet_appends_with_null_index(): void
	{
		/** @var MutableList<string>|ImmutableList<string> $list */
		$list = $this->collectionOf(['a', 'b']);

		if ($list instanceof MutableList) {
			$list[] = 'c';
			$this->assertSame(['a', 'b', 'c'], $list->toArray());

			$tracked = $list->tracked();
			$tracked[] = 'd';
			$this->assertSame(['a', 'b', 'c', 'd'], $list->toArray());
		} else {
			$this->expectException(UnsupportedOperationException::class);
			$list[] = 'c';
		}
	}

	#[Test]
	public function offsetUnset_removes_element_at_index(): void
	{
		/** @var MutableList<string>|ImmutableList<string> $list */
		$list = $this->collectionOf(['a', 'b', 'c']);

		if ($list instanceof MutableList) {
			unset($list[1]);
			$this->assertSame(['a', 'c'], $list->toArray());

			$tracked = $list->tracked();
			unset($tracked[0]);
			$this->assertSame(['c'], $list->toArray());
		} else {
			$this->expectException(UnsupportedOperationException::class);
			unset($list[1]);
		}
	}
}
