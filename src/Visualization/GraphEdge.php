<?php

declare(strict_types=1);

namespace DIScope\Visualization;

final readonly class GraphEdge
{
    public function __construct(
        public string $from,
        public string $to,
        public bool $isViolation = false,
    ) {}
}
