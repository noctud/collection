<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Exception;

use LogicException;
use Noctud\Collection\Collection;
use Noctud\Collection\Sequence\Sequence;
use function sprintf;
use function lcfirst;

/**
 * Thrown when an immutable list or map is written through array access, or when an operation
 * cannot run on the elements it is given (reducing or averaging an empty collection or sequence,
 * unzipping an element that is not a pair). Immutable mutators never throw it: they return a copy.
 */
class UnsupportedOperationException extends LogicException implements NoctudCollectionException
{
    use NamesItsSubject;

    /**
     * @param Collection<mixed>|Sequence<mixed> $subject
     */
    public static function cannotReduceEmptySubject(Collection|Sequence $subject): self
    {
        return new self(sprintf('Cannot reduce empty %s', lcfirst(self::subjectName($subject))));
    }

    /**
     * @param Collection<mixed>|Sequence<mixed> $subject
     */
    public static function cannotAverageEmptySubject(Collection|Sequence $subject): self
    {
        // Mid-sentence, so the noun is lowercased - which leaves the eager message untouched.
        return new self(sprintf('Cannot compute average of empty %s', lcfirst(self::subjectName($subject))));
    }
}
