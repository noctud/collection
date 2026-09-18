<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Fixture;

use Noctud\Collection\Hashable;

class HashableUser implements Hashable
{
	public function __construct(
		private readonly string $id,
	) {
	}

	public function identity(): string
	{
		return "user:{$this->id}";
	}
}
