<?php

namespace Eamirgh\Optimus\Tests\Unit;

use Eamirgh\Optimus\Drivers\ImgproxyDriver;
use Eamirgh\Optimus\Tests\TestCase;

class ImgproxyDriverTest extends TestCase
{
    public function test_it_generates_insecure_url_when_keys_not_provided(): void
    {
        $driver = new ImgproxyDriver([
            'base_url' => 'https://imgproxy.example.com',
            'key' => '',
            'salt' => '',
        ]);

        $url = $driver->generateUrl('https://example.com/banner.jpg', [
            'width' => 640,
            'height' => 360,
            'format' => 'webp',
            'fit' => 'fill',
        ]);

        $this->assertStringStartsWith('https://imgproxy.example.com/insecure/rs:fill:640:360:0/g:no/', $url);
        $this->assertStringEndsWith('.webp', $url);
    }

    public function test_it_generates_signed_imgproxy_url_with_hex_keys(): void
    {
        // 32-byte hex key and salt
        $keyHex = bin2hex('secret-key-32-bytes-long-here!!');
        $saltHex = bin2hex('secret-salt-32-bytes-long-here!!');

        $driver = new ImgproxyDriver([
            'base_url' => 'https://img.cdn.com',
            'key' => $keyHex,
            'salt' => $saltHex,
        ]);

        $url = $driver->generateUrl('https://example.com/avatar.png', [
            'width' => 128,
            'height' => 128,
            'format' => 'avif',
        ]);

        $this->assertStringStartsWith('https://img.cdn.com/', $url);
        $this->assertStringNotContainsString('/insecure/', $url);
        $this->assertStringEndsWith('.avif', $url);
    }
}
