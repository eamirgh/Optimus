<?php

namespace Eamirgh\Optimus\Tests\Feature;

use Eamirgh\Optimus\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class ClearStaleCommandTest extends TestCase
{
    public function test_it_purges_stale_cache_files(): void
    {
        $cacheSubdir = config('optimus.cache_path', 'optimus');
        $storage = Storage::disk('public');

        $oldFile = $cacheSubdir . '/old_variant.webp';
        $freshFile = $cacheSubdir . '/fresh_variant.webp';

        $storage->put($oldFile, 'old-image-binary');
        $storage->put($freshFile, 'fresh-image-binary');

        // Touch old file to simulate age > 30 days
        $oldFilePath = $storage->path($oldFile);
        if (file_exists($oldFilePath)) {
            touch($oldFilePath, time() - (86400 * 35));
        }

        $this->artisan('optimus:clear-stale', ['--ttl' => 86400 * 30])
            ->assertSuccessful();

        $this->assertFalse($storage->exists($oldFile));
        $this->assertTrue($storage->exists($freshFile));
    }

    public function test_it_supports_dry_run_mode(): void
    {
        $cacheSubdir = config('optimus.cache_path', 'optimus');
        $storage = Storage::disk('public');

        $oldFile = $cacheSubdir . '/stale_check.webp';
        $storage->put($oldFile, 'test-binary');

        $oldFilePath = $storage->path($oldFile);
        if (file_exists($oldFilePath)) {
            touch($oldFilePath, time() - 100);
        }

        $this->artisan('optimus:clear-stale', ['--ttl' => 50, '--dry-run' => true])
            ->assertSuccessful();

        $this->assertTrue($storage->exists($oldFile));
    }
}
