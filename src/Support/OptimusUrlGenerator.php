<?php

namespace Eamirgh\Optimus\Support;

use Eamirgh\Optimus\Security\SignatureGenerator;

class OptimusUrlGenerator
{
    public function __construct(
        protected SignatureGenerator $signatureGenerator,
        protected ?string $cdnUrl = null,
        protected string $prefix = 'optimus'
    ) {}

    /**
     * Generate a signed URL for an image transformation.
     */
    public function url(string $path, array $options = []): string
    {
        $params = [];

        if (! empty($options['width'])) {
            $params['w'] = (int) $options['width'];
        }

        if (! empty($options['height'])) {
            $params['h'] = (int) $options['height'];
        }

        if (! empty($options['fit'])) {
            $params['fit'] = $options['fit'];
        }

        if (! empty($options['quality'])) {
            $params['q'] = (int) $options['quality'];
        }

        if (! empty($options['format'])) {
            $params['fm'] = $options['format'];
        }

        $cleanPath = ltrim($path, '/');
        $signature = $this->signatureGenerator->generate($cleanPath, $params);
        $params['s'] = $signature;

        $base = $this->cdnUrl ? rtrim($this->cdnUrl, '/') : url($this->prefix);

        return $base . '/' . $cleanPath . '?' . http_build_query($params);
    }
}
