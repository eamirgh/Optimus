<?php

namespace Eamirgh\Optimus\Http\Controllers;

use Eamirgh\Optimus\Contracts\ImageDriverInterface;
use Eamirgh\Optimus\Contracts\OptimizerInterface;
use Eamirgh\Optimus\Negotiation\FormatNegotiator;
use Eamirgh\Optimus\Security\ConcurrencyLock;
use Eamirgh\Optimus\Security\DimensionValidator;
use Eamirgh\Optimus\Security\SignatureGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OptimusController extends Controller
{
    public function __construct(
        protected SignatureGenerator $signatureGenerator,
        protected DimensionValidator $dimensionValidator,
        protected FormatNegotiator $formatNegotiator,
        protected ConcurrencyLock $concurrencyLock,
        protected ImageDriverInterface $driver,
        protected OptimizerInterface $optimizer
    ) {}

    public function __invoke(Request $request, string $path): Response
    {
        $signature = $request->query('s');
        if (! $signature) {
            throw new HttpException(403, 'Missing HMAC-SHA256 signature.');
        }

        $params = $request->query();
        unset($params['s']);

        $cleanPath = ltrim($path, '/');
        if (! $this->signatureGenerator->validate($signature, $cleanPath, $params)) {
            throw new HttpException(403, 'Invalid or tampered HMAC signature.');
        }

        $width = $request->has('w') ? (int) $request->query('w') : null;
        $height = $request->has('h') ? (int) $request->query('h') : null;

        if (! $this->dimensionValidator->isValid($width, $height)) {
            throw new HttpException(400, 'Requested dimensions not permitted.');
        }

        $format = $this->formatNegotiator->negotiate(
            $request->header('Accept'),
            $request->query('fm'),
            'jpeg',
            $this->driver->supportedFormats()
        );

        $quality = $request->has('q')
            ? (int) $request->query('q')
            : (int) config('optimus.quality.default', 80);

        $fit = $request->query('fit', 'cover');

        // Check local disk cache path (try_files warm cache simulation)
        $diskName = config('optimus.disk', 'public');
        $cacheSubdir = config('optimus.cache_path', 'optimus');
        $storage = Storage::disk($diskName);

        $cacheFileName = sprintf(
            '%s/%s_%dx%d_q%d_%s.%s',
            $cacheSubdir,
            md5($cleanPath),
            $width ?? 0,
            $height ?? 0,
            $quality,
            $fit,
            $format
        );

        if ($storage->exists($cacheFileName)) {
            $cachedBinary = $storage->get($cacheFileName);
            return $this->buildResponse($cachedBinary, $format);
        }

        // Lock & generate
        $binary = $this->concurrencyLock->execute($cacheFileName, function () use (
            $cleanPath, $width, $height, $fit, $format, $quality, $storage, $cacheFileName
        ) {
            // Recheck cache inside lock
            if ($storage->exists($cacheFileName)) {
                return $storage->get($cacheFileName);
            }

            // Retrieve source
            $sourceBinary = null;
            if ($storage->exists($cleanPath)) {
                $sourceBinary = $storage->get($cleanPath);
            } elseif (file_exists(public_path($cleanPath))) {
                $sourceBinary = file_get_contents(public_path($cleanPath));
            } elseif (file_exists($cleanPath)) {
                $sourceBinary = file_get_contents($cleanPath);
            }

            if (! $sourceBinary) {
                throw new HttpException(404, 'Source image not found.');
            }

            // Process image with active driver
            $processed = $this->driver->process($sourceBinary, [
                'width' => $width,
                'height' => $height,
                'fit' => $fit,
                'format' => $format,
                'quality' => $quality,
            ]);

            // Secondary binary compression
            $optimized = $this->optimizer->optimize($processed, $format);

            // Persist to warm cache disk
            $storage->put($cacheFileName, $optimized);

            return $optimized;
        });

        return $this->buildResponse($binary, $format);
    }

    protected function buildResponse(string $binary, string $format): Response
    {
        $mime = $this->formatNegotiator->mimeTypeFor($format);
        $etag = '"' . md5($binary) . '"';

        return response($binary, 200, [
            'Content-Type' => $mime,
            'Content-Length' => strlen($binary),
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'ETag' => $etag,
            'Vary' => 'Accept',
        ]);
    }
}
