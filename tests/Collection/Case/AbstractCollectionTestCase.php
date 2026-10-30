<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Case;

use Noctud\Collection\Tests\Case\AbstractEnumerableTestCase;
use Noctud\Collection\Tests\Collection\CollectionAggregate;
use Noctud\Collection\Tests\Collection\CollectionCallbackIndex;
use Noctud\Collection\Tests\Collection\CollectionContains;
use Noctud\Collection\Tests\Collection\CollectionCount;
use Noctud\Collection\Tests\Collection\CollectionConvert;
use Noctud\Collection\Tests\Collection\CollectionFilter;
use Noctud\Collection\Tests\Collection\CollectionFirstLast;
use Noctud\Collection\Tests\Collection\CollectionForEach;
use Noctud\Collection\Tests\Collection\CollectionGroupAndZip;
use Noctud\Collection\Tests\Collection\CollectionMutate;
use Noctud\Collection\Tests\Collection\CollectionQuantifiers;
use Noctud\Collection\Tests\Collection\CollectionRandom;
use Noctud\Collection\Tests\Collection\CollectionReduce;
use Noctud\Collection\Tests\Collection\CollectionSingle;
use Noctud\Collection\Tests\Collection\CollectionSlice;
use Noctud\Collection\Tests\Collection\CollectionSort;
use Noctud\Collection\Tests\Collection\CollectionMapNotNull;
use Noctud\Collection\Tests\Collection\CollectionTransform;

abstract class AbstractCollectionTestCase extends AbstractEnumerableTestCase implements CollectionTestCase
{
    use CollectionAggregate;
    use CollectionCallbackIndex;
    use CollectionContains;
    use CollectionCount;
    use CollectionConvert;
    use CollectionFilter;
    use CollectionFirstLast;
    use CollectionForEach;
    use CollectionGroupAndZip;
    use CollectionMutate;
    use CollectionQuantifiers;
    use CollectionRandom;
    use CollectionReduce;
    use CollectionSingle;
    use CollectionSlice;
    use CollectionSort;
    use CollectionMapNotNull;
    use CollectionTransform;
}
