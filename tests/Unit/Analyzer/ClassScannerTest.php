<?php

declare(strict_types=1);

namespace DIScope\Tests\Unit\Analyzer;

use DIScope\Analyzer\ClassScanner;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClassScannerTest extends TestCase
{
    private ClassScanner $scanner;
    private string $fixturesPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scanner = new ClassScanner();
        $this->fixturesPath = __DIR__ . '/Fixtures';

        // テスト用ディレクトリを作成
        if (!is_dir($this->fixturesPath)) {
            mkdir($this->fixturesPath, 0777, true);
        }
    }

    #[Test]
    public function 存在しないディレクトリは無視される(): void
    {
        $classes = $this->scanner->scan(['/non/existent/path']);

        $this->assertEmpty($classes);
    }

    #[Test]
    public function パターンで除外できる(): void
    {
        $classes = $this->scanner->scan(
            paths: [__DIR__],
            excludePatterns: ['DIScope\\Tests\\Unit\\Analyzer\\*'],
        );

        // このテストクラス自体が除外される
        $this->assertNotContains(self::class, $classes);
    }

    #[Test]
    public function パスで除外できる(): void
    {
        $classes = $this->scanner->scan(
            paths: [__DIR__ . '/..'],
            excludePaths: [__DIR__],
        );

        // このディレクトリ配下が除外される
        $this->assertNotContains(self::class, $classes);
    }
}
