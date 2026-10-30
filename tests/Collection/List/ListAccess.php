<?php

/**
 * This file is part of the Noctud Collection.
 * Copyright (c) Noctud.dev
 */

declare(strict_types=1);

namespace Noctud\Collection\Tests\Collection\List;

use Noctud\Collection\Exception\IndexOutOfBoundsException;
use Noctud\Collection\List\ListInterface;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

trait ListAccess
{
    #[Test]
    public function get_returns_element_at_index(): void
    {
        $object = new stdClass();
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a', 'b', $object]);

        $this->assertSame('a', $list->get(0));
        $this->assertSame('b', $list->get(1));
        $this->assertSame($object, $list->get(2));
    }

    #[Test]
    public function get_throws_on_invalid_index(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a', 'b']);

        $this->expectException(IndexOutOfBoundsException::class);
        $list->get(5);
    }

    #[Test]
    public function get_throws_on_negative_index(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a']);

        $this->expectException(IndexOutOfBoundsException::class);
        $list->get(-1);
    }

    #[Test]
    public function getOrNull_returns_element(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a', 'b']);

        $this->assertSame('a', $list->getOrNull(0));
        $this->assertSame('b', $list->getOrNull(1));
    }

    #[Test]
    public function getOrNull_returns_null_for_invalid(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a']);

        $this->assertNull($list->getOrNull(5));
        $this->assertNull($list->getOrNull(-1));
    }

    #[Test]
    public function getOrDefault_returns_element(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a', 'b']);

        $this->assertSame('a', $list->getOrDefault(0, 'default'));
    }

    #[Test]
    public function getOrDefault_returns_default_for_invalid(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a']);

        $this->assertSame('default', $list->getOrDefault(5, 'default'));
        $this->assertSame('default', $list->getOrDefault(-1, 'default'));
    }

    #[Test]
    public function getOrCompute_returns_element(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a', 'b']);
        $called = false;

        $result = $list->getOrCompute(0, function () use (&$called) {
            $called = true;
            return 'computed';
        });

        $this->assertSame('a', $result);
        $this->assertSame(false, $called);
    }

    #[Test]
    public function getOrCompute_calls_closure_for_invalid(): void
    {
        /** @var ListInterface $list */
        $list = $this->collectionOf(['a']);

        $this->assertSame('computed', $list->getOrCompute(5, fn () => 'computed'));
        $this->assertSame('computed', $list->getOrCompute(-1, fn () => 'computed'));
    }

    // --- ArrayAccess ---

    #[Test]
    public function offsetExists_returns_true_for_valid_index(): void
    {
        /** @var ListInterface<string> $list */
        $list = $this->collectionOf(['a', 'b', 'c']);

        $this->assertSame(true, isset($list[0]));
        $this->assertSame(true, isset($list[1]));
        $this->assertSame(true, isset($list[2]));
    }

    #[Test]
    public function offsetExists_returns_false_for_invalid_index(): void
    {
        /** @var ListInterface<string> $list */
        $list = $this->collectionOf(['a', 'b']);

        $this->assertSame(false, isset($list[5]));
        $this->assertSame(false, isset($list[-1]));
    }

    #[Test]
    public function offsetGet_returns_element(): void
    {
        $object = new stdClass();
        /** @var ListInterface<mixed> $list */
        $list = $this->collectionOf(['a', 'b', $object]);

        $this->assertSame('a', $list[0]);
        $this->assertSame('b', $list[1]);
        $this->assertSame($object, $list[2]);
    }

    #[Test]
    public function offsetGet_throws_on_invalid_index(): void
    {
        /** @var ListInterface<string> $list */
        $list = $this->collectionOf(['a']);

        $this->expectException(IndexOutOfBoundsException::class);
        $list[5]; // @phpstan-ignore expr.resultUnused
    }

    #[Test]
    public function offsetGet_with_null_coalescing_returns_default(): void
    {
        /** @var ListInterface<string> $list */
        $list = $this->collectionOf(['a']);

        $this->assertSame('default', $list[5] ?? 'default');
        $this->assertSame('a', $list[0] ?? 'default');
    }
}
