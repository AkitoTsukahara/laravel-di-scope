<?php

declare(strict_types=1);

namespace DIScope\Visualization;

class Graph
{
    /** @var array<string, GraphNode> */
    public array $nodes = [];

    /** @var array<GraphEdge> */
    public array $edges = [];

    public function addNode(GraphNode $node): void
    {
        $this->nodes[$node->id] = $node;
    }

    public function addEdge(GraphEdge $edge): void
    {
        $this->edges[] = $edge;
    }
}
