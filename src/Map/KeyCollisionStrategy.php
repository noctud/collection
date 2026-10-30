<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Map;

enum KeyCollisionStrategy
{
    case Throw;
    case KeepFirst;
    case KeepLast;
}
