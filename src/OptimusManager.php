<?php

namespace Eamirgh\Optimus;

use Illuminate\Support\Manager;
use Eamirgh\Optimus\Contracts\ImageDriverInterface;
use Eamirgh\Optimus\Drivers\GdDriver;
use Eamirgh\Optimus\Drivers\ImagickDriver;
use Eamirgh\Optimus\Drivers\ImgproxyDriver;
use Eamirgh\Optimus\Drivers\CloudinaryDriver;
use InvalidArgumentException;

class OptimusManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('optimus.driver', 'gd');
    }

    public function createGdDriver(): ImageDriverInterface
    {
        return new GdDriver($this->config->get('optimus'));
    }

    public function createImagickDriver(): ImageDriverInterface
    {
        return new ImagickDriver($this->config->get('optimus'));
    }

    public function createImgproxyDriver(): ImageDriverInterface
    {
        return new ImgproxyDriver($this->config->get('optimus.drivers.imgproxy', []));
    }

    public function createCloudinaryDriver(): ImageDriverInterface
    {
        return new CloudinaryDriver($this->config->get('optimus.drivers.cloudinary', []));
    }
}
