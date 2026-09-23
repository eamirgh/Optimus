<?php

namespace Eamirgh\Optimus\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearStaleCacheCommand extends Command
{
    protected $signature = 'optimus:clear-stale
                            {--ttl= : Max age in seconds before cache files are purged}
                            {--dry-run : List stale files without deleting them}';

    protected $description = 'Scan storage directories and purge orphaned, outdated, or expired image cache files';

    public function handle(): int
    {
        $diskName = config('optimus.disk', 'public');
        $cacheSubdir = config('optimus.cache_path', 'optimus');
        $storage = Storage::disk($diskName);

        $ttl = $this->option('ttl') !== null
            ? (int) $this->option('ttl')
            : (int) config('optimus.stale_ttl', 86400 * 30);

        $isDryRun = (bool) $this->option('dry-run');

        if (! $storage->exists($cacheSubdir)) {
            $this->info("Cache directory [{$cacheSubdir}] does not exist on disk [{$diskName}]. Nothing to purge.");
            return self::SUCCESS;
        }

        $allFiles = $storage->allFiles($cacheSubdir);
        $thresholdTime = time() - $ttl;

        $purgedCount = 0;
        $purgedBytes = 0;

        foreach ($allFiles as $file) {
            $lastModified = $storage->lastModified($file);

            if ($lastModified < $thresholdTime) {
                $size = $storage->size($file);
                $purgedCount++;
                $purgedBytes += $size;

                if ($isDryRun) {
                    $this->line("Would delete: {$file} (" . round($size / 1024, 2) . ' KB)');
                } else {
                    $storage->delete($file);
                }
            }
        }

        $formattedSize = round($purgedBytes / 1024 / 1024, 2);

        if ($isDryRun) {
            $this->info("Dry run complete: {$purgedCount} files ({$formattedSize} MB) eligible for purging.");
        } else {
            $this->info("Successfully purged {$purgedCount} stale cache files ({$formattedSize} MB freed).");
        }

        return self::SUCCESS;
    }
}
