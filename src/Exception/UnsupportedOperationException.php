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

/**
 * Thrown when a mutating method is called on an immutable collection/map or when a
 * requested operation is not valid in the current state (e.g., removing from an empty list).
 */
class UnsupportedOperationException extends LogicException
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
