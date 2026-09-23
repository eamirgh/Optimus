<?php

namespace Eamirgh\Optimus\Drivers;

use RuntimeException;

class ImagickDriver extends AbstractDriver
{
    public function supportedFormats(): array
    {
        if (! extension_loaded('imagick')) {
            return [];
        }

        return ['avif', 'webp', 'jpeg', 'jpg', 'png', 'gif'];
    }

    public function process(string $sourceBinaryOrPath, array $options = []): string
    {
        if (! extension_loaded('imagick')) {
            throw new RuntimeException('Imagick extension (ext-imagick) is required to use ImagickDriver.');
        }

        $imagick = new \Imagick();

        if (file_exists($sourceBinaryOrPath)) {
            $imagick->readImage($sourceBinaryOrPath);
        } else {
            $imagick->readImageBlob($sourceBinaryOrPath);
        }

        $srcWidth = $imagick->getImageWidth();
        $srcHeight = $imagick->getImageHeight();

        $targetWidth = $options['width'] ?? null;
        $targetHeight = $options['height'] ?? null;
        $fit = $options['fit'] ?? 'cover';
        $format = strtolower($options['format'] ?? 'webp');
        $quality = $options['quality'] ?? 80;

        [$destW, $destH, $srcX, $srcY, $srcW, $srcH] = $this->calculateDimensions(
            $srcWidth,
            $srcHeight,
            $targetWidth,
            $targetHeight,
            $fit
        );

        // Crop source region if needed
        if ($srcW !== $srcWidth || $srcH !== $srcHeight || $srcX !== 0 || $srcY !== 0) {
            $imagick->cropImage($srcW, $srcH, $srcX, $srcY);
            $imagick->setImagePage($srcW, $srcH, 0, 0);
        }

        // Lanczos filter resampling
        $filter = defined('\Imagick::FILTER_LANCZOS') ? \Imagick::FILTER_LANCZOS : 1;
        $imagick->resizeImage($destW, $destH, $filter, 1);

        $imagick->setImageFormat($format);
        $imagick->setImageCompressionQuality($quality);

        // Strip non-color metadata to optimize size while retaining ICC profile
        $icc = $imagick->getImageProfile('icc');
        $imagick->stripImage();
        if ($icc) {
            $imagick->profileImage('icc', $icc);
        }

        $binary = $imagick->getImageBlob();
        $imagick->clear();
        $imagick->destroy();

        return $binary;
    }

    public function generateUrl(string $sourcePath, array $options = []): string
    {
        return '';
    }
}
