<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List\Case;

use Noctud\Collection\Tests\Collection\Case\AbstractCollectionTestCase;
use Noctud\Collection\Tests\Collection\CollectionMutateWrite;
use Noctud\Collection\Tests\Collection\List\ListAccess;
use Noctud\Collection\Tests\Collection\List\ListConvert;
use Noctud\Collection\Tests\Collection\List\ListCopyOnWrite;
use Noctud\Collection\Tests\Collection\List\ListIndex;
use Noctud\Collection\Tests\Collection\List\ListMutate;

abstract class AbstractListTestCase extends AbstractCollectionTestCase
{
    use CollectionMutateWrite;
    use ListAccess;
    use ListConvert;
    use ListCopyOnWrite;
    use ListIndex;
    use ListMutate;
}
