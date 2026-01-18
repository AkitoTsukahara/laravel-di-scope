<?php

declare(strict_types=1);

namespace DIScope\Rules;

class RuleParser
{
    /**
     * @param array<string, array{deny?: array<string>, allow?: array<string>}> $config
     * @return array<RuleDefinition>
     */
    public function parse(array $config): array
    {
        $rules = [];

        foreach ($config as $target => $definition) {
            $rules[] = new RuleDefinition(
                target: $target,
                deny: $definition['deny'] ?? [],
                allow: $definition['allow'] ?? [],
            );
        }

        return $rules;
    }
}
