<?php

declare(strict_types=1);

namespace DIScope\Analyzer;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

class ClassScanner
{
    /**
     * @param array<string> $paths
     * @param array<string> $excludePaths
     * @param array<string> $excludePatterns
     * @return array<string>
     */
    public function scan(
        array $paths,
        array $excludePaths = [],
        array $excludePatterns = [],
    ): array {
        $classes = [];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $classes = array_merge(
                $classes,
                $this->scanDirectory($path, $excludePaths, $excludePatterns)
            );
        }

        return array_unique($classes);
    }

    /**
     * @param array<string> $excludePaths
     * @param array<string> $excludePatterns
     * @return array<string>
     */
    private function scanDirectory(
        string $directory,
        array $excludePaths,
        array $excludePatterns,
    ): array {
        $classes = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();

            // パスベースの除外チェック
            if ($this->shouldExcludePath($filePath, $excludePaths)) {
                continue;
            }

            $className = $this->getClassNameFromFile($filePath);

            if ($className === null) {
                continue;
            }

            // パターンベースの除外チェック
            if ($this->shouldExcludePattern($className, $excludePatterns)) {
                continue;
            }

            $classes[] = $className;
        }

        return $classes;
    }

    private function getClassNameFromFile(string $filePath): ?string
    {
        $contents = file_get_contents($filePath);

        if ($contents === false) {
            return null;
        }

        // 名前空間を取得
        $namespace = null;
        if (preg_match('/namespace\s+([^;]+);/', $contents, $matches)) {
            $namespace = $matches[1];
        }

        // クラス名を取得（class, enum対応）
        if (preg_match('/(?:class|enum)\s+(\w+)/', $contents, $matches)) {
            $className = $matches[1];
            $fullClassName = $namespace ? $namespace . '\\' . $className : $className;

            // クラスが実際にロード可能か確認
            if (class_exists($fullClassName) || enum_exists($fullClassName)) {
                try {
                    $reflection = new ReflectionClass($fullClassName);
                    // 抽象クラス、インターフェース、トレイトは除外
                    if (!$reflection->isAbstract() && !$reflection->isInterface() && !$reflection->isTrait()) {
                        return $fullClassName;
                    }
                } catch (\ReflectionException) {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string> $excludePaths
     */
    private function shouldExcludePath(string $filePath, array $excludePaths): bool
    {
        $normalizedFilePath = str_replace('\\', '/', $filePath);

        foreach ($excludePaths as $excludePath) {
            $normalizedExcludePath = str_replace('\\', '/', $excludePath);

            if (str_contains($normalizedFilePath, $normalizedExcludePath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string> $patterns
     */
    private function shouldExcludePattern(string $className, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $regex = str_replace('\*', '.*', preg_quote($pattern, '/'));
            if (preg_match('/^' . $regex . '$/', $className)) {
                return true;
            }
        }

        return false;
    }
}
