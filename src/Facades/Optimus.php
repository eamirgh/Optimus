<?php

namespace Eamirgh\Optimus\Facades;

use Illuminate\Support\Facades\Facade;

class Optimus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'optimus';
    }
}
