<?php

namespace Eamirgh\Optimus\View\Components;

use Eamirgh\Optimus\Placeholders\BlurPlaceholder;
use Eamirgh\Optimus\Placeholders\BlurhashPlaceholder;
use Eamirgh\Optimus\Placeholders\SqipPlaceholder;
use Eamirgh\Optimus\Support\OptimusUrlGenerator;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Image extends Component
{
    public ?int $width;
    public ?int $height;
    public ?int $quality;
    public bool $fill;
    public bool $picture;

    public function __construct(
        public string $src,
        public string $alt = '',
        int|string|null $width = null,
        int|string|null $height = null,
        public string $fit = 'cover',
        int|string|null $quality = null,
        public string $loading = 'lazy',
        public string $decoding = 'async',
        bool|string $fill = false,
        public ?string $placeholder = null,
        public ?string $blurhash = null,
        public string $placeholderColor = '#e2e8f0',
        bool|string $picture = true,
        public ?string $class = null
    ) {
        $this->width = ($width !== null && $width !== '') ? (int) $width : null;
        $this->height = ($height !== null && $height !== '') ? (int) $height : null;
        $this->quality = ($quality !== null && $quality !== '') ? (int) $quality : null;
        $this->fill = is_bool($fill) ? $fill : filter_var($fill, FILTER_VALIDATE_BOOLEAN);
        $this->picture = is_bool($picture) ? $picture : filter_var($picture, FILTER_VALIDATE_BOOLEAN);
    }

    public function render(): View|string
    {
        $urlGenerator = app(OptimusUrlGenerator::class);

        $dprQualities = config('optimus.quality.dpr', [
            '1x' => 80,
            '1.5x' => 70,
            '2x' => 60,
        ]);

        $q1x = $this->quality ?? ($dprQualities['1x'] ?? 80);
        $q15x = $dprQualities['1.5x'] ?? 70;
        $q2x = $dprQualities['2x'] ?? 60;

        $buildSrcset = function (?string $format) use ($urlGenerator, $q1x, $q15x, $q2x) {
            $w = $this->width;
            $h = $this->height;

            $u1x = $urlGenerator->url($this->src, [
                'width' => $w,
                'height' => $h,
                'fit' => $this->fit,
                'quality' => $q1x,
                'format' => $format,
            ]);

            $w15x = $w ? (int) round($w * 1.5) : null;
            $h15x = $h ? (int) round($h * 1.5) : null;
            $u15x = $urlGenerator->url($this->src, [
                'width' => $w15x,
                'height' => $h15x,
                'fit' => $this->fit,
                'quality' => $q15x,
                'format' => $format,
            ]);

            $w2x = $w ? (int) ($w * 2) : null;
            $h2x = $h ? (int) ($h * 2) : null;
            $u2x = $urlGenerator->url($this->src, [
                'width' => $w2x,
                'height' => $h2x,
                'fit' => $this->fit,
                'quality' => $q2x,
                'format' => $format,
            ]);

            return "{$u1x} 1x, {$u15x} 1.5x, {$u2x} 2x";
        };

        $avifSrcset = $buildSrcset('avif');
        $webpSrcset = $buildSrcset('webp');
        $defaultSrcset = $buildSrcset(null);

        $defaultSrc = $urlGenerator->url($this->src, [
            'width' => $this->width,
            'height' => $this->height,
            'fit' => $this->fit,
            'quality' => $q1x,
        ]);

        // Placeholders
        $placeholderDataUri = null;
        if ($this->placeholder === 'blur') {
            $placeholderDataUri = BlurPlaceholder::makeSvg($this->width ?? 32, $this->height ?? 32, $this->placeholderColor);
        } elseif ($this->placeholder === 'blurhash' && $this->blurhash) {
            $placeholderDataUri = BlurhashPlaceholder::toDataUri($this->blurhash, $this->width ?? 32, $this->height ?? 32);
        } elseif ($this->placeholder === 'sqip') {
            $placeholderDataUri = SqipPlaceholder::make($this->width ?? 32, $this->height ?? 32);
        }

        // Inline CSS
        $styles = [];
        if ($this->fill) {
            $styles[] = 'position:absolute;top:0;left:0;right:0;bottom:0;width:100%;height:100%;object-fit:cover;';
        }
        if ($placeholderDataUri) {
            $styles[] = "background-image:url('{$placeholderDataUri}');background-size:cover;background-position:center;";
        }
        $styleAttr = ! empty($styles) ? ' style="' . implode('', $styles) . '"' : '';

        $classAttr = $this->class ? ' class="' . htmlspecialchars($this->class, ENT_QUOTES, 'UTF-8') . '"' : '';
        $widthAttr = $this->width ? ' width="' . $this->width . '"' : '';
        $heightAttr = $this->height ? ' height="' . $this->height . '"' : '';

        $imgTag = sprintf(
            '<img src="%s" srcset="%s"%s%s alt="%s" loading="%s" decoding="%s"%s%s>',
            htmlspecialchars($defaultSrc, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($defaultSrcset, ENT_QUOTES, 'UTF-8'),
            $widthAttr,
            $heightAttr,
            htmlspecialchars($this->alt, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($this->loading, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($this->decoding, ENT_QUOTES, 'UTF-8'),
            $classAttr,
            $styleAttr
        );

        if (! $this->picture) {
            return $imgTag;
        }

        return sprintf(
            "<picture>\n    <source type=\"image/avif\" srcset=\"%s\">\n    <source type=\"image/webp\" srcset=\"%s\">\n    %s\n</picture>",
            htmlspecialchars($avifSrcset, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($webpSrcset, ENT_QUOTES, 'UTF-8'),
            $imgTag
        );
    }
}
