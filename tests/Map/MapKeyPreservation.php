<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Map;

use Noctud\Collection\Exception\InvalidKeyTypeException;
use Noctud\Collection\Exception\ConversionException;
use PHPUnit\Framework\Attributes\Test;
use function fopen;
use function fclose;

trait MapKeyPreservation
{
	#[Test]
	public function preserved_but_not_suitable_for_to_array(): void
	{
		$map = $this->mapOfPairs([['1', 'a'], [1, 'b'], [1.5, 'b'], [true, 't'], [false, 'f']]);

		$this->assertTrue($map->containsKey('1'));
		$this->assertTrue($map->containsKey(1));
		$this->assertTrue($map->containsKey(1.5));
		$this->assertTrue($map->containsKey(true));
		$this->assertTrue($map->containsKey(false));

		$this->assertSame('a', $map->get('1'));
		$this->assertSame('b', $map->get(1));
		$this->assertSame('b', $map->get(1.5));
		$this->assertSame('t', $map->get(true));
		$this->assertSame('f', $map->get(false));

		// Throw because of duplicate keys
		$this->expectException(ConversionException::class);
		$result = $map->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function null_key_throws(): void
	{
		$this->expectException(InvalidKeyTypeException::class);
		$map = $this->mapOfPairs([[null, 'n']]); // @phpstan-ignore argument.type, argument.templateType
		$map->count(); // Force lazy initialization
	}

	#[Test]
	public function preserved_and_suitable_for_to_array(): void
	{
		// Use 2.0 instead of 2.5 - floats with precision loss cannot be converted to array keys
		$map = $this->mapOfPairs([['1', 'a'], [2.0, 'b'], [false, 'f']]);

		$this->assertTrue($map->containsKey('1'));
		$this->assertTrue($map->containsKey(2.0));
		$this->assertTrue($map->containsKey(false));

		$this->assertSame('a', $map->get('1'));
		$this->assertSame('b', $map->get(2.0));
		$this->assertSame('f', $map->get(false));

		$this->assertSame([
			'1' => 'a',
			2 => 'b',
			false => 'f',
		], $map->toArray());
	}

	#[Test]
	public function resource_key_throws(): void
	{
		$resource = fopen('php://memory', 'r');
		$this->assertIsResource($resource);

		try {
			$this->expectException(InvalidKeyTypeException::class);
			$this->expectExceptionMessage('Resources are not supported as map keys');
			$map = $this->mapOfPairs([[$resource, 'val']]); // @phpstan-ignore argument.type, argument.templateType
			$map->count(); // Force lazy initialization
		} finally {
			fclose($resource); /** @phpstan-ignore argument.type */
		}
	}

	#[Test]
	public function not_preserved_because_of_invalid_input(): void
	{
		/** @phpstan-ignore-next-line */
		$map = $this->mapOf(['1' => 'a', true => 't', false => 'f', '' => 'n']);

		$this->assertFalse($map->containsKey('1')); // '1' became int 1
		$this->assertTrue($map->containsKey(1));
		$this->assertSame('t', $map->get(1));
		$this->assertTrue($map->containsKey(0));
		$this->assertSame('f', $map->get(0));
		$this->assertTrue($map->containsKey(''));
		$this->assertSame('n', $map->get(''));

		$this->assertSame([
			1 => 't',
			0 => 'f',
			'' => 'n',
		], $map->toArray());
	}
}
