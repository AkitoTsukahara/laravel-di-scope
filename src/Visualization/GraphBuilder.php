<?php

declare(strict_types=1);

namespace DIScope\Visualization;

use DIScope\Analyzer\DependencyNode;
use DIScope\Rules\ValidationResult;

class GraphBuilder
{
    /**
     * @param array<DependencyNode> $nodes
     */
    public function build(array $nodes, ValidationResult $validationResult): Graph
    {
        $graph = new Graph();
        $violationPairs = $this->extractViolationPairs($validationResult);

        foreach ($nodes as $node) {
            $this->addNodeToGraph($node, $graph, $violationPairs);
        }

        return $graph;
    }

    /**
     * @return array<string, bool>
     */
    private function extractViolationPairs(ValidationResult $result): array
    {
        $pairs = [];
        foreach ($result->violations as $violation) {
            $key = $violation->source . '->' . $violation->target;
            $pairs[$key] = true;
        }
        return $pairs;
    }

    /**
     * @param array<string, bool> $violationPairs
     */
    private function addNodeToGraph(
        DependencyNode $node,
        Graph $graph,
        array $violationPairs,
    ): void {
        $nodeId = $this->makeNodeId($node->className);

        // ノード追加（まだ追加されていない場合のみ）
        if (!isset($graph->nodes[$nodeId])) {
            $isViolationSource = $this->isViolationSource($node->className, $violationPairs);
            $graph->addNode(new GraphNode(
                id: $nodeId,
                label: $this->getShortName($node->className),
                isViolation: $isViolationSource,
            ));
        }

        // 子ノードを処理
        foreach ($node->dependencies as $child) {
            $childId = $this->makeNodeId($child->className);

            // エッジ追加
            $pairKey = $node->className . '->' . $child->className;
            $isViolation = isset($violationPairs[$pairKey]);

            $graph->addEdge(new GraphEdge(
                from: $nodeId,
                to: $childId,
                isViolation: $isViolation,
            ));

            // 再帰
            $this->addNodeToGraph($child, $graph, $violationPairs);
        }
    }

    /**
     * @param array<string, bool> $violationPairs
     */
    private function isViolationSource(string $className, array $violationPairs): bool
    {
        foreach ($violationPairs as $key => $_) {
            if (str_starts_with($key, $className . '->')) {
                return true;
            }
        }
        return false;
    }

    private function makeNodeId(string $className): string
    {
        return str_replace('\\', '_', $className);
    }

    private function getShortName(string $className): string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }
}
