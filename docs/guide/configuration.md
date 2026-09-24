# Configuration

The published `config/optimus.php` file controls drivers, storage routing, security parameters, and quality rules.

```php
return [
    // Default image driver: "gd", "imagick", "imgproxy", "cloudinary"
    'driver' => env('OPTIMUS_DRIVER', 'gd'),

    // Secret key for HMAC-SHA256 URL signing
    'key' => env('OPTIMUS_KEY', env('APP_KEY')),

    // Storage disk and path for cached binaries
    'disk' => env('OPTIMUS_DISK', 'public'),
    'cache_path' => env('OPTIMUS_CACHE_PATH', 'optimus'),
    'cdn_url' => env('OPTIMUS_CDN_URL', null),

    // Strict dimension pairs to prevent DoS attacks
    'allowed_dimensions' => [
        [16, 16],
        [32, 32],
        [64, 64],
        [320, 240],
        [640, 360],
        [640, 480],
        [1280, 720],
        [1920, 1080],
        // Single axis width scales (height auto-calculated)
        [320, null],
        [640, null],
        [1280, null],
        [1920, null],
    ],

    'max_width' => 3840,
    'max_height' => 2160,

    // DPR quality mapping (lower quality for high-density displays saves massive bandwidth)
    'quality' => [
        'default' => 80,
        'dpr' => [
            '1x' => 80,
            '1.5x' => 70,
            '2x' => 60,
        ],
    ],

    // Atomic lock timeout in seconds (thundering herd defense)
    'lock_timeout' => 10,

    // Purge TTL for php artisan optimus:clear-stale (default 30 days)
    'stale_ttl' => 86400 * 30,
];
```
