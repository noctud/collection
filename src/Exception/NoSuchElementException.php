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

final class NoSuchElementException extends LogicException implements NoctudCollectionException
{
    use NamesItsSubject;

    /**
     * @param Collection<mixed>|Sequence<mixed> $subject
     */
    public static function emptySubject(Collection|Sequence $subject): self
    {
        return new self(sprintf('%s is empty', self::subjectName($subject)));
    }

    /**
     * @param Collection<mixed>|Sequence<mixed> $subject
     */
    public static function subjectHasMoreThanOneElement(Collection|Sequence $subject): self
    {
        return new self(sprintf('%s contains more than one element', self::subjectName($subject)));
    }
}
