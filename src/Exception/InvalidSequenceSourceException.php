<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Exception;

use LogicException;

/**
 * Thrown when a sequence source closure returns a non-iterable value.
 */
final class InvalidSequenceSourceException extends LogicException
{
	public static function closureReturnedNonIterable(mixed $produced): self
	{
		return new self(
			sprintf('The sequence\'s source closure must return an iterable, got %s.', get_debug_type($produced)),
		);
	}
}
