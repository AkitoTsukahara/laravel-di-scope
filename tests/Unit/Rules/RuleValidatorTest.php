<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Rules;

use DIScope\Analyzer\DependencyNode;
use DIScope\Rules\RuleDefinition;
use DIScope\Rules\RuleValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RuleValidatorTest extends TestCase
{
    private RuleValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new RuleValidator();
    }

    #[Test]
    public function 禁止された依存関係を検出できる(): void
    {
        $rules = [
            new RuleDefinition(
                target: 'App\\Domain\\*',
                deny: ['App\\Infrastructure\\*'],
                allow: [],
            ),
        ];

        // Domain -> Infrastructure の依存（違反）
        $infraNode = new DependencyNode('App\\Infrastructure\\DB\\UserRepo', 1);
        $domainNode = new DependencyNode('App\\Domain\\User\\UserService', 0);
        $domainNode->addDependency($infraNode);

        $result = $this->validator->validate([$domainNode], $rules);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->violations);
        $this->assertSame('App\\Domain\\User\\UserService', $result->violations[0]->source);
        $this->assertSame('App\\Infrastructure\\DB\\UserRepo', $result->violations[0]->target);
    }

    #[Test]
    public function 許可された依存関係は違反にならない(): void
    {
        $rules = [
            new RuleDefinition(
                target: 'App\\Domain\\*',
                deny: ['App\\Infrastructure\\*'],
                allow: ['App\\Domain\\*'],
            ),
        ];

        // Domain -> Domain の依存（許可）
        $otherDomainNode = new DependencyNode('App\\Domain\\Order\\Order', 1);
        $domainNode = new DependencyNode('App\\Domain\\User\\UserService', 0);
        $domainNode->addDependency($otherDomainNode);

        $result = $this->validator->validate([$domainNode], $rules);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->violations);
    }

    #[Test]
    public function ネストした依存関係の違反も検出できる(): void
    {
        $rules = [
            new RuleDefinition(
                target: 'App\\Domain\\*',
                deny: ['App\\Infrastructure\\*'],
                allow: [],
            ),
        ];

        // Domain -> Domain -> Infrastructure（2階層目で違反）
        $infraNode = new DependencyNode('App\\Infrastructure\\DB\\Repo', 2);
        $innerDomainNode = new DependencyNode('App\\Domain\\Order\\OrderService', 1);
        $innerDomainNode->addDependency($infraNode);
        $outerDomainNode = new DependencyNode('App\\Domain\\User\\UserService', 0);
        $outerDomainNode->addDependency($innerDomainNode);

        $result = $this->validator->validate([$outerDomainNode], $rules);

        $this->assertFalse($result->isValid());
        // OrderService -> Repo の違反を検出
        $this->assertCount(1, $result->violations);
        $this->assertSame('App\\Domain\\Order\\OrderService', $result->violations[0]->source);
    }

    #[Test]
    public function ルール対象外のクラスは検証されない(): void
    {
        $rules = [
            new RuleDefinition(
                target: 'App\\Domain\\*',
                deny: ['App\\Infrastructure\\*'],
                allow: [],
            ),
        ];

        // Application -> Infrastructure（Domainルールの対象外）
        $infraNode = new DependencyNode('App\\Infrastructure\\DB\\Repo', 1);
        $appNode = new DependencyNode('App\\Application\\SomeHandler', 0);
        $appNode->addDependency($infraNode);

        $result = $this->validator->validate([$appNode], $rules);

        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function 複数ノードを検証できる(): void
    {
        $rules = [
            new RuleDefinition(
                target: 'App\\Domain\\*',
                deny: ['App\\Infrastructure\\*'],
                allow: [],
            ),
        ];

        $infraNode1 = new DependencyNode('App\\Infrastructure\\A', 1);
        $domainNode1 = new DependencyNode('App\\Domain\\Service1', 0);
        $domainNode1->addDependency($infraNode1);

        $infraNode2 = new DependencyNode('App\\Infrastructure\\B', 1);
        $domainNode2 = new DependencyNode('App\\Domain\\Service2', 0);
        $domainNode2->addDependency($infraNode2);

        $result = $this->validator->validate([$domainNode1, $domainNode2], $rules);

        $this->assertFalse($result->isValid());
        $this->assertCount(2, $result->violations);
    }
}
