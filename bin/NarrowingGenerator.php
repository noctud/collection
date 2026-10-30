<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Bin;

/**
 * Simple generator that extracts methods from a source interface and narrows their return types.
 * Inserts the narrowed methods into a target file between marker comments.
 */
final class NarrowingGenerator
{
    private const StartMarker = '// --- Narrowing Start (auto-generated) ---';
    private const EndMarker = '// --- Narrowing End (auto-generated) ---';

    public const SelfPreservingStartMarker = '// --- Self-Preserving Start (auto-generated) ---';
    public const SelfPreservingEndMarker = '// --- Self-Preserving End (auto-generated) ---';

    /**
     * Extract a method (PHPDoc + attributes + signature) from content by name.
     * Returns null if not found.
     */
    public static function extractMethod(string $content, string $methodName): ?string
    {
        // Match: optional PHPDoc + optional attributes + method signature ending with ;
        // The attribute sub-pattern tolerates an inline trailing comment after an attribute
        // (e.g. `#[NoDiscard] // @phpstan-ignore generics.notSubtype`). Without the
        // `\h*(?:\/\/.*)?` the match would stop at the comment, drop the PHPDoc + attribute,
        // and emit a bare signature (this is what mangled `flip` before).
        $pattern = '/( {4}\/\*\*(?:[^*]|\*(?!\/))*\*\/\s*)?(\s*(?:#\[[^\]]+\]\h*(?:\/\/.*)?\s*)*)public\s+function\s+'
            . preg_quote($methodName, '/')
            . '\s*\([^)]*\)[^;]*;/';

        if (preg_match($pattern, $content, $matches)) {
            // Preserve the indentation for PHPDoc
            $method = $matches[0];
            // Ensure each method starts indented (for PHPDoc)
            if (!str_starts_with($method, '    ')) {
                $method = '    ' . $method;
            }

            return $method;
        }

        return null;
    }

