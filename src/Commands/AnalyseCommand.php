<?php

declare(strict_types=1);

namespace DIScope\Commands;

use DIScope\Analyzer\ClassScanner;
use DIScope\Analyzer\DependencyResolver;
use DIScope\Rules\RuleParser;
use DIScope\Rules\RuleValidator;
use Illuminate\Console\Command;

class AnalyseCommand extends Command
{
    protected $signature = 'di:analyse
        {--format=text : Output format (text, json)}
        {--focus= : Focus on specific namespace}';

    protected $description = 'Analyse DI container bindings and check for rule violations';

    public function handle(
        DependencyResolver $resolver,
        RuleParser $ruleParser,
        RuleValidator $ruleValidator,
        ClassScanner $scanner,
    ): int {
        $this->info('DI Scope Analysis');
        $this->info('==================');
        $this->newLine();

        // クラス収集
        $classesToAnalyze = $this->scanClasses($scanner);

        // focusフィルタ
        if ($focus = $this->option('focus')) {
            $classesToAnalyze = array_filter(
                $classesToAnalyze,
                fn($class) => str_starts_with($class, $focus)
            );
            $classesToAnalyze = array_values($classesToAnalyze);
        }

        $this->line(sprintf('✓ %d classes found', count($classesToAnalyze)));

        // ルールパース
        $rulesConfig = $this->laravel['config']->get('di-scope.rules', []);
        $rules = $ruleParser->parse($rulesConfig);

        if (empty($rules)) {
            $this->warn('No rules configured. Skipping validation.');
            return self::SUCCESS;
        }

        $this->line(sprintf('✓ %d rules loaded', count($rules)));

        // 依存ツリー構築
        $dependencyTrees = [];
        $ignorePatterns = $this->laravel['config']->get('di-scope.ignore', []);

        foreach ($classesToAnalyze as $class) {
            $dependencyTrees[] = $resolver->resolve(
                className: $class,
                ignorePatterns: $ignorePatterns,
            );
        }

        // ルール検証
        $result = $ruleValidator->validate($dependencyTrees, $rules);

        $this->newLine();

        if ($result->isValid()) {
            $this->info('✓ No violations found!');
            return self::SUCCESS;
        }

        $this->error(sprintf('✗ %d violations found', $result->violationCount()));
        $this->newLine();

        $this->line('Violations:');
        $this->line('-----------');

        foreach ($result->violations as $i => $violation) {
            $this->line(sprintf(
                '%d. %s',
                $i + 1,
                $violation->getMessage()
            ));
            $this->line(sprintf(
                '   %s → %s',
                $violation->source,
                $violation->target
            ));
            $this->newLine();
        }

        return self::FAILURE;
    }

    /**
     * @return array<string>
     */
    private function scanClasses(ClassScanner $scanner): array
    {
        $scanConfig = $this->laravel['config']->get('di-scope.scan', []);

        $paths = $scanConfig['paths'] ?? [];
        $excludePaths = $scanConfig['exclude_paths'] ?? [];
        $excludePatterns = $scanConfig['exclude_patterns'] ?? [];

        // 相対パスを絶対パスに変換
        $basePath = $this->laravel->basePath();

        $absolutePaths = array_map(
            fn($path) => $basePath . '/' . ltrim($path, '/'),
            $paths
        );

        $absoluteExcludePaths = array_map(
            fn($path) => $basePath . '/' . ltrim($path, '/'),
            $excludePaths
        );

        return $scanner->scan($absolutePaths, $absoluteExcludePaths, $excludePatterns);
    }
}
