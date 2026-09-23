<?php

namespace Eamirgh\Optimus\Tests\Unit;

use Eamirgh\Optimus\Drivers\CloudinaryDriver;
use Eamirgh\Optimus\Tests\TestCase;

class CloudinaryDriverTest extends TestCase
{
    public function test_it_generates_cloudinary_transformation_url(): void
    {
        $driver = new CloudinaryDriver([
            'cloud_name' => 'demo-cloud',
        ]);

        $url = $driver->generateUrl('sample_image.jpg', [
            'width' => 800,
            'height' => 600,
            'fit' => 'cover',
            'quality' => 80,
            'format' => 'webp',
        ]);

        $this->assertEquals(
            'https://res.cloudinary.com/demo-cloud/image/upload/c_fill,w_800,h_600,q_80,f_webp/sample_image.jpg',
            $url
        );
    }
}
