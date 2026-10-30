<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Exception;

use Throwable;

/**
 * Thrown when an iterable that can only be walked once is walked again.
 */
final class NonReplayableSourceException extends SourceException
{
    public static function sequenceSourceAlreadyIterated(): self
    {
        return new self(
            'This sequence is backed by a non-replayable source and has already been iterated. Create a new sequence from a fresh source to iterate again.',
        );
    }

    public static function constrainedOnceSequenceAlreadyIterated(): self
    {
        return new self(
            'This sequence was constrained to a single pass and has already been iterated. Create a new sequence to iterate again.',
        );
    }

    public static function zippedIterableAlreadyIterated(): self
    {
        return new self(
            'The iterable passed to zip() is a non-replayable cursor and has already been consumed. Zip an array or an IteratorAggregate to iterate the result more than once.',
        );
    }

    public static function zippedIterableCannotRewind(Throwable $cause): self
    {
        return new self(
            'The iterable passed to zip() could not be rewound, so it cannot be walked from its start.',
            previous: $cause,
        );
    }

    public static function sourceReturnedSameIterator(): self
    {
        return new self(
            'The sequence\'s source returned the same iterator instance again - a source closure or an IteratorAggregate must produce a fresh iterator on each pass.',
        );
    }
}
