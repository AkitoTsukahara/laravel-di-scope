<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Analyzer;

use DIScope\Analyzer\DependencyNode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DependencyNodeTest extends TestCase
{
    #[Test]
    public function ノード情報を保持できる(): void
    {
        $node = new DependencyNode(
            className: 'App\Services\OrderService',
            depth: 0,
        );

        $this->assertSame('App\Services\OrderService', $node->className);
        $this->assertSame(0, $node->depth);
        $this->assertEmpty($node->dependencies);
        $this->assertFalse($node->isCircular);
    }

    #[Test]
    public function 子ノードを追加できる(): void
    {
        $parent = new DependencyNode(
            className: 'App\Services\OrderService',
            depth: 0,
        );

        $child = new DependencyNode(
            className: 'App\Repositories\OrderRepository',
            depth: 1,
        );

        $parent->addDependency($child);

        $this->assertCount(1, $parent->dependencies);
        $this->assertSame($child, $parent->dependencies[0]);
    }

    #[Test]
    public function 循環依存フラグを設定できる(): void
    {
        $node = new DependencyNode(
            className: 'App\Services\ServiceA',
            depth: 2,
            isCircular: true,
        );

        $this->assertTrue($node->isCircular);
    }
}
