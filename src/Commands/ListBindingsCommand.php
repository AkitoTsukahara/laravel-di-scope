<?php

declare(strict_types=1);

namespace DIScope\Commands;

use DIScope\Analyzer\BindingExtractor;
use Illuminate\Console\Command;

class ListBindingsCommand extends Command
{
    protected $signature = 'di:list
        {--type= : Filter by binding type (bind, singleton, instance)}
        {--search= : Search by class name}';

    protected $description = 'List all service container bindings';

    public function handle(BindingExtractor $extractor): int
    {
        $bindings = $extractor->extract();

        // typeフィルタ
        if ($type = $this->option('type')) {
            $bindings = array_filter(
                $bindings,
                fn($b) => $b->type->value === $type
            );
        }

        // searchフィルタ
        if ($search = $this->option('search')) {
            $bindings = array_filter(
                $bindings,
                fn($b) => str_contains($b->abstract, $search)
                    || str_contains($b->concrete, $search)
            );
        }

        if (empty($bindings)) {
            $this->info('No bindings found.');
            return self::SUCCESS;
        }

        // テーブル出力
        $rows = array_map(fn($b) => [
            $b->abstract,
            $b->concrete,
            $b->type->value,
            $b->context ?? '-',
        ], array_values($bindings));

        $this->table(
            ['Abstract', 'Concrete', 'Type', 'Context'],
            $rows
        );

        $this->newLine();
        $this->info(sprintf('Total: %d bindings', count($bindings)));

        return self::SUCCESS;
    }
}
