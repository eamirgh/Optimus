<?php

namespace Eamirgh\Optimus\Exceptions;

use RuntimeException;

class InvalidDimensionException extends RuntimeException
{
    public static function notAllowed(?int $width, ?int $height): self
    {
        $w = $width ?? 'auto';
        $h = $height ?? 'auto';

        return new self("Requested dimensions [{$w}x{$h}] are not permitted by the Optimus whitelist.");
    }

    public static function exceedsMaximum(?int $width, ?int $height, int $maxWidth, int $maxHeight): self
    {
        return new self("Dimensions [{$width}x{$height}] exceed configured maximum [{$maxWidth}x{$maxHeight}].");
    }
}
