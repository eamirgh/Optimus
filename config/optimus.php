<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Image Processing Driver
    |--------------------------------------------------------------------------
    | Supported: "gd", "imagick", "imgproxy", "cloudinary"
    */
    'driver' => env('OPTIMUS_DRIVER', 'gd'),

    /*
    |--------------------------------------------------------------------------
    | Signing Key
    |--------------------------------------------------------------------------
    | Secret key for HMAC-SHA256 URL signing to prevent parameter tampering.
    */
    'key' => env('OPTIMUS_KEY', env('APP_KEY')),

    /*
    |--------------------------------------------------------------------------
    | Storage & Delivery Architecture
    |--------------------------------------------------------------------------
    */
    'disk' => env('OPTIMUS_DISK', 'public'),
    'cache_path' => env('OPTIMUS_CACHE_PATH', 'optimus'),
    'cdn_url' => env('OPTIMUS_CDN_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Dimension Whitelist (DoS Prevention)
    |--------------------------------------------------------------------------
    | Enforced pairs of [width, height]. Null height means aspect-ratio scale.
    */
    'allowed_dimensions' => [
        [16, 16],
        [32, 32],
        [48, 48],
        [64, 64],
        [128, 128],
        [256, 256],
        [320, 240],
        [640, 360],
        [640, 480],
        [768, 432],
        [800, 600],
        [1024, 576],
        [1024, 768],
        [1280, 720],
        [1536, 864],
        [1600, 900],
        [1920, 1080],
        [2560, 1440],
        [3840, 2160],
        // Common responsive single-axis width scales
        [320, null],
        [480, null],
        [640, null],
        [768, null],
        [1024, null],
        [1280, null],
        [1536, null],
        [1920, null],
    ],

    'max_width' => 3840,
    'max_height' => 2160,

    /*
    |--------------------------------------------------------------------------
    | Quality & Compression Defaults
    |--------------------------------------------------------------------------
    */
    'quality' => [
        'default' => 80,
        'dpr' => [
            '1x' => 80,
            '1.5x' => 70,
            '2x' => 60,
        ],
    ],

    'formats' => ['avif', 'webp', 'jpeg', 'jpg', 'png', 'gif'],

    /*
    |--------------------------------------------------------------------------
    | Concurrency & Lock Protection
    |--------------------------------------------------------------------------
    */
    'lock_timeout' => 10, // seconds

    /*
    |--------------------------------------------------------------------------
    | Cache Stale Purge TTL
    |--------------------------------------------------------------------------
    */
    'stale_ttl' => 86400 * 30, // 30 days

    /*
    |--------------------------------------------------------------------------
    | Driver Configurations
    |--------------------------------------------------------------------------
    */
    'drivers' => [
        'imgproxy' => [
            'base_url' => env('IMGPROXY_URL', 'http://localhost:8080'),
            'key' => env('IMGPROXY_KEY', ''),
            'salt' => env('IMGPROXY_SALT', ''),
        ],
        'cloudinary' => [
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME', ''),
            'api_key' => env('CLOUDINARY_API_KEY', ''),
            'api_secret' => env('CLOUDINARY_API_SECRET', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Secondary Optimizers
    |--------------------------------------------------------------------------
    */
    'optimizers' => [
        'enabled' => env('OPTIMUS_SECONDARY_OPTIMIZERS', true),
        'binaries' => [
            'jpegoptim' => env('JPEGOPTIM_BIN', 'jpegoptim'),
            'pngquant' => env('PNGQUANT_BIN', 'pngquant'),
            'cwebp' => env('CWEBP_BIN', 'cwebp'),
            'gifsicle' => env('GIFSICLE_BIN', 'gifsicle'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Hints (Preconnect / DNS-Prefetch)
    |--------------------------------------------------------------------------
    */
    'hints' => [
        'preconnect' => [
            'https://fonts.googleapis.com',
            'https://fonts.gstatic.com',
        ],
        'dns_prefetch' => [
            '//fonts.googleapis.com',
            '//fonts.gstatic.com',
            '//www.google-analytics.com',
        ],
    ],
];
