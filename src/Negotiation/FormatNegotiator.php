<?php

namespace Eamirgh\Optimus\Negotiation;

class FormatNegotiator
{
    protected const MIME_MAP = [
        'avif' => 'image/avif',
        'webp' => 'image/webp',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
    ];

    /**
     * Determine best format based on Accept header, explicit preference, and driver capabilities.
     *
     * @param string|null $acceptHeader
     * @param string|null $explicitFormat
     * @param string $fallback
     * @param array<string> $driverSupportedFormats
     * @return string
     */
    public function negotiate(
        ?string $acceptHeader,
        ?string $explicitFormat = null,
        string $fallback = 'jpeg',
        array $driverSupportedFormats = ['avif', 'webp', 'jpeg', 'jpg', 'png', 'gif']
    ): string {
        // If explicit format requested and supported by driver
        if ($explicitFormat !== null && in_array(strtolower($explicitFormat), $driverSupportedFormats, true)) {
            return strtolower($explicitFormat);
        }

        if (empty($acceptHeader)) {
            return in_array($fallback, $driverSupportedFormats, true) ? $fallback : 'jpeg';
        }

        $header = strtolower($acceptHeader);

        // Check AVIF first (highest compression ratio)
        if (str_contains($header, 'image/avif') && in_array('avif', $driverSupportedFormats, true)) {
            return 'avif';
        }

        // Check WebP second
        if (str_contains($header, 'image/webp') && in_array('webp', $driverSupportedFormats, true)) {
            return 'webp';
        }

        // Return fallback format
        return in_array($fallback, $driverSupportedFormats, true) ? $fallback : 'jpeg';
    }

    /**
     * Get MIME type for format string.
     */
    public function mimeTypeFor(string $format): string
    {
        $fmt = strtolower($format);

        return self::MIME_MAP[$fmt] ?? 'application/octet-stream';
    }
}
