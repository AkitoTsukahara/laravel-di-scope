<?php

declare(strict_types=1);

namespace DIScope\Rules;

final readonly class RuleDefinition
{
    /**
     * @param string $target 対象パターン（例: 'App\\Domain\\*'）
     * @param array<string> $deny 禁止する依存先パターン
     * @param array<string> $allow 許可する依存先パターン
     */
    public function __construct(
        public string $target,
        public array $deny,
        public array $allow,
    ) {}

    /**
     * クラス名がこのルールの対象かどうか
     */
    public function matches(string $className): bool
    {
        return $this->matchesPattern($this->target, $className);
    }

    /**
     * 依存先が禁止されているかどうか
     */
    public function isDenied(string $dependency): bool
    {
        // allowに含まれていれば許可（優先）
        foreach ($this->allow as $pattern) {
            if ($this->matchesPattern($pattern, $dependency)) {
                return false;
            }
        }

        // denyに含まれていれば禁止
        foreach ($this->deny as $pattern) {
            if ($this->matchesPattern($pattern, $dependency)) {
                return true;
            }
        }

        // どちらにも含まれなければ許可
        return false;
    }

    private function matchesPattern(string $pattern, string $className): bool
    {
        $regex = preg_quote($pattern, '/');
        $regex = str_replace('\*', '.*', $regex);
        $regex = '/^' . $regex . '$/';

        return (bool) preg_match($regex, $className);
    }
}
