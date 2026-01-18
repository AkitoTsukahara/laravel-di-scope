<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Rules;

use DIScope\Rules\RuleDefinition;
use DIScope\Rules\ValidationResult;
use DIScope\Rules\Violation;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ValidationResultTest extends TestCase
{
    #[Test]
    public function 違反がない場合はvalidを返す(): void
    {
        $result = new ValidationResult([]);

        $this->assertTrue($result->isValid());
        $this->assertSame(0, $result->violationCount());
    }

    #[Test]
    public function 違反がある場合はinvalidを返す(): void
    {
        $rule = new RuleDefinition('App\\*', [], []);
        $violation = new Violation('A', 'B', $rule);

        $result = new ValidationResult([$violation]);

        $this->assertFalse($result->isValid());
        $this->assertSame(1, $result->violationCount());
    }

    #[Test]
    public function 違反リストにアクセスできる(): void
    {
        $rule = new RuleDefinition('App\\*', [], []);
        $violation1 = new Violation('A', 'B', $rule);
        $violation2 = new Violation('C', 'D', $rule);

        $result = new ValidationResult([$violation1, $violation2]);

        $this->assertCount(2, $result->violations);
        $this->assertSame($violation1, $result->violations[0]);
        $this->assertSame($violation2, $result->violations[1]);
    }
}
