<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ExampleTest extends TestCase
{
    #[Test]
    public function テスト環境が動作する(): void
    {
        $this->assertTrue(true);
    }
}
