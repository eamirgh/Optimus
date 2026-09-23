<?php

namespace Eamirgh\Optimus\Contracts;

interface ImageDriverInterface
{
    /**
     * Process source image and return the optimized binary string.
     *
     * @param string $sourceBinaryOrPath
     * @param array{
     *     width?: int|null,
     *     height?: int|null,
     *     fit?: string,
     *     format?: string,
     *     quality?: int
     * } $options
     * @return string
     */
    public function process(string $sourceBinaryOrPath, array $options = []): string;

    /**
     * Generate remote delivery URL (for edge drivers like Imgproxy / Cloudinary).
     */
    public function generateUrl(string $sourcePath, array $options = []): string;

    /**
     * List of image formats supported by this driver.
     *
     * @return array<string>
     */
    public function supportedFormats(): array;
}
