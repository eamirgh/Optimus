.PHONY: help install test test-unit test-feature test-coverage benchmark docs-dev docs-build docs-preview clean

help: ## Display this help screen
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-18s\033[0m %s\n", $$1, $$2}'

install: ## Install composer and npm dependencies
	composer install
	npm install

test: ## Run complete PHPUnit test suite
	./vendor/bin/phpunit

test-unit: ## Run only unit tests
	./vendor/bin/phpunit --testsuite=Unit

test-feature: ## Run only feature tests
	./vendor/bin/phpunit --testsuite=Feature

test-coverage: ## Run PHPUnit test suite with code coverage
	./vendor/bin/phpunit --coverage-text

benchmark: ## Run the performance benchmark suite
	php benchmarks/benchmark.php

docs-dev: ## Start VitePress documentation dev server
	npm run docs:dev

docs-build: ## Build VitePress documentation for production
	npm run docs:build

docs-preview: ## Preview built documentation locally
	npm run docs:preview

clean: ## Clean cache and temporary test artifacts
	rm -rf .phpunit.cache docs/.vitepress/dist docs/.vitepress/cache node_modules/.vitepress/cache
