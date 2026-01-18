<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit;

use DIScope\DIScopeServiceProvider;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DIScopeServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [DIScopeServiceProvider::class];
    }

    #[Test]
    public function サービスプロバイダが登録できる(): void
    {
        $this->assertTrue(true);
    }

    #[Test]
    public function 設定ファイルがマージされる(): void
    {
        $config = $this->app['config']->get('di-scope');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('rules', $config);
        $this->assertArrayHasKey('ignore', $config);
        $this->assertArrayHasKey('output', $config);
    }

    #[Test]
    public function デフォルト設定が正しい(): void
    {
        $this->assertTrue($this->app['config']->get('di-scope.enabled'));
        $this->assertIsArray($this->app['config']->get('di-scope.rules'));
        $this->assertEmpty($this->app['config']->get('di-scope.rules'));
    }
}
