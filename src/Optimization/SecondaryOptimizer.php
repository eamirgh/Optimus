<?php

namespace Eamirgh\Optimus\Optimization;

use Eamirgh\Optimus\Contracts\OptimizerInterface;

class SecondaryOptimizer implements OptimizerInterface
{
    /**
     * @param array<string, string> $binaries
     */
    public function __construct(
        protected bool $enabled = true,
        protected array $binaries = [
            'jpegoptim' => 'jpegoptim',
            'pngquant' => 'pngquant',
            'cwebp' => 'cwebp',
            'gifsicle' => 'gifsicle',
        ]
    ) {}

    public function isAvailable(): bool
    {
        return $this->enabled;
    }

    public function optimize(string $binary, string $format): string
    {
        if (! $this->enabled || empty($binary)) {
            return $binary;
        }

        $format = strtolower($format);

        return match ($format) {
            'jpg', 'jpeg' => $this->optimizeJpeg($binary),
            'png'         => $this->optimizePng($binary),
            'gif'         => $this->optimizeGif($binary),
            'webp'        => $this->optimizeWebp($binary),
            default       => $binary,
        };
    }

    protected function optimizeJpeg(string $binary): string
    {
        $bin = $this->binaries['jpegoptim'] ?? 'jpegoptim';
        if (! $this->binaryExists($bin)) {
            return $binary;
        }

        return $this->runCommandOnFile($binary, "{$bin} --strip-all --all-progressive -q");
    }

    protected function optimizePng(string $binary): string
    {
        $bin = $this->binaries['pngquant'] ?? 'pngquant';
        if (! $this->binaryExists($bin)) {
            return $binary;
        }

        return $this->runCommandOnFile($binary, "{$bin} --force --strip --skip-if-larger -");
    }

    protected function optimizeGif(string $binary): string
    {
        $bin = $this->binaries['gifsicle'] ?? 'gifsicle';
        if (! $this->binaryExists($bin)) {
            return $binary;
        }

        return $this->runCommandOnFile($binary, "{$bin} -O3");
    }

    protected function optimizeWebp(string $binary): string
    {
        $bin = $this->binaries['cwebp'] ?? 'cwebp';
        if (! $this->binaryExists($bin)) {
            return $binary;
        }

        return $this->runCommandOnFile($binary, "{$bin} -q 80 -m 6");
    }

    protected function runCommandOnFile(string $binary, string $command): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'opt_');
        if ($tempFile === false) {
            return $binary;
        }

        file_put_contents($tempFile, $binary);

        $cmd = str_contains($command, '-')
            ? "{$command} < " . escapeshellarg($tempFile)
            : "{$command} " . escapeshellarg($tempFile);

        $output = @shell_exec($cmd . ' 2>/dev/null');

        if (file_exists($tempFile)) {
            $result = file_get_contents($tempFile);
            @unlink($tempFile);
            return ($result !== false && strlen($result) > 0 && strlen($result) <= strlen($binary)) ? $result : $binary;
        }

        return $binary;
    }

    protected function binaryExists(string $binary): bool
    {
        if (empty($binary)) {
            return false;
        }

        $cmd = (PHP_OS_FAMILY === 'Windows') ? "where {$binary}" : "command -v {$binary}";
        $result = @shell_exec($cmd . ' 2>/dev/null');

        return ! empty($result);
    }
}
