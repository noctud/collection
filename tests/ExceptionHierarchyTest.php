<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests;

use FilesystemIterator;
use Generator;
use LogicException;
use Noctud\Collection\Exception\NoctudCollectionException;
use Noctud\Collection\Exception\SourceException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;
use function Noctud\Collection\listOf;
use function Noctud\Collection\sequenceOf;

final class ExceptionHierarchyTest extends TestCase
{
	#[Test]
	public function a_source_that_cannot_replay_is_caught_as_a_sequence_failure(): void
	{
		$generator = (static function (): Generator {
			yield 1;
		})();
		$sequence = sequenceOf($generator);

		$this->assertSame([1], $sequence->toArray());

		$this->expectException(SourceException::class);

		$_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function a_source_returning_a_non_iterable_is_caught_as_a_sequence_failure(): void
	{
		/** @phpstan-ignore argument.type, argument.templateType */
		$sequence = sequenceOf(static fn (): int => 42);

		$this->expectException(SourceException::class);

		$_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function a_sequence_failure_is_also_caught_at_the_library_root(): void
	{
		/** @phpstan-ignore argument.type, argument.templateType */
		$sequence = sequenceOf(static fn (): int => 42);

		$this->expectException(NoctudCollectionException::class);

		$_ = $sequence->toArray(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	public function an_eager_failure_is_caught_at_the_library_root(): void
	{
		$this->expectException(NoctudCollectionException::class);

		$_ = listOf([])->first(); // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
	}

	#[Test]
	#[DataProvider('libraryExceptions')]
	public function every_library_exception_implements_the_root_marker(string $class): void
	{
		$this->assertTrue(
			is_subclass_of($class, NoctudCollectionException::class),
			sprintf('%s must implement %s', $class, NoctudCollectionException::class),
		);
	}

	#[Test]
	#[DataProvider('libraryExceptions')]
	public function every_library_exception_is_a_logic_exception(string $class): void
	{
		// IDEs treat LogicException as unchecked, so a @throws tag doesn't ask callers for a try-catch
		$this->assertTrue(
			is_subclass_of($class, LogicException::class),
			sprintf('%s must extend %s', $class, LogicException::class),
		);
	}

	/**
	 * @return Generator<string, array{class-string<Throwable>}>
	 */
	public static function libraryExceptions(): Generator
	{
		$src = dirname(__DIR__) . '/src';
		/** @var iterable<SplFileInfo> $files */
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));

		foreach ($files as $file) {
			if ($file->getExtension() !== 'php') {
				continue;
			}

			// PSR-4 in reverse: src/Exception/Foo.php -> Noctud\Collection\Exception\Foo
			$relative = substr($file->getPathname(), strlen($src) + 1, -4);
			$class = 'Noctud\\Collection\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relative);

			// class_exists() autoloads; interfaces, traits and functions.php fall out here
			if (class_exists($class) && is_subclass_of($class, Throwable::class)) {
				yield $class => [$class];
			}
		}
	}
}
