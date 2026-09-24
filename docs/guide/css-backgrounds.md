# CSS Backgrounds Directive

Modern CSS `image-set()` allows serving responsive AVIF and WebP backgrounds directly inside CSS declarations.

## `@optimusCssBg`

Optimus provides the `@optimusCssBg` Blade directive to generate cross-browser background CSS rules:

```html
<div style="@optimusCssBg('backgrounds/hero.png', ['width' => 1920, 'height' => 1080])">
    <h1>Welcome to Optimus</h1>
</div>
```

### Generated Output

The directive compiles down to a three-tier fallback rule:

```css
background-image: url('https://example.com/optimus/backgrounds/hero.png?...');
background-image: -webkit-image-set(
    url('https://example.com/optimus/backgrounds/hero.png?...&fm=avif') type('image/avif'),
    url('https://example.com/optimus/backgrounds/hero.png?...&fm=webp') type('image/webp'),
    url('https://example.com/optimus/backgrounds/hero.png?...') type('image/jpeg')
);
background-image: image-set(
    url('https://example.com/optimus/backgrounds/hero.png?...&fm=avif') type('image/avif') 1x,
    url('https://example.com/optimus/backgrounds/hero.png?...&fm=webp') type('image/webp') 1x,
    url('https://example.com/optimus/backgrounds/hero.png?...') 1x
);
```

Browsers supporting standard `image-set` pick AVIF or WebP automatically. Older browsers fall back gracefully to standard JPEG/PNG.
