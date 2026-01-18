<?php

declare(strict_types=1);

namespace DIScope\Analyzer;

final readonly class BindingInfo
{
    public function __construct(
        public string $abstract,
        public string $concrete,
        public BindingType $type,
        public ?string $context = null,
    ) {}
}
