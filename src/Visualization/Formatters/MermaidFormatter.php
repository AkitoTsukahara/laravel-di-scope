<?php

declare(strict_types=1);

namespace DIScope\Visualization\Formatters;

use DIScope\Visualization\Graph;

class MermaidFormatter implements FormatterInterface
{
    public function format(Graph $graph): string
    {
        $lines = ['flowchart TD'];
        $violationEdgeIndices = [];
        $edgeIndex = 0;

        // ノード定義
        foreach ($graph->nodes as $node) {
            $lines[] = sprintf('    %s[%s]', $node->id, $node->label);
        }

        if (!empty($graph->nodes)) {
            $lines[] = '';
        }

        // エッジ定義
        foreach ($graph->edges as $edge) {
            $lines[] = sprintf('    %s --> %s', $edge->from, $edge->to);

            if ($edge->isViolation) {
                $violationEdgeIndices[] = $edgeIndex;
            }
            $edgeIndex++;
        }

        // 違反エッジのスタイル
        if (!empty($violationEdgeIndices)) {
            $lines[] = '';
            foreach ($violationEdgeIndices as $index) {
                $lines[] = sprintf(
                    '    linkStyle %d stroke:#ef4444,stroke-width:2px',
                    $index
                );
            }
        }

        // 違反ノードのスタイル
        $violationNodes = array_filter(
            $graph->nodes,
            fn($n) => $n->isViolation
        );
        if (!empty($violationNodes)) {
            $lines[] = '';
            foreach ($violationNodes as $node) {
                $lines[] = sprintf(
                    '    style %s fill:#fef2f2,stroke:#ef4444',
                    $node->id
                );
            }
        }

        return implode("\n", $lines);
    }

    public function getFileExtension(): string
    {
        return 'mmd';
    }
}
