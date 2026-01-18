.PHONY: help build install shell test test-filter test-coverage clean

help:
	@echo "Laravel DI Scope - 開発コマンド"
	@echo ""
	@echo "セットアップ:"
	@echo "  make build      - Dockerイメージをビルド"
	@echo "  make install    - Composer依存をインストール"
	@echo ""
	@echo "開発:"
	@echo "  make shell      - コンテナ内でシェルを起動"
	@echo "  make test       - テストを実行"
	@echo "  make test-filter FILTER=xxx - 特定のテストを実行"
	@echo ""
	@echo "クリーンアップ:"
	@echo "  make clean      - 生成ファイルを削除"

build:
	docker compose build

install:
	docker compose run --rm php composer install

shell:
	docker compose run --rm php bash

test:
	docker compose run --rm php vendor/bin/phpunit

test-filter:
	docker compose run --rm php vendor/bin/phpunit --filter=$(FILTER)

test-coverage:
	docker compose run --rm php vendor/bin/phpunit --coverage-html coverage

clean:
	rm -rf vendor/
	rm -rf coverage/
	rm -rf .phpunit.cache/
	docker compose down --rmi local --volumes