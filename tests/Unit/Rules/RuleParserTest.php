<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Rules;

use DIScope\Rules\RuleDefinition;
use DIScope\Rules\RuleParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RuleParserTest extends TestCase
{
    private RuleParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new RuleParser();
    }

    #[Test]
    public function 設定配列からルールを生成できる(): void
    {
        $config = [
            'App\\Domain\\*' => [
                'deny' => ['App\\Infrastructure\\*'],
                'allow' => ['App\\Domain\\*'],
            ],
            'App\\Application\\*' => [
                'deny' => ['App\\Infrastructure\\*'],
            ],
        ];

        $rules = $this->parser->parse($config);

        $this->assertCount(2, $rules);
        $this->assertContainsOnlyInstancesOf(RuleDefinition::class, $rules);

        $this->assertSame('App\\Domain\\*', $rules[0]->target);
        $this->assertSame(['App\\Infrastructure\\*'], $rules[0]->deny);
        $this->assertSame(['App\\Domain\\*'], $rules[0]->allow);

        $this->assertSame('App\\Application\\*', $rules[1]->target);
        $this->assertSame(['App\\Infrastructure\\*'], $rules[1]->deny);
        $this->assertSame([], $rules[1]->allow);
    }

    #[Test]
    public function 空の設定から空のルール配列を返す(): void
    {
        $rules = $this->parser->parse([]);
        $this->assertEmpty($rules);
    }

    #[Test]
    public function denyとallowが未設定の場合は空配列になる(): void
    {
        $config = [
            'App\\Services\\*' => [],
        ];

        $rules = $this->parser->parse($config);

        $this->assertCount(1, $rules);
        $this->assertSame([], $rules[0]->deny);
        $this->assertSame([], $rules[0]->allow);
    }
}
