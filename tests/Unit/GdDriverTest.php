<?php

namespace Eamirgh\Optimus\Tests\Unit;

use Eamirgh\Optimus\Drivers\GdDriver;
use Eamirgh\Optimus\Tests\TestCase;

class GdDriverTest extends TestCase
{
    private GdDriver $driver;
    private string $sampleImageBinary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->driver = new GdDriver([]);

        // Create a 200x100 synthetic test image
        $im = imagecreatetruecolor(200, 100);
        $red = imagecolorallocate($im, 255, 0, 0);
        imagefilledrectangle($im, 0, 0, 200, 100, $red);
        ob_start();
        imagepng($im);
        $this->sampleImageBinary = ob_get_clean();
        imagedestroy($im);
    }

    public function test_it_reports_supported_formats(): void
    {
        $formats = $this->driver->supportedFormats();
        $this->assertContains('jpeg', $formats);
        $this->assertContains('png', $formats);
    }

    public function test_it_resizes_image_to_specified_dimensions(): void
    {
        $output = $this->driver->process($this->sampleImageBinary, [
            'width' => 100,
            'height' => 50,
            'format' => 'png',
            'fit' => 'cover',
        ]);

        $this->assertNotEmpty($output);

        $resImage = imagecreatefromstring($output);
        $this->assertNotFalse($resImage);
        $this->assertEquals(100, imagesx($resImage));
        $this->assertEquals(50, imagesy($resImage));
        imagedestroy($resImage);
    }

    public function test_it_resizes_with_auto_height(): void
    {
        $output = $this->driver->process($this->sampleImageBinary, [
            'width' => 100,
            'height' => null,
            'format' => 'jpeg',
            'quality' => 85,
        ]);

        $this->assertNotEmpty($output);

        $resImage = imagecreatefromstring($output);
        $this->assertNotFalse($resImage);
        $this->assertEquals(100, imagesx($resImage));
        // Source was 200x100 (2:1 ratio) => 100x50
        $this->assertEquals(50, imagesy($resImage));
        imagedestroy($resImage);
    }

    public function test_it_converts_to_webp_format(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('imagewebp not available in GD');
        }

        $output = $this->driver->process($this->sampleImageBinary, [
            'width' => 64,
            'height' => 32,
            'format' => 'webp',
            'quality' => 75,
        ]);

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('RIFF', $output);
        $this->assertStringContainsString('WEBP', substr($output, 0, 16));
    }
}
