<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map\Extending;

use Noctud\Collection\Map\ImmutableMap;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that extending a Map self-preserves the subtype at runtime.
 *
 * The fixtures' `: self` domain methods are the compile-time narrowing assertions,
 * since tests/ is analysed by PHPStan at level 9.
 */
final class MapExtendingTest extends TestCase
{
	#[Test]
	public function trait_filter_returns_subtype(): void
	{
		$board = new ScoreBoard(['alice' => 120, 'bob' => 80, 'carol' => 100]);

		$winners = $board->winners();

		self::assertInstanceOf(ScoreBoard::class, $winners);
		self::assertCount(2, $winners);
		self::assertSame(120, $winners['alice']);
	}

	#[Test]
	public function trait_ordering_returns_subtype(): void
	{
		$board = new ScoreBoard(['alice' => 120, 'bob' => 80, 'carol' => 100]);

		$ranked = $board->ranked();
		$byName = $board->byName();

		self::assertInstanceOf(ScoreBoard::class, $ranked);
		self::assertInstanceOf(ScoreBoard::class, $byName);
		self::assertSame(['alice', 'carol', 'bob'], $ranked->keys->toArray());
		self::assertSame(['alice', 'bob', 'carol'], $byName->keys->toArray());
	}

	#[Test]
	public function trait_mutation_returns_subtype(): void
	{
		$board = new ScoreBoard(['alice' => 120]);

		$added = $board->with('bob', 90);
		$removed = $added->without('alice');

		self::assertInstanceOf(ScoreBoard::class, $added);
		self::assertInstanceOf(ScoreBoard::class, $removed);
		self::assertCount(2, $added);
		self::assertCount(1, $removed);
		self::assertSame(90, $removed['bob']);
	}

	#[Test]
	public function trait_chained_returns_subtype(): void
	{
		$board = new ScoreBoard(['alice' => 120, 'bob' => 80, 'carol' => 100]);

		$top = $board->top();

		self::assertInstanceOf(ScoreBoard::class, $top);
		self::assertCount(1, $top);
		self::assertSame(['alice'], $top->keys->toArray());
	}

	#[Test]
	public function manual_filter_and_ordering_return_subtype(): void
	{
		$board = new ManualScoreBoard(['alice' => 120, 'bob' => 80, 'carol' => 100]);

		$winners = $board->winners();
		$ranked = $board->ranked();

		self::assertInstanceOf(ManualScoreBoard::class, $winners);
		self::assertInstanceOf(ManualScoreBoard::class, $ranked);
		self::assertCount(2, $winners);
	}

	#[Test]
	public function trait_transform_narrows_key_and_value_types(): void
	{
		$board = new ScoreBoard(['alice' => 120, 'bob' => 80]);

		$rekeyed = $board->rekeyByScore();
		$scaled = $board->scaled();
		$labels = $board->winnerLabels();

		// The key/value type changed, so the static type narrows to the base
		// ImmutableMap<NK,V> / ImmutableMap<K,NV> (asserted by PHPStan via these
		// methods' @return). At runtime the result is a plain base map too: the
		// subtype constructor (which enforces the string => int invariant) is never
		// re-entered with transformed entries.
		self::assertInstanceOf(ImmutableMap::class, $rekeyed);
		self::assertNotInstanceOf(ScoreBoard::class, $rekeyed);
		self::assertNotInstanceOf(ScoreBoard::class, $scaled);
		self::assertNotInstanceOf(ScoreBoard::class, $labels);
		self::assertSame([120, 80], $rekeyed->keys->toArray());
		self::assertSame(180.0, $scaled['alice']);
		self::assertSame(120.0, $scaled['bob']);
		self::assertCount(1, $labels);
		self::assertSame('win', $labels['alice']);
	}
}
