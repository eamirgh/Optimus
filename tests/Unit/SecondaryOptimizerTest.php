<?php

namespace Eamirgh\Optimus\Tests\Unit;

use Eamirgh\Optimus\Optimization\SecondaryOptimizer;
use Eamirgh\Optimus\Tests\TestCase;

class SecondaryOptimizerTest extends TestCase
{
    public function test_it_returns_binary_intact_when_disabled(): void
    {
        $optimizer = new SecondaryOptimizer(enabled: false);
        $binary = 'fake-image-binary-data';

        $this->assertEquals($binary, $optimizer->optimize($binary, 'jpeg'));
    }

    public function test_it_returns_binary_gracefully_when_binaries_not_installed(): void
    {
        $optimizer = new SecondaryOptimizer(enabled: true, binaries: [
            'jpegoptim' => '/non/existent/bin/jpegoptim',
            'pngquant' => '/non/existent/bin/pngquant',
        ]);

        $binary = 'binary-stream-12345';
        $this->assertEquals($binary, $optimizer->optimize($binary, 'jpeg'));
        $this->assertEquals($binary, $optimizer->optimize($binary, 'png'));
    }
}
