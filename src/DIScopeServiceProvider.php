<?php

declare(strict_types=1);

namespace DIScope;

use Illuminate\Support\ServiceProvider;

class DIScopeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/di-scope.php',
            'di-scope'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/di-scope.php' => config_path('di-scope.php'),
            ], 'di-scope-config');
        }
    }
}
