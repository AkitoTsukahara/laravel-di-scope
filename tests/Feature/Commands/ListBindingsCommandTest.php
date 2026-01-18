<?php

declare(strict_types=1);

namespace DIScope\Tests\Feature\Commands;

use DIScope\DIScopeServiceProvider;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ListBindingsCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [DIScopeServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用バインディング
        $this->app->bind('App\Contracts\TestInterface', 'App\Services\TestService');
        $this->app->singleton('App\Contracts\CacheInterface', 'App\Services\CacheService');
    }

    #[Test]
    public function バインディング一覧を表示できる(): void
    {
        $this->artisan('di:list')
            ->assertSuccessful();
    }

    #[Test]
    public function typeオプションでフィルタできる(): void
    {
        $this->artisan('di:list', ['--type' => 'singleton'])
            ->assertSuccessful();
    }

    #[Test]
    public function searchオプションで検索できる(): void
    {
        $this->artisan('di:list', ['--search' => 'Cache'])
            ->assertSuccessful();
    }
}
