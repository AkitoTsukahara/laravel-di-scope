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
        $this->artisan('di:graph', ['--format' => 'mermaid', '--bindings' => true])
            ->assertSuccessful();
    }

    #[Test]
    public function ファイルに出力できる(): void
    {
        $outputPath = sys_get_temp_dir() . '/test-graph.mmd';

        // 既存ファイルを削除
        if (file_exists($outputPath)) {
            unlink($outputPath);
        }

        $this->artisan('di:graph', [
            '--format' => 'mermaid',
            '--output' => $outputPath,
            '--bindings' => true,
        ])->assertSuccessful();

        $this->assertFileExists($outputPath);

        // cleanup
        unlink($outputPath);
    }

    #[Test]
    public function depthオプションで深さを制限できる(): void
    {
        $this->artisan('di:graph', ['--depth' => '2', '--bindings' => true])
            ->assertSuccessful();
    }

    #[Test]
    public function classオプションで特定クラスを分析できる(): void
    {
        $this->artisan('di:graph', [
            '--class' => [DIScopeServiceProvider::class],
        ])->assertSuccessful();
    }

    #[Test]
    public function スキャン対象がない場合も成功する(): void
    {
        // app/ディレクトリがないテスト環境でも成功すること
        $this->artisan('di:graph')
            ->assertSuccessful();
    }
}
