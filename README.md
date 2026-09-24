# Laravel Optimus 🚀

[![Tests & Benchmarks](https://github.com/eamirgh/optimus/actions/workflows/tests.yml/badge.svg)](https://github.com/eamirgh/optimus/actions/workflows/tests.yml)
[![Deploy Documentation](https://github.com/eamirgh/optimus/actions/workflows/docs.yml/badge.svg)](https://github.com/eamirgh/optimus/actions/workflows/docs.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

High-performance image and asset performance optimization engine for Laravel 11, 12, and 13.

## Features

- **Blade Components**: `<x-image>`, `<x-link>`, `<x-script>`, `<x-ga>`, `@optimusCssBg`.
- **Strategy Drivers**: GD (`ext-gd`), Imagick (`ext-imagick`), Imgproxy (edge cluster), Cloudinary (SaaS).
- **Format Negotiation**: Content-negotiated AVIF and WebP delivery based on HTTP `Accept` header.
- **Security & DoS Protection**: HMAC-SHA256 URL signing and strict dimension whitelisting.
- **Thundering Herd Mitigation**: Atomic cache lock serialization preventing cache stampedes.
- **Dual Architecture**:
  - *Low-Scale / Monolithic*: Synchronous on-the-fly generation cached to disk (`try_files` friendly).
  - *High-Scale / Distributed*: Asynchronous variant pregeneration (`GenerateImageVariantsJob`) with Eloquent model trait (`HasOptimusImages`).
- **Secondary Compression**: Post-processing execution of `pngquant`, `jpegoptim`, `cwebp`, `gifsicle`.
- **Cache Maintenance**: `php artisan optimus:clear-stale` command.
- **Test Suite & Benchmarks**: Full TDD unit & feature coverage with Orchestra Testbench and standalone benchmark suite.

## Installation

```bash
composer require eamirgh/optimus
php artisan vendor:publish --tag=optimus-config
```

## Running Tests, Benchmarks & Docs

```bash
cd optimus
make install       # Install composer and npm dependencies
make test          # Run PHPUnit test suite
make benchmark     # Run performance benchmark suite
make docs-dev      # Start local VitePress docs server
make docs-build    # Build production docs
```
