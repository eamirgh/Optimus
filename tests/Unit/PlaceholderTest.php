<?php

namespace Eamirgh\Optimus\Tests\Unit;

use Eamirgh\Optimus\Placeholders\BlurPlaceholder;
use Eamirgh\Optimus\Placeholders\BlurhashPlaceholder;
use Eamirgh\Optimus\Placeholders\SqipPlaceholder;
use Eamirgh\Optimus\Tests\TestCase;

class PlaceholderTest extends TestCase
{
    public function test_blur_placeholder_makes_valid_svg_data_uri(): void
    {
        $uri = BlurPlaceholder::makeSvg(64, 48, '#3b82f6');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $uri);
        $decoded = base64_decode(substr($uri, strlen('data:image/svg+xml;base64,')));
        $this->assertStringContainsString('<svg', $decoded);
        $this->assertStringContainsString('feGaussianBlur', $decoded);
        $this->assertStringContainsString('#3b82f6', $decoded);
    }

    public function test_blur_placeholder_generates_from_binary(): void
    {
        $im = imagecreatetruecolor(50, 50);
        ob_start();
        imagepng($im);
        $bin = ob_get_clean();
        imagedestroy($im);

        $uri = BlurPlaceholder::fromBinary($bin, 16);
        $this->assertStringStartsWith('data:image/', $uri);
        $this->assertStringContainsString(';base64,', $uri);
    }

    public function test_blurhash_placeholder_generates_svg_data_uri(): void
    {
        $uri = BlurhashPlaceholder::toDataUri('L6PZfSi_.AyE_3t7t7R**0o#DgR4', 32, 32);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $uri);
        $decoded = base64_decode(substr($uri, strlen('data:image/svg+xml;base64,')));
        $this->assertStringContainsString('linearGradient', $decoded);
    }

    public function test_sqip_placeholder_generates_svg_primitives_data_uri(): void
    {
        $uri = SqipPlaceholder::make(40, 30);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $uri);
        $decoded = base64_decode(substr($uri, strlen('data:image/svg+xml;base64,')));
        $this->assertStringContainsString('<circle', $decoded);
        $this->assertStringContainsString('feGaussianBlur', $decoded);
    }
}
