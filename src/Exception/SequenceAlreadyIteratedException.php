<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Exception;

/**
 * Thrown when a sequence backed by a non-replayable source is iterated a second time,
 * or when a sequence source - a closure or an IteratorAggregate - hands back the same
 * iterator instance twice.
 */
final class SequenceAlreadyIteratedException extends UnsupportedOperationException
{
	public static function nonReplayableSourceAlreadyIterated(): self
	{
		return new self(
			'This sequence is backed by a non-replayable source and has already been iterated. Create a new sequence from a fresh source to iterate again.',
		);
	}

	public static function sourceReturnedSameIterator(): self
	{
		return new self(
			'The sequence\'s source returned the same iterator instance again - a source closure or an IteratorAggregate must produce a fresh iterator on each pass.',
		);
	}
}
