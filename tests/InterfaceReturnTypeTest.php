<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests;

use FilesystemIterator;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionNamedType;
use SplFileInfo;
use function interface_exists;
use function is_subclass_of;
use function sprintf;
use function dirname;
use function substr;
use function strlen;
use function str_replace;

final class InterfaceReturnTypeTest extends TestCase
{
    /**
     * @param class-string $interface
     */
    #[Test]
    #[DataProvider('libraryInterfaces')]
    public function no_override_natively_returns_a_descendant_of_its_own_interface(string $interface): void
    {
        // Verifying such an override needs the descendant, which needs this interface linked first.
        // PHP defers the check, so opcache can't cache the linked interface or anything below it,
        // and some autoload orders fatal. Declare the parent's native type, the precise one in @return.
        $reflection = new ReflectionClass($interface);
        $offenders = [];
        foreach ($reflection->getMethods() as $method) {
            $type = $method->getReturnType();
            if ($method->class !== $interface || !$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            $returned = $type->getName();
            if (!is_subclass_of($returned, $interface)) {
                continue;
            }

            foreach ($reflection->getInterfaces() as $parent) {
                if ($parent->hasMethod($method->name)) {
                    $offenders[] = sprintf('%s(): %s overrides %s', $method->name, $returned, $parent->name);
                    break;
                }
            }
        }

        $this->assertSame([], $offenders);
    }

    /**
     * @return Generator<string, array{class-string}>
     */
    public static function libraryInterfaces(): Generator
    {
        $src = dirname(__DIR__) . '/src';
        /** @var iterable<SplFileInfo> $files */
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($src) + 1, -4);
            $interface = 'Noctud\\Collection\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relative);

            if (interface_exists($interface)) {
                yield $interface => [$interface];
            }
        }
    }
}