    /**
     * Extract all methods that have a specific return type pattern.
     * Returns array of method strings.
     *
     * @return array<string, string> Method name => full method string
     */
    public static function extractMethodsByReturnType(string $content, string $returnTypePattern): array
    {
        $methods = [];

        // Find all method names with the given return type
        $pattern = '/public\s+function\s+(\w+)\s*\([^)]*\)\s*:\s*' . preg_quote($returnTypePattern, '/') . '\s*;/';

        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[1] as $methodName) {
                $method = self::extractMethod($content, $methodName);
                if ($method !== null) {
                    $methods[$methodName] = $method;
                }
            }
        }

        return $methods;
    }

    /**
     * Replace types in method string.
     *
     * @param array<string, string> $replacements
     */
    public static function replaceTypes(string $method, array $replacements): string
    {
        foreach ($replacements as $from => $to) {
            $method = str_replace($from, $to, $method);
        }

        // Convert @stan-ignore-next-line
        $method = str_replace('@stan-ignore-next-line', '@phpstan-ignore-next-line', $method);

        return $method;
    }

    /**
     * Read the content between markers from a file.
     * Returns null if markers not found.
     */
    public static function readBetweenMarkers(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $startPos = strpos($content, self::StartMarker);
        $endPos = strpos($content, self::EndMarker);

        if ($startPos === false || $endPos === false) {
            return null;
        }

        $startPos += strlen(self::StartMarker);

        return substr($content, $startPos, $endPos - $startPos);
    }

    /**
     * Replace content between markers in a file.
     */
    public static function writeBetweenMarkers(
        string $filePath,
        string $newContent,
        string $startMarker = self::StartMarker,
        string $endMarker = self::EndMarker,
    ): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $startPos = strpos($content, $startMarker);
        $endPos = strpos($content, $endMarker);

        if ($startPos === false || $endPos === false) {
            echo "Markers not found in $filePath\n";
            return false;
        }

        $before = substr($content, 0, $startPos + strlen($startMarker));
        $after = substr($content, $endPos);

        $result = $before . "\n\n" . $newContent . "\n\n    " . $after;

        file_put_contents($filePath, $result);
        echo "Updated: $filePath\n";

        return true;
    }

    /**
     * Generic narrowing generation. Extracts methods by return type, applies replacements,
     * and optionally handles special methods with custom replacements.
     *
     * @param string $sourceContent Source interface content to extract methods from
     * @param string $extractReturnType Return type to match when extracting methods
     * @param array<string, string> $replacements Type replacements to apply
     * @param array<string> $blacklist Method names to exclude
     * @param array<string, array<string, string>>|null $specialMethods Method name => custom replacements
     * @return string The generated method declarations
     */
    public static function generateNarrowing(
        string $sourceContent,
        string $extractReturnType,
        array $replacements,
        array $blacklist = [],
        ?array $specialMethods = null,
    ): string
    {
        $methods = self::extractMethodsByReturnType($sourceContent, $extractReturnType);
        $methods = array_diff_key($methods, array_flip($blacklist));

        $output = [];
        foreach ($methods as $method) {
            $output[] = self::replaceTypes($method, $replacements);
        }

        if ($specialMethods !== null) {
            foreach ($specialMethods as $methodName => $customReplacements) {
                if (in_array($methodName, $blacklist, true)) {
                    continue;
                }
                $method = self::extractMethod($sourceContent, $methodName);
                if ($method !== null) {
                    $output[] = self::replaceTypes($method, $customReplacements);
                }
            }
        }

        return implode("\n\n", $output);
    }

    /**
     * Generate narrowed methods for Set from Collection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateSetNarrowing(string $collectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($collectionContent, 'Collection', [
            ': Collection' => ': Set',
            'Collection<E>' => 'Set<E>',
            'Collection<(E is null ? never : E)>' => 'Set<(E is null ? never : E)>',
            'Collection<(E is iterable<mixed> ? value-of<E|array{}> : E)>' => 'Set<(E is iterable<mixed> ? value-of<E|array{}> : E)>',
            'Collection<R>' => 'Set<R>',
            'Collection<mixed>' => 'Set<mixed>',
            'Collection<T>' => 'Set<T>',
        ], $blacklist, [
            'partition' => [
                'array{Collection<E>, Collection<E>}' => 'array{Set<E>, Set<E>}',
            ],
            'groupBy' => [
                'ImmutableMap<K, Collection<E>>' => 'ImmutableMap<K, Set<E>>',
                '@stan-ignore-next-line' => '@phpstan-ignore-next-line',
            ],
            'toImmutable' => [
                ': ImmutableCollection' => ': ImmutableSet',
                'ImmutableCollection<E>' => 'ImmutableSet<E>',
            ],
        ]);
    }

    /**
     * Generate narrowed methods for ImmutableSet from ImmutableCollection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateImmutableSetNarrowing(string $immutableCollectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($immutableCollectionContent, 'ImmutableCollection', [
            ': ImmutableCollection' => ': ImmutableSet',
            'ImmutableCollection<E>' => 'ImmutableSet<E>',
            'ImmutableCollection<(E is null ? never : E)>' => 'ImmutableSet<(E is null ? never : E)>',
            'ImmutableCollection<(E is iterable<mixed> ? value-of<E|array{}> : E)>' => 'ImmutableSet<(E is iterable<mixed> ? value-of<E|array{}> : E)>',
            'ImmutableCollection<E|NE>' => 'ImmutableSet<E|NE>',
            'ImmutableCollection<R>' => 'ImmutableSet<R>',
            'ImmutableCollection<mixed>' => 'ImmutableSet<mixed>',
            'ImmutableCollection<T>' => 'ImmutableSet<T>',
        ], $blacklist, [
            'partition' => [
                'array{ImmutableCollection<E>, ImmutableCollection<E>}' => 'array{ImmutableSet<E>, ImmutableSet<E>}',
            ],
            'groupBy' => [
                'ImmutableMap<K, ImmutableCollection<E>>' => 'ImmutableMap<K, ImmutableSet<E>>',
                '@stan-ignore-next-line' => '@phpstan-ignore-next-line',
            ],
        ]);
    }

    /**
     * Generate narrowed methods for WritableSet from WritableCollection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateWritableSetNarrowing(string $writableCollectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($writableCollectionContent, 'WritableCollection', [
            ': WritableCollection' => ': WritableSet',
            'WritableCollection<E>' => 'WritableSet<E>',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for MutableSet from MutableCollection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateMutableSetNarrowing(string $mutableCollectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($mutableCollectionContent, 'MutableCollection', [
            ': MutableCollection' => ': MutableSet',
            'MutableCollection<E>' => 'MutableSet<E>',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for ListInterface from Collection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateListNarrowing(string $collectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($collectionContent, 'Collection', [
            ': Collection' => ': ListInterface',
            'Collection<E>' => 'ListInterface<E>',
            'Collection<(E is null ? never : E)>' => 'ListInterface<(E is null ? never : E)>',
            'Collection<(E is iterable<mixed> ? value-of<E|array{}> : E)>' => 'ListInterface<(E is iterable<mixed> ? value-of<E|array{}> : E)>',
            'Collection<R>' => 'ListInterface<R>',
            'Collection<mixed>' => 'ListInterface<mixed>',
            'Collection<T>' => 'ListInterface<T>',
        ], $blacklist, [
            'partition' => [
                'array{Collection<E>, Collection<E>}' => 'array{ListInterface<E>, ListInterface<E>}',
            ],
            'groupBy' => [
                'ImmutableMap<K, Collection<E>>' => 'ImmutableMap<K, ListInterface<E>>',
                '@stan-ignore-next-line' => '@phpstan-ignore-next-line',
            ],
            'toImmutable' => [
                ': ImmutableCollection' => ': ImmutableList',
                'ImmutableCollection<E>' => 'ImmutableList<E>',
            ],
        ]);
    }

    /**
     * Generate narrowed methods for ImmutableCollection from Collection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateImmutableCollectionNarrowing(string $collectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($collectionContent, 'Collection', [
            ': Collection' => ': ImmutableCollection',
            'Collection<E>' => 'ImmutableCollection<E>',
            'Collection<(E is null ? never : E)>' => 'ImmutableCollection<(E is null ? never : E)>',
            'Collection<(E is iterable<mixed> ? value-of<E|array{}> : E)>' => 'ImmutableCollection<(E is iterable<mixed> ? value-of<E|array{}> : E)>',
            'Collection<R>' => 'ImmutableCollection<R>',
            'Collection<mixed>' => 'ImmutableCollection<mixed>',
            'Collection<T>' => 'ImmutableCollection<T>',
        ], $blacklist, [
            'partition' => [
                'array{Collection<E>, Collection<E>}' => 'array{ImmutableCollection<E>, ImmutableCollection<E>}',
            ],
            'groupBy' => [
                'ImmutableMap<K, Collection<E>>' => 'ImmutableMap<K, ImmutableCollection<E>>',
                '@stan-ignore-next-line' => '@phpstan-ignore-next-line',
            ],
        ]);
    }

    /**
     * Generate transformation narrowing for WritableCollection from Collection.
     * Narrows read-only transformation methods to return ImmutableCollection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateWritableCollectionTransformationNarrowing(string $collectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($collectionContent, 'Collection', [
            ': Collection' => ': ImmutableCollection',
            'Collection<E>' => 'ImmutableCollection<E>',
            'Collection<(E is null ? never : E)>' => 'ImmutableCollection<(E is null ? never : E)>',
            'Collection<(E is iterable<mixed> ? value-of<E|array{}> : E)>' => 'ImmutableCollection<(E is iterable<mixed> ? value-of<E|array{}> : E)>',
            'Collection<R>' => 'ImmutableCollection<R>',
            'Collection<mixed>' => 'ImmutableCollection<mixed>',
            'Collection<T>' => 'ImmutableCollection<T>',
        ], $blacklist, [
            'partition' => [
                'array{Collection<E>, Collection<E>}' => 'array{ImmutableCollection<E>, ImmutableCollection<E>}',
            ],
            'groupBy' => [
                'ImmutableMap<K, Collection<E>>' => 'ImmutableMap<K, ImmutableCollection<E>>',
                '@stan-ignore-next-line' => '@phpstan-ignore-next-line',
            ],
        ]);
    }

    /**
     * Generate transformation narrowing for WritableList from Collection.
     * Narrows read-only transformation methods to return ImmutableList.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateWritableListTransformationNarrowing(string $collectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($collectionContent, 'Collection', [
            ': Collection' => ': ImmutableList',
            'Collection<E>' => 'ImmutableList<E>',
            'Collection<(E is null ? never : E)>' => 'ImmutableList<(E is null ? never : E)>',
            'Collection<(E is iterable<mixed> ? value-of<E|array{}> : E)>' => 'ImmutableList<(E is iterable<mixed> ? value-of<E|array{}> : E)>',
            'Collection<R>' => 'ImmutableList<R>',
            'Collection<mixed>' => 'ImmutableList<mixed>',
            'Collection<T>' => 'ImmutableList<T>',
        ], $blacklist, [
            'partition' => [
                'array{Collection<E>, Collection<E>}' => 'array{ImmutableList<E>, ImmutableList<E>}',
            ],
            'groupBy' => [
                'ImmutableMap<K, Collection<E>>' => 'ImmutableMap<K, ImmutableList<E>>',
                '@stan-ignore-next-line' => '@phpstan-ignore-next-line',
            ],
        ]);
    }

    /**
     * Generate transformation narrowing for WritableSet from Collection.
     * Narrows read-only transformation methods to return ImmutableSet.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateWritableSetTransformationNarrowing(string $collectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($collectionContent, 'Collection', [
            ': Collection' => ': ImmutableSet',
            'Collection<E>' => 'ImmutableSet<E>',
            'Collection<(E is null ? never : E)>' => 'ImmutableSet<(E is null ? never : E)>',
            'Collection<(E is iterable<mixed> ? value-of<E|array{}> : E)>' => 'ImmutableSet<(E is iterable<mixed> ? value-of<E|array{}> : E)>',
            'Collection<R>' => 'ImmutableSet<R>',
            'Collection<mixed>' => 'ImmutableSet<mixed>',
            'Collection<T>' => 'ImmutableSet<T>',
        ], $blacklist, [
            'partition' => [
                'array{Collection<E>, Collection<E>}' => 'array{ImmutableSet<E>, ImmutableSet<E>}',
            ],
            'groupBy' => [
                'ImmutableMap<K, Collection<E>>' => 'ImmutableMap<K, ImmutableSet<E>>',
                '@stan-ignore-next-line' => '@phpstan-ignore-next-line',
            ],
        ]);
    }

    /**
     * Generate transformation narrowing for WritableMap from Map.
     * Narrows read-only transformation methods to return ImmutableMap.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateWritableMapTransformationNarrowing(string $mapContent, array $blacklist = []): string
    {
        return self::generateNarrowing($mapContent, 'Map', [
            ': Map' => ': ImmutableMap',
            'Map<K,V>' => 'ImmutableMap<K,V>',
            'Map<K, V>' => 'ImmutableMap<K, V>',
            'Map<K, (V is null ? never : V)>' => 'ImmutableMap<K, (V is null ? never : V)>',
            'Map<NK,V>' => 'ImmutableMap<NK,V>',
            'Map<NK, V>' => 'ImmutableMap<NK, V>',
            'Map<K,NV>' => 'ImmutableMap<K,NV>',
            'Map<K, NV>' => 'ImmutableMap<K, NV>',
            'Map<V,K>' => 'ImmutableMap<V,K>',
            'Map<V, K>' => 'ImmutableMap<V, K>',
            'Map<K,T>' => 'ImmutableMap<K,T>',
            'Map<K, T>' => 'ImmutableMap<K, T>',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for ImmutableList from ImmutableCollection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateImmutableListNarrowing(string $immutableCollectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($immutableCollectionContent, 'ImmutableCollection', [
            ': ImmutableCollection' => ': ImmutableList',
            'ImmutableCollection<E>' => 'ImmutableList<E>',
            'ImmutableCollection<(E is null ? never : E)>' => 'ImmutableList<(E is null ? never : E)>',
            'ImmutableCollection<(E is iterable<mixed> ? value-of<E|array{}> : E)>' => 'ImmutableList<(E is iterable<mixed> ? value-of<E|array{}> : E)>',
            'ImmutableCollection<E|NE>' => 'ImmutableList<E|NE>',
            'ImmutableCollection<R>' => 'ImmutableList<R>',
            'ImmutableCollection<mixed>' => 'ImmutableList<mixed>',
            'ImmutableCollection<T>' => 'ImmutableList<T>',
        ], $blacklist, [
            'partition' => [
                'array{ImmutableCollection<E>, ImmutableCollection<E>}' => 'array{ImmutableList<E>, ImmutableList<E>}',
            ],
            'groupBy' => [
                'ImmutableMap<K, ImmutableCollection<E>>' => 'ImmutableMap<K, ImmutableList<E>>',
                '@stan-ignore-next-line' => '@phpstan-ignore-next-line',
            ],
        ]);
    }

    /**
     * Generate narrowed methods for WritableList from WritableCollection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateWritableListNarrowing(string $writableCollectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($writableCollectionContent, 'WritableCollection', [
            ': WritableCollection' => ': WritableList',
            'WritableCollection<E>' => 'WritableList<E>',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for MutableList from MutableCollection and WritableList.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateMutableListNarrowing(string $mutableCollectionContent, array $blacklist = [], string $writableListContent = ''): string
    {
        // Extract methods returning MutableCollection
        $methods = self::extractMethodsByReturnType($mutableCollectionContent, 'MutableCollection');

        // Filter out blacklisted methods
        $methods = array_diff_key($methods, array_flip($blacklist));

        // Type replacements
        $replacements = [
            ': MutableCollection' => ': MutableList',
            'MutableCollection<E>' => 'MutableList<E>',
        ];

        $output = [];
        foreach ($methods as $name => $method) {
            $output[] = self::replaceTypes($method, $replacements);
        }

        // Also narrow WritableList-specific methods (set, removeEvery, removeAt)
        // Skip methods already narrowed from MutableCollection to avoid duplicates
        if ($writableListContent !== '') {
            $writableListMethods = self::extractMethodsByReturnType($writableListContent, 'WritableList');
            $writableListMethods = array_diff_key($writableListMethods, array_flip($blacklist));
            $writableListMethods = array_diff_key($writableListMethods, $methods);

            $writableListReplacements = [
                ': WritableList' => ': MutableList',
                'WritableList<E>' => 'MutableList<E>',
            ];

            foreach ($writableListMethods as $name => $method) {
                $output[] = self::replaceTypes($method, $writableListReplacements);
            }
        }

        return implode("\n\n", $output);
    }

    /**
     * Generate narrowed methods for MutableMap from WritableMap.
     * Narrows mutation methods that return WritableMap to return MutableMap.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateMutableMapNarrowing(string $writableMapContent, array $blacklist = []): string
    {
        return self::generateNarrowing($writableMapContent, 'WritableMap', [
            ': WritableMap' => ': MutableMap',
            'WritableMap<K,V>' => 'MutableMap<K,V>',
            'WritableMap<K, V>' => 'MutableMap<K, V>',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for ImmutableMap from Map.
     * Narrows transformation and ordering methods that return Map to return ImmutableMap.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateImmutableMapNarrowing(string $mapContent, array $blacklist = []): string
    {
        return self::generateNarrowing($mapContent, 'Map', [
            ': Map' => ': ImmutableMap',
            'Map<K,V>' => 'ImmutableMap<K,V>',
            'Map<K, V>' => 'ImmutableMap<K, V>',
            'Map<K, (V is null ? never : V)>' => 'ImmutableMap<K, (V is null ? never : V)>',
            'Map<NK,V>' => 'ImmutableMap<NK,V>',
            'Map<NK, V>' => 'ImmutableMap<NK, V>',
            'Map<K,NV>' => 'ImmutableMap<K,NV>',
            'Map<K, NV>' => 'ImmutableMap<K, NV>',
            'Map<V,K>' => 'ImmutableMap<V,K>',
            'Map<V, K>' => 'ImmutableMap<V, K>',
            'Map<K,T>' => 'ImmutableMap<K,T>',
            'Map<K, T>' => 'ImmutableMap<K, T>',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for MutableCollection from WritableCollection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateMutableCollectionNarrowing(string $writableCollectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($writableCollectionContent, 'WritableCollection', [
            ': WritableCollection' => ': MutableCollection',
            'WritableCollection<E>' => 'MutableCollection<E>',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for WritableTrackedCollection from WritableCollection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateWritableTrackedCollectionNarrowing(string $writableCollectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($writableCollectionContent, 'WritableCollection', [
            ': WritableCollection' => ': WritableTrackedCollection&TrackedResult',
            'WritableCollection<E>' => 'WritableTrackedCollection<E>&TrackedResult',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for MutableTrackedCollection from MutableCollection.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateTrackedCollectionNarrowing(string $mutableCollectionContent, array $blacklist = []): string
    {
        return self::generateNarrowing($mutableCollectionContent, 'MutableCollection', [
            ': MutableCollection' => ': MutableTrackedCollection&TrackedResult',
            'MutableCollection<E>' => 'MutableTrackedCollection<E>&TrackedResult',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for WritableTrackedList from WritableList.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateWritableTrackedListNarrowing(string $writableListContent, array $blacklist = []): string
    {
        return self::generateNarrowing($writableListContent, 'WritableList', [
            ': WritableList' => ': WritableTrackedList&TrackedResult',
            'WritableList<E>' => 'WritableTrackedList<E>&TrackedResult',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for MutableTrackedList from MutableList.
     * Source must be the updated MutableList content (after its own narrowing is generated).
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateTrackedListNarrowing(string $mutableListContent, array $blacklist = []): string
    {
        return self::generateNarrowing($mutableListContent, 'MutableList', [
            ': MutableList' => ': MutableTrackedList&TrackedResult',
            'MutableList<E>' => 'MutableTrackedList<E>&TrackedResult',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for WritableTrackedSet from WritableSet.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateWritableTrackedSetNarrowing(string $writableSetContent, array $blacklist = []): string
    {
        return self::generateNarrowing($writableSetContent, 'WritableSet', [
            ': WritableSet' => ': WritableTrackedSet&TrackedResult',
            'WritableSet<E>' => 'WritableTrackedSet<E>&TrackedResult',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for MutableTrackedSet from MutableSet.
     * Source must be the updated MutableSet content (after its own narrowing is generated).
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateTrackedSetNarrowing(string $mutableSetContent, array $blacklist = []): string
    {
        return self::generateNarrowing($mutableSetContent, 'MutableSet', [
            ': MutableSet' => ': MutableTrackedSet&TrackedResult',
            'MutableSet<E>' => 'MutableTrackedSet<E>&TrackedResult',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for WritableTrackedMap from WritableMap.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateWritableTrackedMapNarrowing(string $writableMapContent, array $blacklist = []): string
    {
        return self::generateNarrowing($writableMapContent, 'WritableMap', [
            ': WritableMap' => ': WritableTrackedMap&TrackedResult',
            'WritableMap<K,V>' => 'WritableTrackedMap<K,V>&TrackedResult',
            'WritableMap<K, V>' => 'WritableTrackedMap<K, V>&TrackedResult',
        ], $blacklist);
    }

    /**
     * Generate narrowed methods for MutableTrackedMap from MutableMap.
     *
     * @param array<string> $blacklist Methods to exclude
     * @return string The generated method declarations
     */
    public static function generateTrackedMapNarrowing(string $mutableMapContent, array $blacklist = []): string
    {
        return self::generateNarrowing($mutableMapContent, 'MutableMap', [
            ': MutableMap' => ': MutableTrackedMap&TrackedResult',
            'MutableMap<K,V>' => 'MutableTrackedMap<K,V>&TrackedResult',
            'MutableMap<K, V>' => 'MutableTrackedMap<K, V>&TrackedResult',
        ], $blacklist);
    }

    /**
     * Generate the body (between Self-Preserving markers) of a SelfPreserving*Logic trait.
     *
     * Extracts every method on the source interface that natively returns $collectionType,
     * drops $blacklist (type-changing methods), and emits a `: static`-returning override
     * that delegates to an aliased copy of the base implementation. `partition` (when not
     * blacklisted) becomes `array{static, static}`. Mutation widening (`NE`/`NK`/`NV`) is
     * rewritten to the strict `E`/`K`/`V`. The `@phpstan-ignore return.type` on each body is
     * sound because the bundled factory returns `new static(...)`.
     *
     * @param array<string> $blacklist Method names to exclude from the auto-extracted set
     * @param array<string> $arrayMethods Methods returning `array{static, static}` (e.g. partition), taken from $interfaceContent
     * @param array<string> $extraSimpleMethods Extra `: static` methods not returning $collectionType (e.g. Set's intersect/union/subtract), taken from $extraSource
     * @param string $templateParams Generic parameters of the using collection, e.g. `E` or `K,V`
     */
    public static function generateSelfPreserving(
        string $interfaceContent,
        string $extraSource,
        string $baseTrait,
        string $collectionType,
        string $factoryMethod,
        string $templateParams,
        array $blacklist,
        array $arrayMethods,
        array $extraSimpleMethods,
    ): string
    {
        $methods = self::extractMethodsByReturnType($interfaceContent, $collectionType);
        $methods = array_diff_key($methods, array_flip($blacklist));

        $aliases = [];
        $overrides = [];

        foreach ($methods as $name => $extracted) {
            $aliases[] = '        ' . $name . ' as private ' . $name . 'Impl;';
            $overrides[] = self::buildSelfPreservingOverride($extracted, $name, false);
        }

        foreach ($extraSimpleMethods as $name) {
            $extracted = self::extractMethod($extraSource, $name);
            if ($extracted !== null) {
                $aliases[] = '        ' . $name . ' as private ' . $name . 'Impl;';
                $overrides[] = self::buildSelfPreservingOverride($extracted, $name, false);
            }
        }

        foreach ($arrayMethods as $name) {
            $extracted = self::extractMethod($interfaceContent, $name);
            if ($extracted !== null) {
                $aliases[] = '        ' . $name . ' as private ' . $name . 'Impl;';
                $overrides[] = self::buildSelfPreservingOverride($extracted, $name, true);
            }
        }

        $useBlock = '    /** @use ' . $baseTrait . '<' . $templateParams . "> */\n"
            . '    use ' . $baseTrait . " {\n"
            . implode("\n", $aliases) . "\n"
            . '    }';

        $factory = "    /**\n"
            . "     * Builds derived instances as the using subtype.\n"
            . "     *\n"
            . '     * @param iterable<' . $templateParams . "> \$data\n"
            . "     * @return static\n"
            . "     */\n"
            . '    protected function ' . $factoryMethod . '(iterable $data): ' . $collectionType . "\n"
            . "    {\n"
            . "        return new static(\$data); // @phpstan-ignore return.type\n"
            . '    }';

        return $useBlock . "\n\n" . implode("\n\n", $overrides) . "\n\n" . $factory;
    }

    /**
     * Build one `: static`-returning delegating override from an extracted interface method.
     */
    private static function buildSelfPreservingOverride(string $extracted, string $name, bool $isArray): string
    {
        preg_match('/public\s+function\s+' . preg_quote($name, '/') . '\s*\(([^)]*)\)/', $extracted, $sigMatch);
        $params = trim($sigMatch[1] ?? '');

        preg_match_all('/\$\w+/', $params, $varMatch);
        $args = implode(', ', $varMatch[0]);

        // Collect @template (minus the NE/NK/NV widening ones) and @param tags.
        $tags = [];
        foreach (preg_split('/\R/', $extracted) ?: [] as $line) {
            $tag = ltrim($line, ' *');
            if (str_starts_with($tag, '@template') && !preg_match('/^@template\s+N[EKV]\b/', $tag)) {
                $tags[] = $tag;
            } elseif (str_starts_with($tag, '@param')) {
                $tags[] = $tag;
            }
        }

        // Rewrite mutation widening types to the strict element/key/value types.
        $tags = array_map(static function (string $tag): string {
            $tag = preg_replace('/\bNE\b/', 'E', $tag);
            $tag = preg_replace('/\bNK\b/', 'K', $tag);
            return preg_replace('/\bNV\b/', 'V', $tag) ?? $tag;
        }, $tags);

        $doc = "    /**\n     * {@inheritDoc}\n     *\n";
        foreach ($tags as $tag) {
            $doc .= '     * ' . $tag . "\n";
        }

        $doc .= '     * ' . ($isArray ? '@return array{static, static}' : '@return static') . "\n     */";

        return $doc . "\n"
            . "    #[NoDiscard]\n"
            . '    public function ' . $name . '(' . $params . '): ' . ($isArray ? 'array' : 'static') . "\n"
            . "    {\n"
            . '        return $this->' . $name . 'Impl(' . $args . '); // @phpstan-ignore return.type' . "\n"
            . '    }';
    }
}
