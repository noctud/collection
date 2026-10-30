<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Noctud\Collection\Tests\Collection\Fixture\Cat;
use Noctud\Collection\Tests\Collection\Fixture\Dog;
use Noctud\Collection\Tests\Collection\Fixture\Walkable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use function is_string;
use function str_contains;

trait MapFilter
{
	#[Test]
	#[DataProvider('filterProvider')]
	public function filter(array $input, string $mode, string $needle, array $expected): void
	{
		$map = $this->mapOf($input);

		$actual = $map->filter(fn (string $v, string|int $k): bool => match ($mode) {
			'key_eq' => $k === $needle,
			'key_neq' => $k !== $needle,
			'key_contains' => is_string($k) && str_contains($k, $needle),
			'value_eq' => $v === $needle,
			'value_neq' => $v !== $needle,
			'value_contains' => str_contains($v, $needle),
			'both_contains' => is_string($k) && str_contains($k, $needle) && str_contains($v, $needle),
			default => false,
		})
			->toArray();

		$this->assertSame($expected, $actual);
	}

	public static function filterProvider(): iterable
	{
		yield 'empty input stays empty' => [
			'input' => [],
			'mode' => 'key_eq',
			'needle' => 'any',
			'expected' => [],
		];

		yield 'key equals — one match' => [
			'input' => ['key1' => 'value1', 'key2' => 'value2'],
			'mode' => 'key_eq',
			'needle' => 'key1',
			'expected' => ['key1' => 'value1'],
		];

		yield 'key not equals — everything except key1' => [
			'input' => ['key1' => 'value1', 'key2' => 'value2'],
			'mode' => 'key_neq',
			'needle' => 'key1',
			'expected' => ['key2' => 'value2'],
		];

		yield 'key contains — multiple, keeps order' => [
			'input' => ['alpha' => '1', 'beta' => '2', 'alpaca' => '3'],
			'mode' => 'key_contains',
			'needle' => 'alp',
			'expected' => ['alpha' => '1', 'alpaca' => '3'],
		];

		yield 'value equals' => [
			'input' => ['a' => 'x', 'b' => 'y', 'c' => 'x'],
			'mode' => 'value_eq',
			'needle' => 'x',
			'expected' => ['a' => 'x', 'c' => 'x'],
		];

		yield 'value not equals — none left' => [
			'input' => ['a' => 'x'],
			'mode' => 'value_neq',
			'needle' => 'x',
			'expected' => [],
		];

		yield 'value contains — unicode' => [
			'input' => ['k1' => 'žluťoučký kůň', 'k2' => 'kůzle'],
			'mode' => 'value_contains',
			'needle' => 'ků',
			'expected' => ['k1' => 'žluťoučký kůň', 'k2' => 'kůzle'],
		];

		yield 'both key and value contain needle' => [
			'input' => ['pre_a' => 'pre_x', 'pre_b' => 'x', 'c' => 'pre_x'],
			'mode' => 'both_contains',
			'needle' => 'pre',
			'expected' => ['pre_a' => 'pre_x'],
		];
	}

	#[Test]
	#[DataProvider('filterKeysProvider')]
	public function filterKeys(array $input, string $needle, string $mode, array $expected): void
	{
		$map = $this->mapOf($input);

		$actual = $map->filterKeys(fn ($k): bool => match ($mode) {
			'eq' => $k === $needle,
			'neq' => $k !== $needle,
			'contains' => is_string($k) && str_contains($k, $needle),
			default => false,
		})
			->toArray();

		$this->assertSame($expected, $actual);
	}

	public static function filterKeysProvider(): iterable
	{
		yield 'empty input' => [
			'input' => [],
			'needle' => 'x',
			'mode' => 'contains',
			'expected' => [],
		];

		yield 'equals — single' => [
			'input' => ['key1' => 'v1', 'key2' => 'v2'],
			'needle' => 'key1',
			'mode' => 'eq',
			'expected' => ['key1' => 'v1'],
		];

		yield 'not equals — all but one' => [
			'input' => ['key1' => 'v1', 'key2' => 'v2'],
			'needle' => 'key1',
			'mode' => 'neq',
			'expected' => ['key2' => 'v2'],
		];

		yield 'contains — none' => [
			'input' => ['a' => 'x', 'b' => 'y'],
			'needle' => 'z',
			'mode' => 'contains',
			'expected' => [],
		];

		yield 'contains — all' => [
			'input' => ['key1' => 'x', 'key2' => 'y'],
			'needle' => 'key',
			'mode' => 'contains',
			'expected' => ['key1' => 'x', 'key2' => 'y'],
		];
	}

