<?php

namespace Eamirgh\Optimus\Jobs;

use Eamirgh\Optimus\Contracts\ImageDriverInterface;
use Eamirgh\Optimus\Contracts\OptimizerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateImageVariantsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param string $sourcePath Path to original image on disk
     * @param array<int, array{0: int|null, 1: int|null}> $dimensions
     * @param array<string> $formats
     * @param string|null $disk
     */
    public function __construct(
        public string $sourcePath,
        public array $dimensions = [],
        public array $formats = ['webp', 'avif'],
        public ?string $disk = null
    ) {}

    public function handle(ImageDriverInterface $driver, OptimizerInterface $optimizer): void
    {
        $diskName = $this->disk ?? config('optimus.disk', 'public');
        $storage = Storage::disk($diskName);

        if (! $storage->exists($this->sourcePath)) {
            return;
        }

        $sourceBinary = $storage->get($this->sourcePath);
        $cacheSubdir = config('optimus.cache_path', 'optimus');

        $dimensions = ! empty($this->dimensions)
            ? $this->dimensions
            : config('optimus.allowed_dimensions', [[320, 240], [640, 480], [1280, 720]]);

        $supportedFormats = array_intersect($this->formats, $driver->supportedFormats());

        foreach ($dimensions as $dim) {
            $w = $dim[0] ?? null;
            $h = $dim[1] ?? null;

            foreach ($supportedFormats as $format) {
                $cacheFileName = sprintf(
                    '%s/%s_%dx%d_q%d_%s.%s',
                    $cacheSubdir,
                    md5(ltrim($this->sourcePath, '/')),
                    $w ?? 0,
                    $h ?? 0,
                    config('optimus.quality.default', 80),
                    'cover',
                    $format
                );

                if ($storage->exists($cacheFileName)) {
                    continue;
                }

                $processed = $driver->process($sourceBinary, [
                    'width' => $w,
                    'height' => $h,
                    'fit' => 'cover',
                    'format' => $format,
                    'quality' => config('optimus.quality.default', 80),
                ]);

                $optimized = $optimizer->optimize($processed, $format);

                $storage->put($cacheFileName, $optimized);
            }
        }
    }
}
