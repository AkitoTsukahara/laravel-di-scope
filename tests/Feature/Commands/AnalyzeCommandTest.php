<?php

declare(strict_types=1);

namespace DIScope\Tests\Feature\Commands;

use DIScope\DIScopeServiceProvider;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AnalyzeCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [DIScopeServiceProvider::class];
    }

    #[Test]
    public function ルールがない場合は成功する(): void
    {
        $this->app['config']->set('di-scope.rules', []);

        $this->artisan('di:analyze')
            ->assertSuccessful();
    }

    #[Test]
    public function 違反がなければ成功コードを返す(): void
    {
        $this->app['config']->set('di-scope.rules', [
            'App\\Domain\\*' => [
                'deny' => ['App\\Infrastructure\\*'],
            ],
        ]);

        // 違反するバインディングがなければ成功
        $this->artisan('di:analyze')
            ->assertSuccessful();
    }
}