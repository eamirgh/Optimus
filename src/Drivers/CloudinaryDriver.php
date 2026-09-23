<?php

namespace Eamirgh\Optimus\Drivers;

use RuntimeException;

class CloudinaryDriver extends AbstractDriver
{
    protected string $cloudName;
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->cloudName = $config['cloud_name'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiSecret = $config['api_secret'] ?? '';
    }

    public function supportedFormats(): array
    {
        return ['avif', 'webp', 'auto', 'jpeg', 'jpg', 'png', 'gif'];
    }

    public function process(string $sourceBinaryOrPath, array $options = []): string
    {
        throw new RuntimeException('CloudinaryDriver generates edge URLs. It does not perform local binary processing.');
    }

    public function generateUrl(string $sourcePath, array $options = []): string
    {
        if (empty($this->cloudName)) {
            throw new RuntimeException('Cloudinary cloud_name must be configured.');
        }

        $transforms = [];

        $fitMap = [
            'cover' => 'fill',
            'contain' => 'fit',
            'crop' => 'crop',
            'fill' => 'scale',
        ];

        $fit = $options['fit'] ?? 'cover';
        $transforms[] = 'c_' . ($fitMap[$fit] ?? $fit);

        if (! empty($options['width'])) {
            $transforms[] = 'w_' . (int) $options['width'];
        }

        if (! empty($options['height'])) {
            $transforms[] = 'h_' . (int) $options['height'];
        }

        if (! empty($options['quality'])) {
            $transforms[] = 'q_' . (int) $options['quality'];
        } else {
            $transforms[] = 'q_auto';
        }

        $format = $options['format'] ?? 'auto';
        $transforms[] = 'f_' . $format;

        $transformString = implode(',', $transforms);
        $publicId = ltrim($sourcePath, '/');

        return sprintf(
            'https://res.cloudinary.com/%s/image/upload/%s/%s',
            $this->cloudName,
            $transformString,
            $publicId
        );
    }
}
