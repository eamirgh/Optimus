<?php

namespace Eamirgh\Optimus\Exceptions;

use RuntimeException;

class InvalidSignatureException extends RuntimeException
{
    public static function mismatch(): self
    {
        return new self("The HMAC-SHA256 signature for this image request is invalid or missing.");
    }
}
