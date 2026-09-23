<?php

namespace Eamirgh\Optimus\Tests\Feature;

use Eamirgh\Optimus\Tests\TestCase;
use Eamirgh\Optimus\View\Components\Link;
use Illuminate\Support\Facades\Blade;

class BladeComponentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Link::resetObserverState();
    }

    public function test_image_component_renders_picture_with_avif_webp_and_fallback(): void
    {
        $html = Blade::render('<x-image src="hero.jpg" width="640" height="360" alt="Hero Banner" />');

        $this->assertStringContainsString('<picture>', $html);
        $this->assertStringContainsString('<source type="image/avif"', $html);
        $this->assertStringContainsString('<source type="image/webp"', $html);
        $this->assertStringContainsString('<img src=', $html);
        $this->assertStringContainsString('width="640"', $html);
        $this->assertStringContainsString('height="360"', $html);
        $this->assertStringContainsString('alt="Hero Banner"', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('decoding="async"', $html);
        $this->assertStringContainsString('1x', $html);
        $this->assertStringContainsString('1.5x', $html);
        $this->assertStringContainsString('2x', $html);
    }

    public function test_image_component_supports_fill_and_blur_placeholder(): void
    {
        $html = Blade::render('<x-image src="banner.jpg" width="800" height="400" fill="true" placeholder="blur" />');

        $this->assertStringContainsString('position:absolute', $html);
        $this->assertStringContainsString('object-fit:cover', $html);
        $this->assertStringContainsString('background-image:url(\'data:image/svg+xml;base64,', $html);
    }

    public function test_link_component_renders_prefetch_attributes_and_hints(): void
    {
        $html = Blade::render('<x-link href="/products" :preconnect="[\'https://api.cdn.com\']" preload="true" as="fetch">View Products</x-link>');

        $this->assertStringContainsString('href="/products"', $html);
        $this->assertStringContainsString('data-optimus-prefetch', $html);
        $this->assertStringContainsString('<link rel="preconnect" href="https://api.cdn.com">', $html);
        $this->assertStringContainsString('<link rel="dns-prefetch" href="https://api.cdn.com">', $html);
        $this->assertStringContainsString('<link rel="preload" href="/products" as="fetch">', $html);
        $this->assertStringContainsString('IntersectionObserver', $html);
    }

    public function test_script_component_supports_strategies(): void
    {
        // 1. beforeInteractive
        $htmlBefore = Blade::render('<x-script src="https://cdn.com/critical.js" strategy="beforeInteractive" />');
        $this->assertStringContainsString('<script src="https://cdn.com/critical.js"></script>', $htmlBefore);
        $this->assertStringNotContainsString('defer', $htmlBefore);

        // 2. afterInteractive
        $htmlAfter = Blade::render('<x-script src="https://cdn.com/deferred.js" strategy="afterInteractive" />');
        $this->assertStringContainsString('defer', $htmlAfter);

        // 3. lazyOnload
        $htmlLazy = Blade::render('<x-script src="https://cdn.com/widget.js" strategy="lazyOnload" />');
        $this->assertStringContainsString("window.addEventListener('load'", $htmlLazy);
    }

    public function test_google_analytics_component_renders_gtag(): void
    {
        $html = Blade::render('<x-ga id="G-ABC12345" />');

        $this->assertStringContainsString('googletagmanager.com/gtag/js?id=G-ABC12345', $html);
        $this->assertStringContainsString('defer', $html);
        $this->assertStringContainsString("gtag('config', 'G-ABC12345');", $html);
    }

    public function test_optimus_css_bg_directive_renders_cross_browser_background(): void
    {
        $html = Blade::render("@optimusCssBg('backgrounds/hero.png', ['width' => 1920, 'height' => 1080])");

        $this->assertStringContainsString('background-image: url(', $html);
        $this->assertStringContainsString('-webkit-image-set', $html);
        $this->assertStringContainsString('image-set(', $html);
        $this->assertStringContainsString("type('image/avif')", $html);
        $this->assertStringContainsString("type('image/webp')", $html);
    }
}
