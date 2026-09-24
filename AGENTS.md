# Agent Guidelines for Laravel Optimus Development

This document guides AI agents and developers contributing to **Laravel Optimus** (`eamirgh/optimus`), an enterprise-grade image and asset performance optimization engine for Laravel.

---

## 1. Core Principles & Development Workflow

When implementing new features or adjusting drivers/components, **always follow this strict cycle**:

```
1. TEST FIRST (TDD) ──> 2. IMPLEMENT CODE ──> 3. BENCHMARK ──> 4. DOCUMENT
```

1. **Test First**: Write a dedicated, failing test in `tests/Unit/` or `tests/Feature/` verifying behavior with assertions.
2. **Implement Code**: Write the minimum, clean, strictly typed PHP code in `src/` to pass tests.
3. **Benchmark**: Run `php benchmarks/benchmark.php` or `make benchmark` to verify operations/sec and memory bounds.
4. **Document**: Update or create relevant documentation and code samples in `docs/` (VitePress).

---

## 2. Architecture & Tech Stack

- **Package**: `eamirgh/optimus`
- **Root Namespace**: `Eamirgh\Optimus\`
- **Test Namespace**: `Eamirgh\Optimus\Tests\`
- **Language**: PHP 8.2+ (strict types, union types, match expressions, constructor promotion)
- **Supported Frameworks**: Laravel 11.x, 12.x, and 13.x
- **Test Framework**: PHPUnit 11+ with Orchestra Testbench
- **Documentation**: VitePress (`docs/`)

### Directory Layout

```
optimus/
├── config/
│   └── optimus.php               # Central configuration defaults
├── src/
│   ├── Commands/                 # Artisan commands (optimus:clear-stale)
│   ├── Concerns/                 # HasOptimusImages Eloquent trait
│   ├── Contracts/                # ImageDriverInterface, OptimizerInterface
│   ├── Drivers/                  # GdDriver, ImagickDriver, ImgproxyDriver, CloudinaryDriver
│   ├── Exceptions/               # InvalidDimensionException, InvalidSignatureException
│   ├── Facades/                  # Optimus facade
│   ├── Http/Controllers/        # OptimusController (signed delivery & warm disk cache)
│   ├── Jobs/                     # GenerateImageVariantsJob (async queue pregeneration)
│   ├── Negotiation/              # FormatNegotiator (Accept header -> AVIF/WebP)
│   ├── Optimization/             # SecondaryOptimizer (pngquant, jpegoptim, cwebp, gifsicle)
│   ├── Placeholders/             # BlurPlaceholder, BlurhashPlaceholder, SqipPlaceholder
│   ├── Security/                 # SignatureGenerator, DimensionValidator, ConcurrencyLock
│   ├── Support/                  # OptimusUrlGenerator
│   ├── View/                     # Components (<x-image>, <x-link>, <x-script>, <x-ga>)
│   │   └── Directives/           # OptimusBladeDirectives (@optimusCssBg)
│   ├── OptimusManager.php        # Driver factory manager
│   └── OptimusServiceProvider.php# Service provider, route registration, Blade components
├── tests/
│   ├── Unit/                     # Unit tests for security, drivers, negotiation, placeholders
│   ├── Feature/                  # Feature tests for HTTP controller, Blade components, queue, CLI
│   └── TestCase.php              # Base Orchestra testbench setup
├── benchmarks/
│   └── benchmark.php             # Standalone 9-suite performance benchmark
├── docs/                         # VitePress documentation
│   ├── .vitepress/config.mts     # VitePress navigation and sidebar config
│   └── guide/                    # Markdown guides
├── .github/workflows/            # CI/CD (tests.yml, docs.yml)
├── Makefile                      # make test, make benchmark, make docs-dev
└── SKILL.md                      # Agent skill instructions
```

---

## 3. Key Conventions

- **Security First**: Never accept raw dimensions or format strings without passing through `SignatureGenerator` and `DimensionValidator`.
- **Thundering Herd**: Any file generation logic touching disk must use `ConcurrencyLock` with an atomic cache lock key.
- **Cache-Control**: All generated image responses must emit `Cache-Control: public, max-age=31536000, immutable`.
- **Zero CLS**: Image components must always output or compute explicit `width` and `height` attributes unless `fill="true"` is declared.
- **Graceful Fallbacks**: Drivers and optimizers must check for extension/binary availability and fail gracefully (e.g. falling back to WebP/JPEG if AVIF is unsupported).
