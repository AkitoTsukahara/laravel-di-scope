<?php

declare(strict_types=1);

namespace DIScope\Rules;

final readonly class Violation
{
    public function __construct(
        public string $source,
        public string $target,
        public RuleDefinition $rule,
    ) {}

    public function getMessage(): string
    {
        return sprintf(
            '%s cannot depend on %s (rule: %s)',
            $this->getShortName($this->source),
            $this->getShortName($this->target),
            $this->rule->target,
        );
    }

    private function getShortName(string $className): string
    {
        $parts = explode('\\', $className);
        return end($parts);
    }
}
