<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Fixture;

final class DetailedHashableUser extends HashableUser
{
	public function __construct(
		string $id,
		public readonly string $email,
	) {
		parent::__construct($id);
	}
}
