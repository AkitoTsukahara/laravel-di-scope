<?php

declare(strict_types=1);

namespace DIScope\Analyzer;

class DependencyNode
{
    /** @var array<DependencyNode> */
    public array $dependencies = [];

    public function __construct(
        public readonly string $className,
        public readonly int $depth,
        public bool $isCircular = false,
    ) {}

    public function addDependency(DependencyNode $node): void
    {
        $this->dependencies[] = $node;
    }
}
