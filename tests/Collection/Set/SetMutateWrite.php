<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\Set;

use Generator;
use Noctud\Collection\Set\MutableSet;
use Noctud\Collection\Tests\Collection\Fixture\HashableUser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

trait SetMutateWrite
{
    /**
     * An element and a different value that hashes the same, which the set treats as that element.
     *
     * @return Generator<string, array{mixed, mixed}>
     */
    public static function sameHashProvider(): Generator
    {
        yield 'Hashable copy' => [new HashableUser('1'), new HashableUser('1')];
        yield 'float rounded to 12 decimals' => [0.1 + 0.2, 0.3];
    }

    #[Test]
    #[DataProvider('sameHashProvider')]
    public function removeAll_matches_elements_like_contains(mixed $element, mixed $sameHash): void
    {
        $set = $this->collectionOf([$element, 'other']);
        $this->assertTrue($set->contains($sameHash));

        if ($set instanceof MutableSet) {
            $tracked = $set->toMutable()->tracked();
            $this->assertSame($set, $set->removeAll([$sameHash]));
            $this->assertSame(['other'], $set->toArray());

            $result = $tracked->removeAll([$sameHash]);
            $this->assertTrue($result->changed);
            $this->assertSame(['other'], $result->toArray());
        } else {
            $this->assertSame(['other'], $set->removeAll([$sameHash])->toArray());
            $this->assertSame([$element, 'other'], $set->toArray());
        }
    }

    #[Test]
    #[DataProvider('sameHashProvider')]
    public function retainAll_keeps_elements_that_contains_reports(mixed $element, mixed $sameHash): void
    {
        $set = $this->collectionOf([$element, 'other']);

        if ($set instanceof MutableSet) {
            $tracked = $set->toMutable()->tracked();
            $this->assertSame($set, $set->retainAll([$sameHash]));
            $this->assertSame([$element], $set->toArray());

            $result = $tracked->retainAll([$sameHash]);
            $this->assertTrue($result->changed);
            $this->assertSame([$element], $result->toArray());
        } else {
            $this->assertSame([$element], $set->retainAll([$sameHash])->toArray());
            $this->assertSame([$element, 'other'], $set->toArray());
        }
    }

    #[Test]
    #[DataProvider('sameHashProvider')]
    public function construction_keeps_first_of_same_hash(mixed $element, mixed $sameHash): void
    {
        $set = $this->collectionOf([$element, 'other', $sameHash]);

        $this->assertSame([$element, 'other'], $set->toArray());
    }

    #[Test]
    #[DataProvider('sameHashProvider')]
    public function add_keeps_stored_element_of_same_hash(mixed $element, mixed $sameHash): void
    {
        $set = $this->collectionOf([$element, 'other']);

        if ($set instanceof MutableSet) {
            $result = $set->tracked()->add($sameHash);
            $this->assertFalse($result->changed);
            $this->assertSame([$element, 'other'], $set->toArray());

            $this->assertSame($set, $set->add($sameHash));
            $this->assertSame([$element, 'other'], $set->toArray());
        } else {
            $this->assertSame([$element, 'other'], $set->add($sameHash)->toArray());
        }
    }

    #[Test]
    #[DataProvider('sameHashProvider')]
    public function addAll_keeps_stored_element_of_same_hash(mixed $element, mixed $sameHash): void
    {
        $set = $this->collectionOf([$element]);

        if ($set instanceof MutableSet) {
            $result = $set->tracked()->addAll([$sameHash]);
            $this->assertFalse($result->changed);
            $this->assertSame([$element], $set->toArray());

            $result = $set->tracked()->addAll([$sameHash, 'other']);
            $this->assertTrue($result->changed);
            $this->assertSame([$element, 'other'], $set->toArray());
        } else {
            $this->assertSame([$element, 'other'], $set->addAll([$sameHash, 'other'])->toArray());
        }
    }

