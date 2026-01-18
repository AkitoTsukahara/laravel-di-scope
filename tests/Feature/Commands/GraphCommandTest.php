<?php

declare(strict_types=1);

namespace DIScope\Tests\Feature\Commands;

use DIScope\DIScopeServiceProvider;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GraphCommandTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [DIScopeServiceProvider::class];
    }

    #[Test]
    public function Mermaid形式でグラフを出力できる(): void
    {
        $this->artisan('di:graph', ['--format' => 'mermaid'])
            ->assertSuccessful();
    }

    #[Test]
    public function ファイルに出力できる(): void
    {
        $outputPath = sys_get_temp_dir() . '/test-graph.mmd';

        $this->artisan('di:graph', [
            '--format' => 'mermaid',
            '--output' => $outputPath,
        ])->assertSuccessful();

        $this->assertFileExists($outputPath);

        // cleanup
        unlink($outputPath);
    }

    #[Test]
    public function depthオプションで深さを制限できる(): void
    {
        $this->artisan('di:graph', ['--depth' => '2'])
            ->assertSuccessful();
    }
}
