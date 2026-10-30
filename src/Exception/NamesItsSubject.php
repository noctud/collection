<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Exception;

use Noctud\Collection\Collection;
use Noctud\Collection\Sequence\Sequence;

/**
 * Names what a message is about, so that a body shared by the eager and the lazy side does not
 * have to carry two versions of its own error text: a sequence must not be told a "Collection" is
 * empty, and that difference is the only thing that used to keep those bodies apart.
 *
 * @internal
 */
trait NamesItsSubject
{
    /**
     * @param Collection<mixed>|Sequence<mixed> $subject
     */
    private static function subjectName(Collection|Sequence $subject): string
    {
        // The parameter type makes this exhaustive, so the default arm *is* the Collection case
        // rather than a catch-all. Widening that union means adding an arm here.
        return match (true) {
            $subject instanceof Sequence => 'Sequence',
            default => 'Collection',
        };
    }
}