    #[Test]
    #[DataProvider('sameHashProvider')]
    public function addFirst_moves_stored_element_of_same_hash(mixed $element, mixed $sameHash): void
    {
        $set = $this->collectionOf(['other', $element]);

        if ($set instanceof MutableSet) {
            $tracked = $set->tracked();

            $result = $tracked->addFirst($sameHash);
            $this->assertTrue($result->changed);
            $this->assertSame([$element, 'other'], $set->toArray());

            $result = $tracked->addFirst($sameHash);
            $this->assertFalse($result->changed);
            $this->assertSame([$element, 'other'], $set->toArray());
        } else {
            $this->assertSame([$element, 'other'], $set->addFirst($sameHash)->toArray());
            $this->assertSame([$element, 'other'], $set->addFirst($element)->addFirst($sameHash)->toArray());
        }
    }

    #[Test]
    public function uniqueness_enforced_on_add(): void
    {
        $set = $this->collectionOf([1, 2, 3]);

        if ($set instanceof MutableSet) {
            $tracked = $set->tracked();
            $result = $tracked->add(2);
            $this->assertSame([1, 2, 3], $set->toArray());
            $this->assertFalse($result->changed);

            $result = $tracked->add(4);
            $this->assertSame([1, 2, 3, 4], $set->toArray());
            $this->assertTrue($result->changed);
        } else {
            $result = $set->add(2);
            $this->assertSame([1, 2, 3], $result->toArray());

            $result = $set->add(4);
            $this->assertSame([1, 2, 3, 4], $result->toArray());
        }
    }

    #[Test]
    public function addFirst_moves_existing_to_front(): void
    {
        $set = $this->collectionOf([1, 2, 3]);

        if ($set instanceof MutableSet) {
            $tracked = $set->tracked();
            $result = $tracked->addFirst(3);
            $this->assertSame([3, 1, 2], $set->toArray());
            $this->assertTrue($result->changed);
        } else {
            $result = $set->addFirst(3);
            $this->assertSame([3, 1, 2], $result->toArray());
            $this->assertSame([1, 2, 3], $set->toArray());
        }
    }

    #[Test]
    public function addFirst_noop_when_already_first(): void
    {
        $set = $this->collectionOf([1, 2, 3]);

        if ($set instanceof MutableSet) {
            $tracked = $set->tracked();
            $result = $tracked->addFirst(1);
            $this->assertSame([1, 2, 3], $set->toArray());
            $this->assertFalse($result->changed);
        } else {
            $result = $set->addFirst(1);
            $this->assertSame([1, 2, 3], $result->toArray());
        }
    }

    #[Test]
    public function addFirst_adds_new_element_at_front(): void
    {
        $set = $this->collectionOf([1, 2, 3]);

        if ($set instanceof MutableSet) {
            $tracked = $set->tracked();
            $result = $tracked->addFirst(0);
            $this->assertSame([0, 1, 2, 3], $set->toArray());
            $this->assertTrue($result->changed);
        } else {
            $result = $set->addFirst(0);
            $this->assertSame([0, 1, 2, 3], $result->toArray());
        }
    }

    #[Test]
    public function addFirst_preserves_uniqueness(): void
    {
        $set = $this->collectionOf([1, 2, 3]);

        if ($set instanceof MutableSet) {
            $this->assertSame($set, $set->addFirst(2));
            $this->assertSame(3, $set->count());
            $this->assertSame([2, 1, 3], $set->toArray());
        } else {
            $result = $set->addFirst(2);
            $this->assertSame(3, $result->count());
            $this->assertSame([2, 1, 3], $result->toArray());
        }
    }

    #[Test]
    public function uniqueness_enforced_on_addAll(): void
    {
        $set = $this->collectionOf([1, 2, 3]);

        if ($set instanceof MutableSet) {
            $tracked = $set->tracked();
            $result = $tracked->addAll([2, 3, 4, 5]);
            $this->assertSame([1, 2, 3, 4, 5], $set->toArray());
            $this->assertTrue($result->changed);
        } else {
            $result = $set->addAll([2, 3, 4, 5]);
            $this->assertSame([1, 2, 3, 4, 5], $result->toArray());
        }
    }

    #[Test]
    public function removeFirst_keeps_int_elements_findable(): void
    {
        $set = $this->collectionOf([5, 7, 9]);
        $result = $set->removeFirst();

        if (!$set instanceof MutableSet) {
            $this->assertTrue($set->contains(5));
        }

        $this->assertTrue($result->contains(7));
        $this->assertTrue($result->contains(9));
        $this->assertSame([7, 9], $result->add(7)->toArray());
    }
}
