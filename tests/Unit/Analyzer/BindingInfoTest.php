<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Analyzer;

use DIScope\Analyzer\BindingInfo;
use DIScope\Analyzer\BindingType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BindingInfoTest extends TestCase
{
    #[Test]
    public function バインディング情報を保持できる(): void
    {
        $binding = new BindingInfo(
            abstract: 'App\Contracts\PaymentInterface',
            concrete: 'App\Services\StripePayment',
            type: BindingType::SINGLETON,
        );

        $this->assertSame('App\Contracts\PaymentInterface', $binding->abstract);
        $this->assertSame('App\Services\StripePayment', $binding->concrete);
        $this->assertSame(BindingType::SINGLETON, $binding->type);
        $this->assertNull($binding->context);
    }

    #[Test]
    public function コンテキスト付きバインディングを保持できる(): void
    {
        $binding = new BindingInfo(
            abstract: 'App\Contracts\LoggerInterface',
            concrete: 'App\Services\OrderLogger',
            type: BindingType::CONTEXTUAL,
            context: 'App\Controllers\OrderController',
        );

        $this->assertSame('App\Controllers\OrderController', $binding->context);
        $this->assertSame(BindingType::CONTEXTUAL, $binding->type);
    }
}
