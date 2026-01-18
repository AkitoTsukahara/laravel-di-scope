<?php

declare(strict_types=1);

namespace DIScope\Commands;

use DIScope\Analyzer\BindingExtractor;
use DIScope\Analyzer\DependencyResolver;
use DIScope\Rules\RuleParser;
use DIScope\Rules\RuleValidator;
use Illuminate\Console\Command;

class AnalyseCommand extends Command
{
    protected $signature = 'di:analyse
        {--format=text : Output format (text, json)}';

    protected $description = 'Analyse DI container bindings and check for rule violations';

    public function handle(
        BindingExtractor $extractor,
        DependencyResolver $resolver,
        RuleParser $ruleParser,
        RuleValidator $ruleValidator,
    ): int {
        $this->info('DI Scope Analysis');
        $this->info('==================');
        $this->newLine();

        // バインディング抽出
        $bindings = $extractor->extract();
        $this->line(sprintf('✓ %d bindings found', count($bindings)));

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
        foreach ($bindings as $binding) {
            if ($binding->concrete !== 'Closure' && class_exists($binding->concrete)) {
                $dependencyTrees[] = $resolver->resolve($binding->concrete);
            }
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
}
