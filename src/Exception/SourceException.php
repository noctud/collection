<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Exception;

use LogicException;

/**
 * Base of the failures raised when walking a source rather than by what a method does: a
 * source that refuses to hand back a fresh pass, or one that hands back something not
 * iterable.
 *
 * Methods that walk a source declare this single type rather than the concrete exceptions
 * behind it - which one surfaces depends on the source, not on the method called. It is a
 * class rather than an interface so that IDEs see a LogicException and treat it as
 * unchecked, like the other exceptions of the library.
 */
class SourceException extends LogicException implements NoctudCollectionException
{
}
