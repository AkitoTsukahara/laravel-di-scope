<?php

declare(strict_types=1);

namespace DIScope\Analyzer;

use Illuminate\Container\Container;
use ReflectionClass;
use ReflectionNamedType;

class DependencyResolver
{
    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param array<string> $visited
     * @param array<string> $ignorePatterns
     */
    public function resolve(
        string $className,
        int $depth = 0,
        int $maxDepth = 5,
        array $visited = [],
        array $ignorePatterns = [],
    ): DependencyNode {
        // 循環依存チェック
        if (in_array($className, $visited, true)) {
            return new DependencyNode(
                className: $className,
                depth: $depth,
                isCircular: true,
            );
        }

        $node = new DependencyNode(
            className: $className,
            depth: $depth,
        );

        // 最大深度チェック
        if ($depth >= $maxDepth) {
            return $node;
        }

        // クラス存在チェック
        if (!class_exists($className) && !interface_exists($className)) {
            return $node;
        }

        // 訪問済みに追加
        $visited[] = $className;

        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $node;
        }

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            $dependencyClass = $type->getName();
            $resolvedClass = $this->resolveFromContainer($dependencyClass);

            // ignoreパターンに一致する場合はスキップ
            if ($this->shouldIgnore($resolvedClass, $ignorePatterns)) {
                continue;
            }

            $childNode = $this->resolve(
                $resolvedClass,
                $depth + 1,
                $maxDepth,
                $visited,
                $ignorePatterns,
            );

            $node->addDependency($childNode);
        }

        return $node;
    }

    private function resolveFromContainer(string $abstract): string
    {
        if ($this->container->bound($abstract)) {
            try {
                $binding = $this->container->getBindings()[$abstract] ?? null;
                if ($binding === null) {
                    return $abstract;
                }

                $concrete = $binding['concrete'] ?? null;

                if (is_string($concrete)) {
                    return $concrete;
                }

                // ClosureからconcreteクラスNameを取得
                if ($concrete instanceof \Closure) {
                    $reflection = new \ReflectionFunction($concrete);
                    $staticVars = $reflection->getStaticVariables();

                    if (isset($staticVars['concrete']) && is_string($staticVars['concrete'])) {
                        return $staticVars['concrete'];
                    }
                }
            } catch (\Throwable) {
                // 取得できない場合は元のクラス名を返す
            }
        }

        return $abstract;
    }

    /**
     * @param array<string> $patterns
     */
    private function shouldIgnore(string $className, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $regex = str_replace('\*', '.*', preg_quote($pattern, '/'));
            if (preg_match('/^' . $regex . '$/', $className)) {
                return true;
            }
        }

        return false;
    }
}
