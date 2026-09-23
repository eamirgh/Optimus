<?php

namespace Eamirgh\Optimus\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Eamirgh\Optimus\OptimusServiceProvider;
use Illuminate\Support\Facades\File;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            OptimusServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('optimus.key', 'test-secret-key-32-chars-long!!');
        config()->set('optimus.disk', 'public');
        config()->set('optimus.cache_path', 'optimus');
        config()->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => sys_get_temp_dir() . '/optimus_tests_storage',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        File::ensureDirectoryExists(sys_get_temp_dir() . '/optimus_tests_storage');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(sys_get_temp_dir() . '/optimus_tests_storage');
        parent::tearDown();
    }
}
