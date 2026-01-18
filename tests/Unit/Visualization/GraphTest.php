<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Visualization;

use DIScope\Visualization\Graph;
use DIScope\Visualization\GraphEdge;
use DIScope\Visualization\GraphNode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GraphTest extends TestCase
{
    #[Test]
    public function ノードを追加できる(): void
    {
        $graph = new Graph();
        $node = new GraphNode('node1', 'UserService');

        $graph->addNode($node);

        $this->assertCount(1, $graph->nodes);
        $this->assertSame($node, $graph->nodes['node1']);
    }

    #[Test]
    public function エッジを追加できる(): void
    {
        $graph = new Graph();
        $edge = new GraphEdge('node1', 'node2');

        $graph->addEdge($edge);

        $this->assertCount(1, $graph->edges);
        $this->assertSame($edge, $graph->edges[0]);
    }

    #[Test]
    public function ノードの違反フラグを確認できる(): void
    {
        $node = new GraphNode('node1', 'Service', isViolation: true);

        $this->assertTrue($node->isViolation);
    }

    #[Test]
    public function エッジの違反フラグを確認できる(): void
    {
        $edge = new GraphEdge('node1', 'node2', isViolation: true);

        $this->assertTrue($edge->isViolation);
    }
}
