<?php

namespace Eamirgh\Optimus\Drivers;

use RuntimeException;

class ImgproxyDriver extends AbstractDriver
{
    protected string $baseUrl;
    protected string $key;
    protected string $salt;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->baseUrl = rtrim($config['base_url'] ?? 'http://localhost:8080', '/');
        $this->key = $config['key'] ?? '';
        $this->salt = $config['salt'] ?? '';
    }

    public function supportedFormats(): array
    {
        return ['avif', 'webp', 'jpeg', 'jpg', 'png', 'gif', 'svg'];
    }

    public function process(string $sourceBinaryOrPath, array $options = []): string
    {
        throw new RuntimeException('ImgproxyDriver does not process local binaries directly. Use generateUrl() for edge delivery.');
    }

    public function generateUrl(string $sourcePath, array $options = []): string
    {
        $w = $options['width'] ?? 0;
        $h = $options['height'] ?? 0;
        $fit = $options['fit'] ?? 'fill';
        $gravity = $options['gravity'] ?? 'no';
        $enlarge = $options['enlarge'] ?? 0;
        $extension = $options['format'] ?? 'webp';

        // URL-safe base64 encode source path/URL
        $encodedUrl = rtrim(strtr(base64_encode($sourcePath), '+/', '-_'), '=');

        $path = sprintf('/rs:%s:%d:%d:%d/g:%s/%s.%s', $fit, $w, $h, $enlarge, $gravity, $encodedUrl, $extension);

        if (empty($this->key) || empty($this->salt)) {
            // Unsigned url
            return $this->baseUrl . '/insecure' . $path;
        }

        $keyBin = pack('H*', $this->key);
        $saltBin = pack('H*', $this->salt);

        $signature = hash_hmac('sha256', $saltBin . $path, $keyBin, true);
        $encodedSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return $this->baseUrl . '/' . $encodedSignature . $path;
    }
}
