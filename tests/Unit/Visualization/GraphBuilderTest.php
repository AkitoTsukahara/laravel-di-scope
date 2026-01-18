<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Visualization;

use DIScope\Analyzer\DependencyNode;
use DIScope\Rules\RuleDefinition;
use DIScope\Rules\ValidationResult;
use DIScope\Rules\Violation;
use DIScope\Visualization\GraphBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GraphBuilderTest extends TestCase
{
    private GraphBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new GraphBuilder();
    }

    #[Test]
    public function 依存ノードからグラフを構築できる(): void
    {
        $childNode = new DependencyNode('App\\Repository\\UserRepo', 1);
        $parentNode = new DependencyNode('App\\Service\\UserService', 0);
        $parentNode->addDependency($childNode);

        $validationResult = new ValidationResult([]);

        $graph = $this->builder->build([$parentNode], $validationResult);

        $this->assertCount(2, $graph->nodes);
        $this->assertCount(1, $graph->edges);
    }

    #[Test]
    public function 違反エッジにフラグを立てる(): void
    {
        $childNode = new DependencyNode('App\\Infra\\UserRepo', 1);
        $parentNode = new DependencyNode('App\\Domain\\UserService', 0);
        $parentNode->addDependency($childNode);

        $rule = new RuleDefinition('App\\Domain\\*', ['App\\Infra\\*'], []);
        $violation = new Violation(
            'App\\Domain\\UserService',
            'App\\Infra\\UserRepo',
            $rule,
        );
        $validationResult = new ValidationResult([$violation]);

        $graph = $this->builder->build([$parentNode], $validationResult);

        $violationEdges = array_filter(
            $graph->edges,
            fn($e) => $e->isViolation
        );
        $this->assertCount(1, $violationEdges);
    }

    #[Test]
    public function 違反元ノードにもフラグを立てる(): void
    {
        $childNode = new DependencyNode('App\\Infra\\Repo', 1);
        $parentNode = new DependencyNode('App\\Domain\\Service', 0);
        $parentNode->addDependency($childNode);

        $rule = new RuleDefinition('App\\Domain\\*', ['App\\Infra\\*'], []);
        $violation = new Violation(
            'App\\Domain\\Service',
            'App\\Infra\\Repo',
            $rule,
        );
        $validationResult = new ValidationResult([$violation]);

        $graph = $this->builder->build([$parentNode], $validationResult);

        $violationNodes = array_filter(
            $graph->nodes,
            fn($n) => $n->isViolation
        );
        $this->assertCount(1, $violationNodes);
    }

    #[Test]
    public function ノードラベルはクラス名の短縮形になる(): void
    {
        $node = new DependencyNode('App\\Domain\\Order\\OrderService', 0);
        $validationResult = new ValidationResult([]);

        $graph = $this->builder->build([$node], $validationResult);

        $graphNode = array_values($graph->nodes)[0];
        $this->assertSame('OrderService', $graphNode->label);
    }
}
