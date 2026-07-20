<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Sequence;

use Closure;

/**
 * Lazy sequence iterating through generators, whatever the source kind.
 * If the given source is a Closure, it is a producer: invoked once per pass and expected
 * to return a fresh iterable on each call.
 *
 * The class is final; if you want your own Sequence, use the SequenceLogic trait in your
 * own class; this way you are not tied to our class hierarchy (you can extend your own
 * base class).
 *
 * @template E
 * @implements Sequence<E>
 */
final class GeneratorSequence implements Sequence
{
	/** @use SequenceLogic<E> */
	use SequenceLogic;

	/**
	 * @param iterable<E>|Closure():iterable<E> $source
	 */
	public function __construct(iterable|Closure $source = [])
	{
		$this->source = $source;
	}
}
