# Getting Started

Laravel Optimus provides an end-to-end media and asset optimization pipeline for Laravel applications.

## Requirements

- PHP 8.2 or higher
- Laravel 11.x, 12.x, or 13.x
- GD extension (`ext-gd`) or Imagick (`ext-imagick`) for local image processing

## Installation

Install the package via Composer:

```bash
composer require eamirgh/optimus
```

Publish the package configuration:

```bash
php artisan vendor:publish --tag=optimus-config
```

Publish AI Agent Skill:

```bash
php artisan optimus:skill
# or custom path:
php artisan optimus:skill --path=.cursor/rules/optimus.md
```

This publishes `SKILL.md` directly into `.agents/skills/optimus/SKILL.md` so coding assistants automatically apply Optimus best practices.

## Quick Start: `<x-image>`

Replace standard `<img>` tags with `<x-image>`:

```html
<x-image
    src="hero.jpg"
    width="1280"
    height="720"
    alt="Hero Banner"
    placeholder="blur"
/>
```

Optimus automatically generates:
1. `<picture>` tag serving AVIF to supporting browsers, WebP as second choice, and JPEG/PNG as fallback.
2. `srcset` containing 1x, 1.5x, and 2x DPR resolutions.
3. Cryptographically signed URLs to prevent dimension tampering.
4. Inline base64 blur placeholder for instant First Contentful Paint (FCP).
5. Native `loading="lazy"` and `decoding="async"`.
