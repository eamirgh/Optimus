<?php

namespace Eamirgh\Optimus\Security;

class SignatureGenerator
{
    public function __construct(
        protected string $key
    ) {}

    /**
     * Generate an HMAC-SHA256 signature for the given path and parameters.
     */
    public function generate(string $path, array $params = []): string
    {
        $payload = $this->buildPayload($path, $params);

        return hash_hmac('sha256', $payload, $this->key);
    }

    /**
     * Validate an HMAC-SHA256 signature using timing attack-safe hash_equals.
     */
    public function validate(string $signature, string $path, array $params = []): bool
    {
        $expected = $this->generate($path, $params);

        return hash_equals($expected, $signature);
    }

    /**
     * Generate a signed URL with the signature appended as a query parameter or path segment.
     */
    public function signUrl(string $baseUrl, string $path, array $params = []): string
    {
        $signature = $this->generate($path, $params);
        $params['s'] = $signature;

        $queryString = http_build_query($params);
        $separator = str_contains($path, '?') ? '&' : '?';

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/') . ($queryString ? $separator . $queryString : '');
    }

    /**
     * Build canonical deterministic payload for signing.
     */
    protected function buildPayload(string $path, array $params = []): string
    {
        // Strip signature parameter if present
        unset($params['s'], $params['signature']);

        // Normalize path
        $cleanPath = '/' . ltrim(parse_url($path, PHP_URL_PATH) ?? $path, '/');

        // Sort parameters for canonical representation
        ksort($params);

        // Normalize nulls and booleans
        $normalized = [];
        foreach ($params as $key => $val) {
            if ($val !== null && $val !== '') {
                $normalized[$key] = (string) $val;
            }
        }

        return $cleanPath . '?' . http_build_query($normalized);
    }
}
