<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Analyzer;

use DIScope\Analyzer\BindingExtractor;
use DIScope\Analyzer\BindingType;
use Illuminate\Container\Container;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BindingExtractorTest extends TestCase
{
    private Container $container;
    private BindingExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->extractor = new BindingExtractor($this->container);
    }

    #[Test]
    public function bindで登録したバインディングを抽出できる(): void
    {
        $this->container->bind(
            'App\Contracts\PaymentInterface',
            'App\Services\StripePayment'
        );

        $bindings = $this->extractor->extract();

        $this->assertCount(1, $bindings);
        $this->assertSame('App\Contracts\PaymentInterface', $bindings[0]->abstract);
        $this->assertSame('App\Services\StripePayment', $bindings[0]->concrete);
        $this->assertSame(BindingType::BIND, $bindings[0]->type);
    }

    #[Test]
    public function singletonで登録したバインディングを抽出できる(): void
    {
        $this->container->singleton(
            'App\Contracts\CacheInterface',
            'App\Services\RedisCache'
        );

        $bindings = $this->extractor->extract();

        $this->assertCount(1, $bindings);
        $this->assertSame(BindingType::SINGLETON, $bindings[0]->type);
    }

    #[Test]
    public function instanceで登録したバインディングを抽出できる(): void
    {
        $object = new \stdClass();
        $object->name = 'test';
        $this->container->instance('App\SomeConfig', $object);

        $bindings = $this->extractor->extract();

        $this->assertCount(1, $bindings);
        $this->assertSame('App\SomeConfig', $bindings[0]->abstract);
        $this->assertSame('stdClass', $bindings[0]->concrete);
        $this->assertSame(BindingType::INSTANCE, $bindings[0]->type);
    }

    #[Test]
    public function コンテキストバインディングを抽出できる(): void
    {
        $this->container->when('App\Controllers\OrderController')
            ->needs('App\Contracts\LoggerInterface')
            ->give('App\Services\OrderLogger');

        $bindings = $this->extractor->extract();

        $contextualBindings = array_filter(
            $bindings,
            fn($b) => $b->type === BindingType::CONTEXTUAL
        );

        $this->assertCount(1, $contextualBindings);
        $binding = array_values($contextualBindings)[0];
        $this->assertSame('App\Contracts\LoggerInterface', $binding->abstract);
        $this->assertSame('App\Services\OrderLogger', $binding->concrete);
        $this->assertSame('App\Controllers\OrderController', $binding->context);
    }

    #[Test]
    public function Closureで登録したバインディングのconcreteはClosureになる(): void
    {
        $this->container->bind('App\Contracts\ServiceInterface', function () {
            return new \stdClass();
        });

        $bindings = $this->extractor->extract();

        $this->assertCount(1, $bindings);
        $this->assertSame('Closure', $bindings[0]->concrete);
    }
}
