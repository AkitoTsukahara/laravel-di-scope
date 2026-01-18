<?php

declare(strict_types=1);

namespace DIScope\Commands;

use DIScope\Analyzer\BindingExtractor;
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
        {--depth=3 : Maximum dependency depth}';

    protected $description = 'Generate dependency graph visualization';

    public function handle(
        BindingExtractor $extractor,
        DependencyResolver $resolver,
        RuleParser $ruleParser,
        RuleValidator $ruleValidator,
        GraphBuilder $graphBuilder,
    ): int {
        // バインディング抽出
        $bindings = $extractor->extract();

        // focusフィルタ
        if ($focus = $this->option('focus')) {
            $bindings = array_filter(
                $bindings,
                fn($b) => str_starts_with($b->abstract, $focus)
                    || str_starts_with($b->concrete, $focus)
            );
        }

        // 依存ツリー構築
        $maxDepth = (int) $this->option('depth');
        $dependencyTrees = [];
        foreach ($bindings as $binding) {
            if ($binding->concrete !== 'Closure' && class_exists($binding->concrete)) {
                $dependencyTrees[] = $resolver->resolve(
                    $binding->concrete,
                    maxDepth: $maxDepth
                );
            }
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

        return self::SUCCESS;
    }

    private function getFormatter(): FormatterInterface
    {
        return match ($this->option('format')) {
            'mermaid' => new MermaidFormatter(),
            default => new MermaidFormatter(),
        };
    }
}
