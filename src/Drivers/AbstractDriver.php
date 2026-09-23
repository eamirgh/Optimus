<?php

namespace Eamirgh\Optimus\Drivers;

use Eamirgh\Optimus\Contracts\ImageDriverInterface;

abstract class AbstractDriver implements ImageDriverInterface
{
    public function __construct(
        protected array $config = []
    ) {}

    /**
     * Calculate output dimensions based on source dimensions and fit strategy.
     *
     * @return array{0: int, 1: int, 2: int, 3: int, 4: int, 5: int}
     *         [destWidth, destHeight, srcX, srcY, srcW, srcH]
     */
    protected function calculateDimensions(
        int $srcWidth,
        int $srcHeight,
        ?int $targetWidth,
        ?int $targetHeight,
        string $fit = 'cover'
    ): array {
        // If neither specified, keep original
        if (! $targetWidth && ! $targetHeight) {
            return [$srcWidth, $srcHeight, 0, 0, $srcWidth, $srcHeight];
        }

        // If only one specified, compute the other keeping aspect ratio
        if ($targetWidth && ! $targetHeight) {
            $ratio = $targetWidth / $srcWidth;
            $targetHeight = (int) round($srcHeight * $ratio);
            return [$targetWidth, $targetHeight, 0, 0, $srcWidth, $srcHeight];
        }

        if (! $targetWidth && $targetHeight) {
            $ratio = $targetHeight / $srcHeight;
            $targetWidth = (int) round($srcWidth * $ratio);
            return [$targetWidth, $targetHeight, 0, 0, $srcWidth, $srcHeight];
        }

        // Both specified:
        if ($fit === 'fill') {
            return [$targetWidth, $targetHeight, 0, 0, $srcWidth, $srcHeight];
        }

        if ($fit === 'contain') {
            $ratio = min($targetWidth / $srcWidth, $targetHeight / $srcHeight);
            $newW = (int) round($srcWidth * $ratio);
            $newH = (int) round($srcHeight * $ratio);
            return [$newW, $newH, 0, 0, $srcWidth, $srcHeight];
        }

        // Default 'cover': crop center
        $srcRatio = $srcWidth / $srcHeight;
        $targetRatio = $targetWidth / $targetHeight;

        if ($srcRatio > $targetRatio) {
            // Source is wider: crop sides
            $cropW = (int) round($srcHeight * $targetRatio);
            $cropH = $srcHeight;
            $srcX = (int) round(($srcWidth - $cropW) / 2);
            $srcY = 0;
        } else {
            // Source is taller: crop top/bottom
            $cropW = $srcWidth;
            $cropH = (int) round($srcWidth / $targetRatio);
            $srcX = 0;
            $srcY = (int) round(($srcHeight - $cropH) / 2);
        }

        return [$targetWidth, $targetHeight, $srcX, $srcY, $cropW, $cropH];
    }
}
