<?php

namespace Eamirgh\Optimus\Tests\Unit;

use Eamirgh\Optimus\Security\SignatureGenerator;
use Eamirgh\Optimus\Tests\TestCase;

class SignatureGeneratorTest extends TestCase
{
    private SignatureGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new SignatureGenerator('secret-test-key');
    }

    public function test_it_generates_consistent_hmac_signature(): void
    {
        $sig1 = $this->generator->generate('/images/hero.jpg', ['w' => 800, 'h' => 600, 'q' => 80]);
        $sig2 = $this->generator->generate('/images/hero.jpg', ['h' => 600, 'w' => 800, 'q' => 80]);

        $this->assertNotEmpty($sig1);
        $this->assertEquals($sig1, $sig2, 'Parameter order must not affect signature');
    }

    public function test_it_validates_correct_signature(): void
    {
        $params = ['w' => 640, 'h' => 480];
        $signature = $this->generator->generate('products/shirt.png', $params);

        $this->assertTrue($this->generator->validate($signature, 'products/shirt.png', $params));
        $this->assertFalse($this->generator->validate($signature, 'products/shirt.png', ['w' => 641, 'h' => 480]));
        $this->assertFalse($this->generator->validate('tampered-signature', 'products/shirt.png', $params));
    }

    public function test_it_generates_valid_signed_url(): void
    {
        $signedUrl = $this->generator->signUrl('https://example.com/optimus', 'hero.jpg', ['w' => 1280, 'h' => 720]);

        $this->assertStringStartsWith('https://example.com/optimus/hero.jpg?', $signedUrl);
        $this->assertStringContainsString('w=1280', $signedUrl);
        $this->assertStringContainsString('h=720', $signedUrl);
        $this->assertStringContainsString('s=', $signedUrl);

        parse_str(parse_url($signedUrl, PHP_URL_QUERY), $query);
        $this->assertArrayHasKey('s', $query);

        $sig = $query['s'];
        unset($query['s']);

        $this->assertTrue($this->generator->validate($sig, 'hero.jpg', $query));
    }
}
