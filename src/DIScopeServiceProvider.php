<?php

declare(strict_types=1);

namespace DIScope;

use DIScope\Analyzer\BindingExtractor;
use DIScope\Analyzer\DependencyResolver;
use DIScope\Commands\ListBindingsCommand;
use DIScope\Rules\RuleParser;
use DIScope\Rules\RuleValidator;
use DIScope\Visualization\GraphBuilder;
use Illuminate\Support\ServiceProvider;

class DIScopeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/di-scope.php',
            'di-scope'
        );

        // Analyzer
        $this->app->singleton(BindingExtractor::class, function ($app) {
            return new BindingExtractor($app);
        });

        $this->app->singleton(DependencyResolver::class, function ($app) {
            return new DependencyResolver($app);
        });

        // Rules
        $this->app->singleton(RuleParser::class);
        $this->app->singleton(RuleValidator::class);

        // Visualization
        $this->app->singleton(GraphBuilder::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/di-scope.php' => config_path('di-scope.php'),
            ], 'di-scope-config');

            $this->commands([
                ListBindingsCommand::class,
            ]);
        }
    }
}
