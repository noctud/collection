<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection;

use ArrayIterator;
use Generator;
use LimitIterator;
use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\Exception\UnsupportedOperationException;
use Noctud\Collection\Map\ImmutableMap;
use NoRewindIterator;
use PHPUnit\Framework\Attributes\Test;
use SplStack;
use function strtoupper;
use function strlen;
use function iterator_to_array;

trait CollectionGroupAndZip
{
	#[Test]
	public function groupBy_key_selector(): void
	{
		$collection = $this->collectionOf(['apple', 'avocado', 'banana', 'blueberry', 'cherry']);
		$grouped = $collection->groupBy(fn ($v) => $v[0]);

		$this->assertSame(['apple', 'avocado'], $grouped->get('a')->toArray());
		$this->assertSame(['banana', 'blueberry'], $grouped->get('b')->toArray());
		$this->assertSame(['cherry'], $grouped->get('c')->toArray());
	}

	#[Test]
	public function groupBy_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$grouped = $collection->groupBy(fn ($v) => $v);
		$this->assertSame(0, $grouped->count());
	}

	#[Test]
	public function groupBy_numeric_key(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5, 6]);
		$grouped = $collection->groupBy(fn ($v) => $v % 2 === 0 ? 'even' : 'odd');

		$this->assertSame([1, 3, 5], $grouped->get('odd')->toArray());
		$this->assertSame([2, 4, 6], $grouped->get('even')->toArray());
	}

	#[Test]
	public function groupBy_with_value_transform(): void
	{
		$collection = $this->collectionOf(['apple', 'avocado', 'banana', 'blueberry', 'cherry']);
		$grouped = $collection->groupBy(fn ($v) => $v[0], fn ($v) => strtoupper($v));

		$this->assertSame(['APPLE', 'AVOCADO'], $grouped->get('a')->toArray());
		$this->assertSame(['BANANA', 'BLUEBERRY'], $grouped->get('b')->toArray());
		$this->assertSame(['CHERRY'], $grouped->get('c')->toArray());
	}

	#[Test]
	public function groupBy_with_value_transform_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$grouped = $collection->groupBy(fn ($v) => $v, fn ($v) => $v);
		$this->assertSame(0, $grouped->count());
	}

	#[Test]
	public function groupBy_with_value_transform_to_different_type(): void
	{
		$collection = $this->collectionOf(['apple', 'avocado', 'banana']);
		$grouped = $collection->groupBy(fn ($v) => $v[0], fn ($v) => strlen($v));

		$this->assertSame([5, 7], $grouped->get('a')->toArray());
		$this->assertSame([6], $grouped->get('b')->toArray());
	}

	#[Test]
	public function groupBy_with_null_key_throws(): void
	{
		$this->expectException(InvalidKeyTypeException::class);

		// @phpstan-ignore argument.templateType
		$_ = $this->collectionOf(['a', 'b'])->groupBy(fn ($v) => null); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function groupBy_with_array_key_throws(): void
	{
		$this->expectException(InvalidKeyTypeException::class);

		// @phpstan-ignore argument.templateType
		$_ = $this->collectionOf(['a', 'b'])->groupBy(fn ($v) => [$v]); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function zip_with_another_iterable(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$zipped = $collection->zip(['a', 'b', 'c']);

		$this->assertSame([[1, 'a'], [2, 'b'], [3, 'c']], $zipped->toArray());
	}

	#[Test]
	public function zip_with_shorter_iterable(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$zipped = $collection->zip(['a']);

		$this->assertSame([[1, 'a']], $zipped->toArray());
	}

	#[Test]
	public function zip_with_longer_iterable(): void
	{
		$collection = $this->collectionOf([1]);
		$zipped = $collection->zip(['a', 'b', 'c']);

		$this->assertSame([[1, 'a']], $zipped->toArray());
	}

	#[Test]
	public function zip_with_empty(): void
	{
		$collection = $this->collectionOf([1, 2]);
		$this->assertSame([], $collection->zip([])->toArray());
	}

	#[Test]
	public function zip_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->zip(['a'])->toArray());
	}

	#[Test]
	public function zip_pulls_the_other_iterable_lazily(): void
	{
		$pulled = [];
		$other = (static function () use (&$pulled): Generator {
			foreach (['a', 'b', 'c', 'd'] as $value) {
				$pulled[] = $value;

				yield $value;
			}
		})();

		$collection = $this->collectionOf([1, 2]);

		$this->assertSame([[1, 'a'], [2, 'b']], $collection->zip($other)->toArray());

		// The other side is walked in lockstep, never buffered - and not pulled once past the
		// shorter side either.
		$this->assertSame(['a', 'b'], $pulled);
	}

	#[Test]
	public function zip_with_an_unpositioned_iterator(): void
	{
		$stack = new SplStack();
		$stack->push('a');
		$stack->push('b');

		// An SplDoublyLinkedList holds no position until it is rewound, so valid() answers
		// false on a stack that plainly has elements. Left unrewound, zip reads that as an
		// empty side and pairs nothing at all.
		$this->assertCount(2, $stack);
		$this->assertSame(['b', 'a'], iterator_to_array($stack, false));

		$collection = $this->collectionOf([1, 2]);

		$this->assertSame([[1, 'b'], [2, 'a']], $collection->zip($stack)->toArray());
	}

	#[Test]
	public function zip_with_an_iterator_decorator(): void
	{
		$other = new LimitIterator(new ArrayIterator(['a', 'b', 'c']), 0, 2);
		$collection = $this->collectionOf([1, 2]);

		$this->assertSame([[1, 'a'], [2, 'b']], $collection->zip($other)->toArray());
	}

	#[Test]
	public function zip_with_a_started_generator(): void
	{
		$other = (static function (): Generator {
			yield 'a';
			yield 'b';
			yield 'c';
		})();
		$other->current();
		$other->next();

		$collection = $this->collectionOf([1, 2]);

		// A Generator is never rewound: it is always positioned, so it resumes from where it
		// stands and the head of a stream can be consumed before zipping the rest.
		$this->assertSame([[1, 'b'], [2, 'c']], $collection->zip($other)->toArray());
	}

	#[Test]
	public function zip_with_an_advanced_iterator(): void
	{
		$other = new ArrayIterator(['a', 'b', 'c']);
		$other->next();

		$collection = $this->collectionOf([1, 2]);

		// The flip side of rewinding everything that is not a Generator: an advanced cursor
		// cannot be told apart from a fresh one, so it restarts rather than resuming.
		$this->assertSame([[1, 'a'], [2, 'b']], $collection->zip($other)->toArray());
	}

	#[Test]
	public function zip_with_an_advanced_iterator_wrapped_in_a_no_rewind_iterator(): void
	{
		$other = new ArrayIterator(['a', 'b', 'c']);
		$other->next();

		$collection = $this->collectionOf([1, 2]);

		// NoRewindIterator::rewind() is a no-op, which is how a caller who does mean to resume
		// a non-Generator cursor says so: only they can know that it is mid-stream.
		$this->assertSame([[1, 'b'], [2, 'c']], $collection->zip(new NoRewindIterator($other))->toArray());
	}

	#[Test]
	public function zipWithNext_pairs_adjacent_elements(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4]);
		$result = $collection->zipWithNext();

		$this->assertSame([[1, 2], [2, 3], [3, 4]], $result->toArray());
	}

	#[Test]
	public function zipWithNext_with_map(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4]);
		$result = $collection->zipWithNext()->map(fn ($pair) => $pair[0] + $pair[1]);

		$this->assertSame([3, 5, 7], $result->toArray());
	}

	#[Test]
	public function zipWithNext_single_element(): void
	{
		$collection = $this->collectionOf([1]);
		$this->assertSame([], $collection->zipWithNext()->toArray());
	}

	#[Test]
	public function zipWithNext_empty(): void
	{
		$collection = $this->collectionOf([]);
		$this->assertSame([], $collection->zipWithNext()->toArray());
	}

	#[Test]
	public function zipWithNext_two_elements(): void
	{
		$collection = $this->collectionOf(['a', 'b']);
		$this->assertSame([['a', 'b']], $collection->zipWithNext()->toArray());
	}

	#[Test]
	public function zipWithNext_with_string_map(): void
	{
		$collection = $this->collectionOf(['a', 'b', 'c']);
		$result = $collection->zipWithNext()->map(fn ($pair) => "{$pair[0]}-{$pair[1]}");

		$this->assertSame(['a-b', 'b-c'], $result->toArray());
	}

	#[Test]
	public function unzip_pairs(): void
	{
		$collection = $this->collectionOf([[1, 'a'], [2, 'b'], [3, 'c']]);
		[$first, $second] = $collection->unzip();

		$this->assertSame([1, 2, 3], $first->toArray());
		$this->assertSame(['a', 'b', 'c'], $second->toArray());
	}

	#[Test]
	public function unzip_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		[$first, $second] = $collection->unzip();

		$this->assertSame([], $first->toArray());
		$this->assertSame([], $second->toArray());
	}

	#[Test]
	public function unzip_single_pair(): void
	{
		$collection = $this->collectionOf([[42, 'x']]);
		[$first, $second] = $collection->unzip();

		$this->assertSame([42], $first->toArray());
		$this->assertSame(['x'], $second->toArray());
	}

	#[Test]
	public function unzip_roundtrip_with_zip(): void
	{
		$a = $this->collectionOf([1, 2, 3]);
		$zipped = $a->zip(['a', 'b', 'c']);
		[$first, $second] = $zipped->unzip();

		$this->assertSame([1, 2, 3], $first->toArray());
		$this->assertSame(['a', 'b', 'c'], $second->toArray());
	}

	#[Test]
	public function unzip_throws_on_non_pair_elements(): void
	{
		$this->expectException(UnsupportedOperationException::class);
		$result = $this->collectionOf([1, 2, 3])->unzip(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function partition_by_predicate(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5, 6]);
		[$matching, $nonMatching] = $collection->partition(fn ($v) => $v % 2 === 0);

		$this->assertSame([2, 4, 6], $matching->toArray());
		$this->assertSame([1, 3, 5], $nonMatching->toArray());
	}

	#[Test]
	public function partition_none_match(): void
	{
		$collection = $this->collectionOf([1, 3, 5]);
		[$matching, $nonMatching] = $collection->partition(fn ($v) => $v % 2 === 0);

		$this->assertSame([], $matching->toArray());
		$this->assertSame([1, 3, 5], $nonMatching->toArray());
	}

	#[Test]
	public function partition_all_match(): void
	{
		$collection = $this->collectionOf([2, 4, 6]);
		[$matching, $nonMatching] = $collection->partition(fn ($v) => $v % 2 === 0);

		$this->assertSame([2, 4, 6], $matching->toArray());
		$this->assertSame([], $nonMatching->toArray());
	}

	#[Test]
	public function partition_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		[$matching, $nonMatching] = $collection->partition(fn ($v) => true);

		$this->assertSame([], $matching->toArray());
		$this->assertSame([], $nonMatching->toArray());
	}

	#[Test]
	public function countBy_groups_and_counts(): void
	{
		$collection = $this->collectionOf(['apple', 'avocado', 'banana', 'blueberry', 'cherry']);
		$result = $collection->countBy(fn ($v) => $v[0]);

		$this->assertSame(2, $result->get('a'));
		$this->assertSame(2, $result->get('b'));
		$this->assertSame(1, $result->get('c'));
		$this->assertSame(3, $result->count());
	}

	#[Test]
	public function countBy_with_null_key_throws(): void
	{
		$this->expectException(InvalidKeyTypeException::class);

		// @phpstan-ignore argument.templateType
		$_ = $this->collectionOf(['a', 'b'])->countBy(fn ($v) => null); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function countBy_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$result = $collection->countBy(fn ($v) => $v);

		$this->assertSame(0, $result->count());
		$this->assertInstanceOf(ImmutableMap::class, $result);
	}

	#[Test]
	public function countBy_all_same_key(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5]);
		$result = $collection->countBy(fn ($v) => 'all');

		$this->assertSame(5, $result->get('all'));
		$this->assertSame(1, $result->count());
	}

	#[Test]
	public function countBy_each_unique(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$result = $collection->countBy(fn ($v) => $v);

		$this->assertSame(1, $result->get(1));
		$this->assertSame(1, $result->get(2));
		$this->assertSame(1, $result->get(3));
		$this->assertSame(3, $result->count());
	}

	#[Test]
	public function countBy_with_numeric_keys(): void
	{
		$collection = $this->collectionOf([1, 2, 3, 4, 5, 6]);
		$result = $collection->countBy(fn ($v) => $v % 2 === 0 ? 'even' : 'odd');

		$this->assertSame(3, $result->get('odd'));
		$this->assertSame(3, $result->get('even'));
	}

	#[Test]
	public function countBy_returns_immutable_map(): void
	{
		$collection = $this->collectionOf([1, 2, 3]);
		$result = $collection->countBy(fn ($v) => $v);

		$this->assertInstanceOf(ImmutableMap::class, $result);
	}

	#[Test]
	public function toMap_with_key_mapper(): void
	{
		$collection = $this->collectionOf(['apple', 'banana', 'cherry']);
		$map = $collection->toMap(fn ($v) => $v[0]);

		$this->assertSame('apple', $map->get('a'));
		$this->assertSame('banana', $map->get('b'));
		$this->assertSame('cherry', $map->get('c'));
	}

	#[Test]
	public function toMap_with_key_and_value_mapper(): void
	{
		$collection = $this->collectionOf(['apple', 'banana', 'cherry']);
		$map = $collection->toMap(fn ($v) => $v[0], fn ($v) => strlen($v));

		$this->assertSame(5, $map->get('a'));
		$this->assertSame(6, $map->get('b'));
		$this->assertSame(6, $map->get('c'));
	}

	#[Test]
	public function toMap_on_empty(): void
	{
		$collection = $this->collectionOf([]);
		$map = $collection->toMap(fn ($v) => $v);
		$this->assertSame(0, $map->count());
	}
}
