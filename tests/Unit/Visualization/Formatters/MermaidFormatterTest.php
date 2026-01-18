<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Visualization\Formatters;

use DIScope\Visualization\Formatters\MermaidFormatter;
use DIScope\Visualization\Graph;
use DIScope\Visualization\GraphEdge;
use DIScope\Visualization\GraphNode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MermaidFormatterTest extends TestCase
{
    private MermaidFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new MermaidFormatter();
    }

    #[Test]
    public function Mermaid形式で出力できる(): void
    {
        $graph = new Graph();
        $graph->addNode(new GraphNode('node1', 'UserService'));
        $graph->addNode(new GraphNode('node2', 'UserRepository'));
        $graph->addEdge(new GraphEdge('node1', 'node2'));

        $output = $this->formatter->format($graph);

        $this->assertStringContainsString('flowchart TD', $output);
        $this->assertStringContainsString('node1[UserService]', $output);
        $this->assertStringContainsString('node2[UserRepository]', $output);
        $this->assertStringContainsString('node1 --> node2', $output);
    }

    #[Test]
    public function 違反エッジは赤色スタイルが適用される(): void
    {
        $graph = new Graph();
        $graph->addNode(new GraphNode('node1', 'UserService'));
        $graph->addNode(new GraphNode('node2', 'UserRepository'));
        $graph->addEdge(new GraphEdge('node1', 'node2', isViolation: true));

        $output = $this->formatter->format($graph);

        $this->assertStringContainsString('linkStyle', $output);
        $this->assertStringContainsString('stroke:#ef4444', $output);
    }

    #[Test]
    public function 違反ノードは背景色が適用される(): void
    {
        $graph = new Graph();
        $graph->addNode(new GraphNode('node1', 'Service', isViolation: true));

        $output = $this->formatter->format($graph);

        $this->assertStringContainsString('style node1', $output);
        $this->assertStringContainsString('fill:#fef2f2', $output);
    }

    #[Test]
    public function ファイル拡張子を返す(): void
    {
        $this->assertSame('mmd', $this->formatter->getFileExtension());
    }

    #[Test]
    public function 空のグラフでも出力できる(): void
    {
        $graph = new Graph();

        $output = $this->formatter->format($graph);

        $this->assertStringContainsString('flowchart TD', $output);
    }
}
