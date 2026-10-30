<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set\Case;

use Noctud\Collection\Tests\Collection\Case\AbstractCollectionTestCase;
use Noctud\Collection\Tests\Collection\Set\SetMutate;

abstract class AbstractSetTestCase extends AbstractCollectionTestCase
{
    use SetMutate;
}
