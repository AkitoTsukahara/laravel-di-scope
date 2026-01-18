<?php

declare(strict_types=1);

namespace DIScope\Analyzer;

use Illuminate\Container\Container;

final class BindingExtractor
{
    public function __construct(
        private readonly Container $container,
    )
    {
    }

    /**
     * @return array<BindingInfo>
     */
    public function extract(): array
    {
        $bindings = [];
        $reflection = new \ReflectionClass($this->container);

        // bindingsプロパティから抽出
        $bindingsProperty = $reflection->getProperty('bindings');
        $rawBindings = $bindingsProperty->getValue($this->container);

        foreach ($rawBindings as $abstract => $binding) {
            $concrete = $this->resolveConcrete($binding['concrete'] ?? null, $abstract);
            $type  = ($binding['shared'] ?? false) ? BindingType::SINGLETON : BindingType::BIND;

            $bindings[] = new BindingInfo($abstract, $concrete, $type);
        }

        // instancesプロパティから抽出
        $instancesProperty = $reflection->getProperty('instances');
        $instances = $instancesProperty->getValue($this->container);

        foreach ($instances as $abstract => $instance) {
            // bindingsに既にあるものはスキップ
            if (isset($rawBindings[$abstract])) {
                continue;
            }

            $bindings[] = new BindingInfo(
                abstract: $abstract,
                concrete: is_object($instance) ? get_class($instance) : (string) $instance,
                type: BindingType::INSTANCE,
            );
        }

        // contextualプロパティから抽出
        $contextualProperty = $reflection->getProperty('contextual');
        $contextualBindings = $contextualProperty->getValue($this->container);

        foreach ($contextualBindings as $context => $abstracts) {
            foreach ($abstracts as $abstract => $concrete) {
                $bindings[] = new BindingInfo(
                    abstract: $abstract,
                    concrete: $this->resolveConcrete($concrete, $abstract),
                    type: BindingType::CONTEXTUAL,
                    context: $context,
                );
            }
        }

        return $bindings;
    }

    private function resolveConcrete(mixed $concrete, string $abstract): string
    {
        if ($concrete instanceof \Closure) {
            // Closureの静的変数から元のconcreteクラス名を取得
            $reflection = new \ReflectionFunction($concrete);
            $staticVars = $reflection->getStaticVariables();

            if (isset($staticVars['concrete']) && is_string($staticVars['concrete'])) {
                return $staticVars['concrete'];
            }

            return 'Closure';
        }

        if (is_string($concrete)) {
            return $concrete;
        }

        return $abstract;
    }
}
