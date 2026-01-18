<?php

declare(strict_types=1);

namespace DIScope\Rules;

final readonly class ValidationResult
{
    /**
     * @param array<Violation> $violations
     */
    public function __construct(
        public array $violations,
    ) {}

    public function isValid(): bool
    {
        return empty($this->violations);
    }

    public function violationCount(): int
    {
        return count($this->violations);
    }
}
