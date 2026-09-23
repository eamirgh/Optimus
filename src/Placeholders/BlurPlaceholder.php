<?php

namespace Eamirgh\Optimus\Placeholders;

use GdImage;

class BlurPlaceholder
{
    /**
     * Generate inline base64 SVG placeholder with Gaussian blur filter.
     */
    public static function makeSvg(int $width = 32, int $height = 32, string $solidColor = '#e2e8f0'): string
    {
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d"><filter id="b" color-interpolation-filters="sRGB"><feGaussianBlur stdDeviation="20"/><feColorMatrix values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 100 -1" result="s"/><feFlood x="0" y="0" width="100%%" height="100%%"/></filter><rect width="100%%" height="100%%" fill="%s" filter="url(#b)"/></svg>',
            $width,
            $height,
            htmlspecialchars($solidColor, ENT_QUOTES, 'UTF-8')
        );

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Generate a micro-WebP/JPEG (e.g. 16x16) base64 data URI from source image.
     */
    public static function fromBinary(string $binary, int $microWidth = 16): string
    {
        if (! extension_loaded('gd')) {
            return self::makeSvg($microWidth, $microWidth);
        }

        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            return self::makeSvg($microWidth, $microWidth);
        }

        $w = imagesx($image);
        $h = imagesy($image);
        $microHeight = (int) max(1, round($h * ($microWidth / $w)));

        $micro = imagecreatetruecolor($microWidth, $microHeight);
        imagecopyresampled($micro, $image, 0, 0, 0, 0, $microWidth, $microHeight, $w, $h);

        ob_start();
        if (function_exists('imagewebp')) {
            imagewebp($micro, null, 20);
            $mime = 'image/webp';
        } else {
            imagejpeg($micro, null, 20);
            $mime = 'image/jpeg';
        }
        $data = ob_get_clean();

        imagedestroy($image);
        imagedestroy($micro);

        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }
}
