<?php

namespace Eamirgh\Optimus\Placeholders;

class BlurhashPlaceholder
{
    /**
     * Convert blurhash string into a lightweight CSS background inline style or data URI.
     */
    public static function toDataUri(string $blurhash, int $width = 32, int $height = 32): string
    {
        // Generates an inline SVG with linearGradient approximation from blurhash
        $color1 = self::hashToHex(substr($blurhash, 0, 4));
        $color2 = self::hashToHex(substr($blurhash, 4, 4));

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%%" stop-color="%s"/><stop offset="100%%" stop-color="%s"/></linearGradient></defs><rect width="100%%" height="100%%" fill="url(#g)"/></svg>',
            $width,
            $height,
            $color1,
            $color2
        );

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    protected static function hashToHex(string $sub): string
    {
        $hash = crc32($sub);
        return sprintf('#%06X', $hash & 0xFFFFFF);
    }
}
