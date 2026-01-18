<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Rules;

use DIScope\Rules\RuleDefinition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RuleDefinitionTest extends TestCase
{
    #[Test]
    public function ワイルドカードパターンにマッチする(): void
    {
        $rule = new RuleDefinition(
            target: 'App\\Domain\\*',
            deny: ['App\\Infrastructure\\*'],
            allow: [],
        );

        $this->assertTrue($rule->matches('App\\Domain\\Order\\OrderService'));
        $this->assertTrue($rule->matches('App\\Domain\\User\\UserEntity'));
        $this->assertFalse($rule->matches('App\\Application\\OrderHandler'));
        $this->assertFalse($rule->matches('App\\Infrastructure\\Database'));
    }

    #[Test]
    public function 完全一致パターンにマッチする(): void
    {
        $rule = new RuleDefinition(
            target: 'App\\Services\\PaymentService',
            deny: [],
            allow: [],
        );

        $this->assertTrue($rule->matches('App\\Services\\PaymentService'));
        $this->assertFalse($rule->matches('App\\Services\\OrderService'));
    }

    #[Test]
    public function 依存先が許可されているか判定できる(): void
    {
        $rule = new RuleDefinition(
            target: 'App\\Domain\\*',
            deny: ['App\\Infrastructure\\*'],
            allow: ['App\\Domain\\*', 'App\\Application\\*'],
        );

        // denyリストに含まれる → 禁止
        $this->assertTrue($rule->isDenied('App\\Infrastructure\\Database\\UserRepository'));
        // allowリストに含まれる → 許可
        $this->assertFalse($rule->isDenied('App\\Domain\\User\\UserEntity'));
        $this->assertFalse($rule->isDenied('App\\Application\\UserHandler'));
        // どちらにも含まれない → 許可（デフォルト）
        $this->assertFalse($rule->isDenied('App\\External\\SomeClass'));
    }

    #[Test]
    public function allowが優先される(): void
    {
        $rule = new RuleDefinition(
            target: 'App\\Domain\\*',
            deny: ['App\\*'], // 全部禁止
            allow: ['App\\Domain\\*'], // でもDomainは許可
        );

        // allowが優先されるのでDomainは許可
        $this->assertFalse($rule->isDenied('App\\Domain\\Order'));
        // それ以外は禁止
        $this->assertTrue($rule->isDenied('App\\Infrastructure\\DB'));
    }
}
