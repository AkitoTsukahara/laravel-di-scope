<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Rules;

use DIScope\Rules\RuleDefinition;
use DIScope\Rules\Violation;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ViolationTest extends TestCase
{
    #[Test]
    public function 違反情報を保持できる(): void
    {
        $rule = new RuleDefinition(
            target: 'App\\Domain\\*',
            deny: ['App\\Infrastructure\\*'],
            allow: [],
        );

        $violation = new Violation(
            source: 'App\\Domain\\Order\\OrderService',
            target: 'App\\Infrastructure\\Database\\OrderRepository',
            rule: $rule,
        );

        $this->assertSame('App\\Domain\\Order\\OrderService', $violation->source);
        $this->assertSame('App\\Infrastructure\\Database\\OrderRepository', $violation->target);
        $this->assertSame($rule, $violation->rule);
    }

    #[Test]
    public function メッセージを生成できる(): void
    {
        $rule = new RuleDefinition(
            target: 'App\\Domain\\*',
            deny: ['App\\Infrastructure\\*'],
            allow: [],
        );

        $violation = new Violation(
            source: 'App\\Domain\\Order\\OrderService',
            target: 'App\\Infrastructure\\Database\\OrderRepository',
            rule: $rule,
        );

        $message = $violation->getMessage();

        $this->assertStringContainsString('OrderService', $message);
        $this->assertStringContainsString('OrderRepository', $message);
    }
}
