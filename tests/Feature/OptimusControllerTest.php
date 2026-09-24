<?php

namespace Eamirgh\Optimus\Tests\Feature;

use Eamirgh\Optimus\Security\SignatureGenerator;
use Eamirgh\Optimus\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class OptimusControllerTest extends TestCase
{
    private string $testImagePath = 'test_hero.png';

    protected function setUp(): void
    {
        parent::setUp();

        // Create a 640x480 test image in storage
        $im = imagecreatetruecolor(640, 480);
        $blue = imagecolorallocate($im, 0, 100, 255);
        imagefilledrectangle($im, 0, 0, 640, 480, $blue);
        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);

        Storage::disk('public')->put($this->testImagePath, $png);
    }

    public function test_it_returns_403_when_signature_is_missing(): void
    {
        $response = $this->get('/optimus/' . $this->testImagePath . '?w=320&h=240');
        $response->assertStatus(403);
    }

    public function test_it_returns_403_when_signature_is_tampered(): void
    {
        $response = $this->get('/optimus/' . $this->testImagePath . '?w=320&h=240&s=invalid_signature_hash');
        $response->assertStatus(403);
    }

    public function test_it_returns_400_when_dimensions_are_not_whitelisted(): void
    {
        $sigGen = app(SignatureGenerator::class);
        $params = ['w' => 777, 'h' => 999];
        $sig = $sigGen->generate($this->testImagePath, $params);

        $response = $this->get('/optimus/' . $this->testImagePath . '?w=777&h=999&s=' . $sig);
        $response->assertStatus(400);
    }

    public function test_it_serves_processed_image_with_immutable_cache_headers(): void
    {
        $sigGen = app(SignatureGenerator::class);
        $params = ['w' => 320, 'h' => 240, 'fm' => 'png'];
        $sig = $sigGen->generate($this->testImagePath, $params);

        $response = $this->get('/optimus/' . $this->testImagePath . '?w=320&h=240&fm=png&s=' . $sig);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('max-age=31536000', $cacheControl);
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('immutable', $cacheControl);
        $this->assertNotEmpty($response->headers->get('ETag'));
        $this->assertNotEmpty($response->getContent());

        // Check image was written to cache disk
        $cacheSubdir = config('optimus.cache_path', 'optimus');
        $cachedFiles = Storage::disk('public')->files($cacheSubdir);
        $this->assertNotEmpty($cachedFiles);

        // Subsequent hit serves from cache
        $secondResponse = $this->get('/optimus/' . $this->testImagePath . '?w=320&h=240&fm=png&s=' . $sig);
        $secondResponse->assertStatus(200);
        $secondResponse->assertHeader('Content-Type', 'image/png');
    }
}
