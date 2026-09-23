<?php

namespace Eamirgh\Optimus\Placeholders;

class SqipPlaceholder
{
    /**
     * Generate an SVG placeholder using geometric primitive simulation and blur.
     */
    public static function make(int $width = 32, int $height = 32, array $colors = ['#cbd5e1', '#94a3b8', '#64748b']): string
    {
        $c1 = $colors[0] ?? '#cbd5e1';
        $c2 = $colors[1] ?? '#94a3b8';
        $c3 = $colors[2] ?? '#64748b';

        $r1 = (int) round($width * 0.4);
        $r2 = (int) round($width * 0.3);

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d"><filter id="b"><feGaussianBlur stdDeviation="12"/></filter><g filter="url(#b)"><rect width="100%%" height="100%%" fill="%s"/><circle cx="%d" cy="%d" r="%d" fill="%s"/><circle cx="%d" cy="%d" r="%d" fill="%s"/></g></svg>',
            $width,
            $height,
            $c1,
            (int) ($width * 0.3),
            (int) ($height * 0.4),
            $r1,
            $c2,
            (int) ($width * 0.7),
            (int) ($height * 0.6),
            $r2,
            $c3
        );

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
