<?php

namespace Eamirgh\Optimus\Tests\Unit;

use Eamirgh\Optimus\Negotiation\FormatNegotiator;
use Eamirgh\Optimus\Tests\TestCase;

class FormatNegotiatorTest extends TestCase
{
    private FormatNegotiator $negotiator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->negotiator = new FormatNegotiator();
    }

    public function test_it_negotiates_avif_when_supported_by_client_and_driver(): void
    {
        $accept = 'text/html,application/xhtml+xml,image/avif,image/webp,image/apng,*/*;q=0.8';
        $format = $this->negotiator->negotiate($accept, null, 'jpeg', ['avif', 'webp', 'jpeg']);

        $this->assertEquals('avif', $format);
    }

    public function test_it_negotiates_webp_when_avif_not_supported_by_driver(): void
    {
        $accept = 'image/avif,image/webp,image/png';
        $format = $this->negotiator->negotiate($accept, null, 'jpeg', ['webp', 'jpeg', 'png']);

        $this->assertEquals('webp', $format);
    }

    public function test_it_falls_back_when_modern_formats_missing(): void
    {
        $accept = 'image/png,image/*;q=0.8';
        $format = $this->negotiator->negotiate($accept, null, 'png', ['webp', 'jpeg', 'png']);

        $this->assertEquals('png', $format);
    }

    public function test_explicit_format_overrides_accept_header(): void
    {
        $accept = 'image/avif,image/webp';
        $format = $this->negotiator->negotiate($accept, 'png', 'jpeg', ['avif', 'webp', 'png']);

        $this->assertEquals('png', $format);
    }

    public function test_it_returns_correct_mime_type(): void
    {
        $this->assertEquals('image/avif', $this->negotiator->mimeTypeFor('avif'));
        $this->assertEquals('image/webp', $this->negotiator->mimeTypeFor('webp'));
        $this->assertEquals('image/jpeg', $this->negotiator->mimeTypeFor('jpeg'));
        $this->assertEquals('image/png', $this->negotiator->mimeTypeFor('png'));
    }
}
