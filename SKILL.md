# Skill: optimus

# Laravel Optimus: Media & Asset Performance Guidelines

## What I Do

I provide expert guidance for implementing modern image and frontend asset performance optimization in Laravel applications using `eamirgh/optimus`:
- Modern `<x-image>` component with content-negotiated AVIF/WebP, DPR `srcset` scaling, CLS prevention, and instant placeholders (blur, blurhash, sqip).
- Low-priority viewport prefetching with `<x-link>` and automatic resource hints (`preconnect`, `dns-prefetch`).
- Declarative script loading strategies via `<x-script>` (`beforeInteractive`, `afterInteractive`, `lazyOnload`) and `<x-ga>`.
- Cross-browser CSS `image-set()` backgrounds via `@optimusCssBg`.
- Switching and configuring Strategy image drivers: `GD`, `Imagick`, `Imgproxy`, and `Cloudinary`.
- HMAC-SHA256 URL security, strict dimension whitelisting, and thundering herd atomic cache locks.
- Scalable async background image pregeneration (`GenerateImageVariantsJob`) with Eloquent model integration (`HasOptimusImages`).
- Secondary binary compression pipelines (`jpegoptim`, `pngquant`, `cwebp`, `gifsicle`) and cache maintenance (`optimus:clear-stale`).

## When to Use Me

Load this skill when:
- Replacing raw `<img>` or `<a>` or `<script>` tags with high-performance Laravel Optimus components.
- Fixing Core Web Vitals (LCP, CLS, FCP) on image-heavy Laravel pages.
- Configuring image drivers (`gd`, `imagick`, `imgproxy`, `cloudinary`) in `config/optimus.php`.
- Securing dynamic image routes against DoS / memory exhaustion attacks.
- Setting up asynchronous queue jobs for image resizing on Eloquent model uploads.
- Generating modern cross-browser background images in CSS/Blade.
- Pruning stale disk caches using `php artisan optimus:clear-stale`.

## Framework Information

- **Package**: `eamirgh/optimus`
- **Namespace**: `Eamirgh\Optimus\`
- **Supported Frameworks**: Laravel 11.x, 12.x, 13.x
- **Language**: PHP 8.2+

---

## Core Code Patterns

### 1. High-Performance Blade Components

#### A. `<x-image>` (Responsive Picture, Modern Formats, CLS Guard)

```html
{{-- Standard responsive image with 1x, 1.5x, 2x srcset and WebP/AVIF content negotiation --}}
<x-image
    src="products/sneakers.jpg"
    alt="Sport Sneakers"
    width="640"
    height="480"
    fit="cover"
    placeholder="blur"
    placeholder-color="#f1f5f9"
/>

{{-- Fill parent container with absolute positioning (object-fit: cover) --}}
<div class="relative w-full h-96">
    <x-image
        src="hero-banner.jpg"
        alt="Hero Banner"
        fill="true"
        placeholder="blur"
    />
</div>

{{-- Blurhash placeholder --}}
<x-image
    src="avatar.png"
    alt="User Avatar"
    width="128"
    height="128"
    placeholder="blurhash"
    blurhash="L6PZfSi_.AyE_3t7t7R**0o#DgR4"
/>
```

#### B. `<x-link>` (Viewport Prefetching & Resource Hinting)

```html
{{-- Prefetches HTML and assets via low-priority fetch() when link enters viewport --}}
<x-link
    href="/checkout"
    prefetch="true"
    :preconnect="['https://api.stripe.com', 'https://cdn.example.com']"
    class="btn btn-primary"
>
    Proceed to Checkout
</x-link>

{{-- Preload critical asset link --}}
<x-link href="/fonts/inter.woff2" preload="true" as="font">
    Fonts
</x-link>
```

#### C. `<x-script>` & `<x-ga>` (Declarative Loading Strategies)

```html
{{-- 1. beforeInteractive: Injected into head; blocks parsing for consent or bot mitigation --}}
<x-script src="/cookie-consent.js" strategy="beforeInteractive" />

{{-- 2. afterInteractive: Deferred execution before </body> --}}
<x-script src="/analytics.js" strategy="afterInteractive" />

{{-- 3. lazyOnload: Executes after window 'load' event (chat widgets, non-critical scripts) --}}
<x-script src="/chat-widget.js" strategy="lazyOnload" id="live-chat" />

{{-- Google Analytics gtag wrapper (forces afterInteractive strategy) --}}
<x-ga id="G-XXXXXXXXXX" />
```

---

### 2. CSS Modern Background Directive

Generate cross-browser CSS `image-set()` with modern format fallbacks:

```html
<div style="@optimusCssBg('backgrounds/banner.png', ['width' => 1920, 'height' => 1080])">
    <h1>Optimized Banner</h1>
</div>
```

---

### 3. Eloquent Model Integration (`HasOptimusImages`)

Automatically dispatch asynchronous image variant pregeneration upon file upload/save:

```php
namespace App\Models;

use Eamirgh\Optimus\Concerns\HasOptimusImages;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasOptimusImages;

    // Attributes monitored for upload changes (default: image, avatar, cover, thumbnail)
    protected array $optimusImages = ['featured_image', 'thumbnail'];

    // Access transformed URL directly:
    public function getThumbnailUrlAttribute(): string
    {
        return $this->optimusUrl('featured_image', [
            'width' => 320,
            'height' => 240,
            'fit' => 'cover',
            'quality' => 80,
        ]);
    }
}
```

---

### 4. Direct URL Generation

```php
use Eamirgh\Optimus\Support\OptimusUrlGenerator;

$urlGenerator = app(OptimusUrlGenerator::class);

$signedUrl = $urlGenerator->url('products/camera.jpg', [
    'width' => 800,
    'height' => 600,
    'fit' => 'cover',
    'quality' => 75,
    'format' => 'webp',
]);
```

---

### 5. Maintenance Commands

```bash
# Preview stale cache files older than 30 days
php artisan optimus:clear-stale --dry-run

# Purge cache files older than 7 days (in seconds)
php artisan optimus:clear-stale --ttl=604800
```

Schedule in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('optimus:clear-stale')->weekly();
```

---

### 6. Nginx try_files Configuration (Warm Cache Bypass)

Bypass PHP runtime completely on subsequent requests:

```nginx
location /optimus/ {
    try_files $uri /index.php?$query_string;
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
}
```
