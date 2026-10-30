<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection;

use Noctud\Collection\Exception\InvalidKeyTypeException;
use function is_int;
use function is_string;
use function is_null;
use function is_bool;
use function is_float;
use function sprintf;
use function is_object;
use function spl_object_id;
use function is_array;
use function hash;
use function serialize;
use function is_resource;
use function get_resource_id;
use function get_debug_type;

/**
 * @internal
 */
final class KeyHasher
{
    /**
     * @throws InvalidKeyTypeException
     */
    public static function hashSetKey(mixed $key): int|string
    {
        return match (true) {
            is_int($key) => $key,
            is_string($key) => "s:$key",
            is_null($key) => 'n:',
            is_bool($key) => 'b:' . ($key ? '1' : '0'),
            is_float($key) => 'f:' . sprintf('%.12F', $key),
            $key instanceof Hashable => "h:{$key->identity()}",
            is_object($key) => 'o:' . spl_object_id($key),
            is_array($key) => 'a:' . hash('xxh128', serialize($key)),
            is_resource($key) => 'r:' . get_resource_id($key),
            default => throw new InvalidKeyTypeException('Unsupported key type: ' . get_debug_type($key)),
        };
    }

    /**
     * @throws InvalidKeyTypeException
     */
    public static function hashMapKey(mixed $key): int|string
    {
        return match (true) {
            is_string($key) => "s:$key",
            is_int($key) => $key,
            $key instanceof Hashable => "h:{$key->identity()}",
            is_object($key) => 'o:' . spl_object_id($key),
            is_bool($key) => 'b:' . ($key ? '1' : '0'),
            is_float($key) => 'f:' . sprintf('%.12F', $key),
            is_array($key) => throw new InvalidKeyTypeException('Arrays are not supported as map keys.'),
            is_null($key) => throw new InvalidKeyTypeException('Null is not supported as a map key.'),
            is_resource($key) => throw new InvalidKeyTypeException('Resources are not supported as map keys.'),
            default => throw new InvalidKeyTypeException('Unsupported key type: ' . get_debug_type($key)),
        };
    }
}