	#[Test]
	#[DataProvider('filterValuesProvider')]
	public function filterValues(array $input, string $needle, string $mode, array $expected): void
	{
		$map = $this->mapOf($input);

		$actual = $map->filterValues(fn (string $v): bool => match ($mode) {
			'eq' => $v === $needle,
			'neq' => $v !== $needle,
			'contains' => str_contains($v, $needle),
			default => false,
		})
			->toArray();

		$this->assertSame($expected, $actual);

		$mapWithNulls = $this->mapOf($input + ['nullKey' => null, 'anotherNull' => null]);

		$this->assertSame($input, $mapWithNulls->filterValuesNotNull()->toArray());
	}

	public static function filterValuesProvider(): iterable
	{
		yield 'empty input' => [
			'input' => [],
			'needle' => 'x',
			'mode' => 'contains',
			'expected' => [],
		];

		yield 'equals — multiple' => [
			'input' => ['k1' => 'x', 'k2' => 'y', 'k3' => 'x'],
			'needle' => 'x',
			'mode' => 'eq',
			'expected' => ['k1' => 'x', 'k3' => 'x'],
		];

		yield 'not equals — none' => [
			'input' => ['k1' => 'x'],
			'needle' => 'x',
			'mode' => 'neq',
			'expected' => [],
		];

		yield 'contains — order preserved' => [
			'input' => ['a' => 'alpha', 'b' => 'beta', 'c' => 'alpaca'],
			'needle' => 'alp',
			'mode' => 'contains',
			'expected' => ['a' => 'alpha', 'c' => 'alpaca'],
		];

		yield 'contains — none' => [
			'input' => ['a' => 'x', 'b' => 'y'],
			'needle' => 'zzz',
			'mode' => 'contains',
			'expected' => [],
		];

		yield 'unicode value contains' => [
			'input' => ['u' => 'München', 'v' => 'Munich'],
			'needle' => 'ün',
			'mode' => 'contains',
			'expected' => ['u' => 'München'],
		];
	}

	#[Test]
	public function filterValuesInstanceOf_filters_by_class(): void
	{
		$dog = new Dog('Rex');
		$cat = new Cat('Whiskers');

		$map = $this->mapOfPairs([['a', $dog], ['b', $cat], ['c', new Dog('Buddy')]]);
		$filtered = $map->filterValuesInstanceOf(Dog::class);

		$this->assertCount(2, $filtered);
		$this->assertSame($dog, $filtered->get('a'));
	}

	#[Test]
	public function filterValuesInstanceOf_with_interface(): void
	{
		$dog = new Dog('Rex');
		$cat = new Cat('Whiskers');

		$map = $this->mapOfPairs([['a', $dog], ['b', $cat]]);
		$filtered = $map->filterValuesInstanceOf(Walkable::class);

		$this->assertCount(1, $filtered);
		$this->assertSame($dog, $filtered->get('a'));
	}

	#[Test]
	public function filterValuesInstanceOf_returns_empty_when_no_match(): void
	{
		$map = $this->mapOfPairs([['a', new Cat('Whiskers')]]);
		$filtered = $map->filterValuesInstanceOf(Dog::class);

		$this->assertSame([], $filtered->toArray());
	}

	#[Test]
	public function filterValuesInstanceOf_on_empty_map(): void
	{
		$map = $this->mapOf([]);
		$filtered = $map->filterValuesInstanceOf(Dog::class);

		$this->assertSame([], $filtered->toArray());
	}

	#[Test]
	public function filter_preserves_keys(): void
	{
		$map = $this->mapOfPairs([['01', 'a'], ['1', 'b']]);

		$actual = $map->filterKeys(fn (string $k) => $k === '1')
			->toArray();

		$this->assertSame(['1' => 'b'], $actual);
	}
}
