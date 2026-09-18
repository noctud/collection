<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\Extending;

use Closure;
use InvalidArgumentException;
use Noctud\Collection\Map\HashMap\HashKeyValueStore;
use Noctud\Collection\Map\ImmutableMap;
use Noctud\Collection\Map\ImmutableMapLogic;
use ReturnTypeWillChange;

/**
 * Extension style #2 (Map): base ImmutableMapLogic + class-level `@method self`
 * overrides + a newMapOf override.
 *
 * The constructor enforces an entry invariant: it proves that transforms
 * never go through the hand-written newMapOf() with transformed entries.
 *
 * @implements ImmutableMap<string, int>
 * @phpstan-consistent-constructor
 * @method self filterValues(Closure(int): bool $predicate)
 * @method self sortedByValueDesc()
 */
class ManualScoreBoard implements ImmutableMap
{
	/** @use ImmutableMapLogic<string, int> */
	use ImmutableMapLogic;

	/** @param iterable<string, int> $data */
	public function __construct(iterable $data = [])
	{
		// Buffered into pairs: iterator_to_array() crashes on non-scalar keys before the guard runs
		$pairs = [];
		foreach ($data as $name => $score) {
			self::assertEntry($name, $score);
			$pairs[] = [$name, $score];
		}

		$this->store = HashKeyValueStore::fromPairs($pairs);
	}

	// mixed on purpose: the guard checks at runtime what the PHPDoc already promises,
	// to catch internal rebuilds that would bypass the declared entry types.
	private static function assertEntry(mixed $name, mixed $score): void
	{
		if (!is_string($name) || !is_int($score)) {
			throw new InvalidArgumentException('ManualScoreBoard must only hold string => int entries');
		}
	}

	/**
	 * Deliberately omits a native return type to exercise legacy ArrayAccess compatibility.
	 *
	 * @param string $offset
	 * @return int
	 */
	#[ReturnTypeWillChange]
	public function offsetGet(mixed $offset)
	{
		return $this->get($offset);
	}

	/** @param iterable<string, int> $data */
	protected function newMapOf(iterable $data): ImmutableMap
	{
		return new self($data);
	}

	public function winners(): self
	{
		return $this->filterValues(static fn (int $score): bool => $score >= 100);
	}

	public function ranked(): self
	{
		return $this->sortedByValueDesc();
	}

	/** @return ImmutableMap<string, float> */
	public function scaled(): ImmutableMap
	{
		return $this->mapValues(static fn (int $score): float => $score * 1.5);
	}
}
