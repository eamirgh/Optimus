<?php

namespace Eamirgh\Optimus\View\Directives;

use Eamirgh\Optimus\Support\OptimusUrlGenerator;

class OptimusBladeDirectives
{
    /**
     * Compile CSS background declaration with modern image-set formats and fallback.
     */
    public static function compileCssBg(string $imagePath, array $options = []): string
    {
        $generator = app(OptimusUrlGenerator::class);

        $avifUrl = $generator->url($imagePath, array_merge($options, ['format' => 'avif']));
        $webpUrl = $generator->url($imagePath, array_merge($options, ['format' => 'webp']));
        $fallbackUrl = $generator->url($imagePath, $options);

        return sprintf(
            "background-image: url('%s'); " .
            "background-image: -webkit-image-set(url('%s') type('image/avif'), url('%s') type('image/webp'), url('%s') type('image/jpeg')); " .
            "background-image: image-set(url('%s') type('image/avif') 1x, url('%s') type('image/webp') 1x, url('%s') 1x);",
            $fallbackUrl,
            $avifUrl, $webpUrl, $fallbackUrl,
            $avifUrl, $webpUrl, $fallbackUrl
        );
    }
}
