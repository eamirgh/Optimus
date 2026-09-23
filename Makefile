.PHONY: help install test test-unit test-feature test-coverage benchmark clean

help: ## Display this help screen
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-18s\033[0m %s\n", $$1, $$2}'

install: ## Install composer dependencies
	composer install

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

clean: ## Clean cache and temporary test artifacts
	rm -rf .phpunit.cache
