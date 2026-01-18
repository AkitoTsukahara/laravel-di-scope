# Laravel DI Scope

[![Latest Version on Packagist](https://img.shields.io/packagist/v/akito-tsukahara/laravel-di-scope.svg)](https://packagist.org/packages/akito-tsukahara/laravel-di-scope)
[![Tests](https://github.com/AkitoTsukahara/laravel-di-scope/actions/workflows/tests.yml/badge.svg)](https://github.com/AkitoTsukahara/laravel-di-scope/actions)
[![License](https://img.shields.io/packagist/l/akito-tsukahara/laravel-di-scope.svg)](https://packagist.org/packages/akito-tsukahara/laravel-di-scope)

Laravelのサービスコンテナのバインディング情報を解析し、依存関係の可視化とアーキテクチャルールの検証を行うパッケージ。

## 特徴

- 🔍 **バインディング可視化**: サービスコンテナに登録された全バインディングを一覧表示
- 🌳 **依存ツリー構築**: クラス間の依存関係を再帰的に解決・表示
- ✅ **ルール検証**: 定義したアーキテクチャルールに違反する依存を検出
- 📊 **グラフ出力**: Mermaid形式で依存関係を可視化（違反は赤色でハイライト）
- 🔄 **CI連携**: 違反があればexit code 1を返すためCIパイプラインに組み込み可能

## 動作要件

- PHP 8.2以上
- Laravel 10.x / 11.x / 12.x

## インストール
```bash
composer require --dev akito-tsukahara/laravel-di-scope
```

## 設定

設定ファイルをpublish:
```bash
php artisan vendor:publish --tag=di-scope-config
```

`config/di-scope.php` でルールを定義:
```php
return [
    'rules' => [
        'App\\Domain\\*' => [
            'deny' => ['App\\Infrastructure\\*'],
            'allow' => ['App\\Domain\\*', 'App\\Application\\*'],
        ],
        'App\\Application\\*' => [
            'deny' => ['App\\Infrastructure\\*'],
        ],
    ],
];
```

## 使い方

### バインディング一覧を表示
```bash
php artisan di:list

# singletonのみ表示
php artisan di:list --type=singleton

# 検索
php artisan di:list --search=Repository
```

### ルール違反を検出
```bash
php artisan di:analyse
```

出力例:
```
DI Scope Analysis
==================

✓ 42 bindings found
✓ 3 rules loaded

✗ 1 violations found

Violations:
-----------
1. OrderService cannot depend on MySQLConnection (rule: App\Domain\*)
   App\Domain\Order\OrderService → App\Infrastructure\Database\MySQLConnection
```

### 依存グラフを出力
```bash
# コンソールに出力
php artisan di:graph

# ファイルに保存
php artisan di:graph --output=graph.mmd

# 特定の名前空間にフォーカス
php artisan di:graph --focus=App\\Domain

# 依存の深さを制限
php artisan di:graph --depth=2
```

## CI連携

GitHub Actionsでの使用例:
```yaml
- name: Check DI Architecture Rules
  run: php artisan di:analyse
```

## ライセンス

MIT
