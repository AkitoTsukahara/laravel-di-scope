<?php

declare(strict_types=1);

namespace DIScope\Rules;

use DIScope\Analyzer\DependencyNode;

class RuleValidator
{
    /**
     * @param array<DependencyNode> $nodes
     * @param array<RuleDefinition> $rules
     */
    public function validate(array $nodes, array $rules): ValidationResult
    {
        $violations = [];

        foreach ($nodes as $node) {
            $this->validateNode($node, $rules, $violations);
        }

        return new ValidationResult($violations);
    }

    /**
     * @param array<RuleDefinition> $rules
     * @param array<Violation> $violations
     */
    private function validateNode(
        DependencyNode $node,
        array $rules,
        array &$violations,
    ): void {
        // このノードに適用されるルールを探す
        $applicableRules = array_filter(
            $rules,
            fn($rule) => $rule->matches($node->className)
        );

        // 各依存先をチェック
        foreach ($node->dependencies as $dependency) {
            foreach ($applicableRules as $rule) {
                if ($rule->isDenied($dependency->className)) {
                    $violations[] = new Violation(
                        source: $node->className,
                        target: $dependency->className,
                        rule: $rule,
                    );
                }
            }

            // 再帰的にチェック
            $this->validateNode($dependency, $rules, $violations);
        }
    }
}
