<?php

declare(strict_types=1);

namespace DIScope\Commands;

use DIScope\Analyzer\BindingExtractor;
use DIScope\Analyzer\ClassScanner;
use DIScope\Analyzer\DependencyResolver;
use DIScope\Rules\RuleParser;
use DIScope\Rules\RuleValidator;
use DIScope\Visualization\Formatters\FormatterInterface;
use DIScope\Visualization\Formatters\MermaidFormatter;
use DIScope\Visualization\GraphBuilder;
use Illuminate\Console\Command;

class GraphCommand extends Command
{
    protected $signature = 'di:graph
        {--format=mermaid : Output format (mermaid)}
        {--output= : Output file path}
        {--focus= : Focus on specific namespace}
        {--depth=3 : Maximum dependency depth}
        {--class=* : Specific class(es) to analyze}
        {--bindings : Include container bindings only (skip directory scan)}';

    protected $description = 'Generate dependency graph visualization';

    public function handle(
        BindingExtractor $extractor,
        DependencyResolver $resolver,
        RuleParser $ruleParser,
        RuleValidator $ruleValidator,
        GraphBuilder $graphBuilder,
        ClassScanner $scanner,
    ): int {
        $classesToAnalyze = [];
        $maxDepth = (int) $this->option('depth');
        $focus = $this->option('focus');

        // 1. --class オプションで指定されたクラス
        $specifiedClasses = $this->option('class');
        if (!empty($specifiedClasses)) {
            foreach ($specifiedClasses as $class) {
                if (class_exists($class)) {
                    $classesToAnalyze[] = $class;
                } else {
                    $this->warn("Class not found: {$class}");
                }
            }
        }

        // 2. --bindings オプション: コンテナバインディングのみ
        if ($this->option('bindings')) {
            $bindings = $extractor->extract();
            foreach ($bindings as $binding) {
                if ($binding->concrete !== 'Closure' && class_exists($binding->concrete)) {
                    $classesToAnalyze[] = $binding->concrete;
                }
            }
        }

        // 3. デフォルト: ディレクトリスキャン（--class も --bindings も指定されていない場合）
        if (empty($specifiedClasses) && !$this->option('bindings')) {
            $scannedClasses = $this->scanClasses($scanner);
            $classesToAnalyze = array_merge($classesToAnalyze, $scannedClasses);
        }

        // 重複を除去
        $classesToAnalyze = array_unique($classesToAnalyze);

        // focusフィルタ
        if ($focus) {
            $classesToAnalyze = array_filter(
                $classesToAnalyze,
                fn($class) => str_starts_with($class, $focus)
            );
            $classesToAnalyze = array_values($classesToAnalyze);
        }

        if (empty($classesToAnalyze)) {
            $this->warn('No classes to analyze.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Analyzing %d classes...', count($classesToAnalyze)));

        // 依存ツリー構築
        $dependencyTrees = [];
        $ignorePatterns = $this->laravel['config']->get('di-scope.ignore', []);

        foreach ($classesToAnalyze as $class) {
            $dependencyTrees[] = $resolver->resolve(
                className: $class,
                maxDepth: $maxDepth,
                ignorePatterns: $ignorePatterns,
            );
        }

        // ルール検証（違反ハイライト用）
        $rules = $ruleParser->parse($this->laravel['config']->get('di-scope.rules', []));
        $validationResult = $ruleValidator->validate($dependencyTrees, $rules);

        // グラフ構築
        $graph = $graphBuilder->build($dependencyTrees, $validationResult);

        // フォーマッタ選択
        $formatter = $this->getFormatter();
        $output = $formatter->format($graph);

        // 出力
        if ($outputPath = $this->option('output')) {
            file_put_contents($outputPath, $output);
            $this->info(sprintf('Graph saved to: %s', $outputPath));
        } else {
            $this->line($output);
        }

        if (!$validationResult->isValid()) {
            $this->newLine();
            $this->warn(sprintf('%d violation(s) found', $validationResult->violationCount()));
        }

        return self::SUCCESS;
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

    private function getFormatter(): FormatterInterface
    {
        return match ($this->option('format')) {
            'mermaid' => new MermaidFormatter(),
            default => new MermaidFormatter(),
        };
    }
}
