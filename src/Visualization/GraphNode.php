<?php

declare(strict_types=1);

namespace DIScope\Visualization;

final readonly class GraphNode
{
    public function __construct(
        public string $id,
        public string $label,
        public bool $isViolation = false,
    ) {}
}
