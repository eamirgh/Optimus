<?php

namespace Eamirgh\Optimus\Tests\Unit;

use Eamirgh\Optimus\Exceptions\InvalidDimensionException;
use Eamirgh\Optimus\Security\DimensionValidator;
use Eamirgh\Optimus\Tests\TestCase;

class DimensionValidatorTest extends TestCase
{
    private DimensionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new DimensionValidator(
            allowedDimensions: [
                [320, 240],
                [640, 480],
                [1280, 720],
                [800, null], // Allowed width with auto height
            ],
            maxWidth: 1920,
            maxHeight: 1080
        );
    }

    public function test_it_allows_exact_whitelisted_dimensions(): void
    {
        $this->assertTrue($this->validator->isValid(320, 240));
        $this->assertTrue($this->validator->isValid(640, 480));
        $this->assertTrue($this->validator->isValid(1280, 720));
        $this->assertTrue($this->validator->isValid(800, null));
    }

    public function test_it_rejects_unwhitelisted_dimensions(): void
    {
        $this->assertFalse($this->validator->isValid(500, 500));
        $this->assertFalse($this->validator->isValid(999, 999));
        $this->assertFalse($this->validator->isValid(320, 241));
    }

    public function test_it_rejects_dimensions_exceeding_maximum_bounds(): void
    {
        $this->assertFalse($this->validator->isValid(2000, 1000));
        $this->assertFalse($this->validator->isValid(1000, 2000));
        $this->assertFalse($this->validator->isValid(-10, 200));
    }

    public function test_it_throws_exception_on_invalid_dimensions(): void
    {
        $this->expectException(InvalidDimensionException::class);
        $this->validator->validate(444, 444);
    }
}
