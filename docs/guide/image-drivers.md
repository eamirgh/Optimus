# Image Processing Drivers

Laravel Optimus implements the **Strategy Pattern** for image transformations, supporting local server rendering and high-scale edge microservices.

## 1. GD Driver (`gd`)

The default driver. Bundled with almost every PHP installation.

- **Formats**: JPEG, PNG, GIF, WebP (and AVIF if compiled with libavif).
- **Pros**: Zero third-party dependencies, low memory footprint.
- **Cons**: CPU-bound on monolithic servers under high concurrent load.

## 2. Imagick Driver (`imagick`)

Utilizes ImageMagick C-bindings (`ext-imagick`).

- **Formats**: AVIF, WebP, JPEG, PNG, GIF.
- **Features**: Lanczos resampling filter, color space preservation, and ICC color profile retention.
- **Recommendation**: Best for high visual fidelity on self-hosted servers.

## 3. Imgproxy Driver (`imgproxy`)

Offloads image transformation entirely to an external [imgproxy](https://imgproxy.net/) cluster or container.

```php
// config/optimus.php
'driver' => 'imgproxy',
'drivers' => [
    'imgproxy' => [
        'base_url' => env('IMGPROXY_URL', 'https://img.yourcdn.com'),
        'key' => env('IMGPROXY_KEY', 'hex-encoded-key'),
        'salt' => env('IMGPROXY_SALT', 'hex-encoded-salt'),
    ],
],
```

Optimus generates cryptographically signed URLs for imgproxy, shielding your PHP workers from all image processing work.

## 4. Cloudinary Driver (`cloudinary`)

Connects directly to Cloudinary SaaS image optimization.

```php
// config/optimus.php
'driver' => 'cloudinary',
'drivers' => [
    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME', 'my-cloud'),
        'api_key' => env('CLOUDINARY_API_KEY', ''),
        'api_secret' => env('CLOUDINARY_API_SECRET', ''),
    ],
],
```

Generates Cloudinary transformation paths (`/image/upload/c_fill,w_...,h_...,q_auto,f_auto/...`) pointing to Cloudinary's worldwide CDN.
