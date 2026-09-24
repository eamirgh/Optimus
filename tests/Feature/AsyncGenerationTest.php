<?php

namespace Eamirgh\Optimus\Tests\Feature;

use Eamirgh\Optimus\Jobs\GenerateImageVariantsJob;
use Eamirgh\Optimus\Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Eamirgh\Optimus\Concerns\HasOptimusImages;

class TestProductModel extends Model
{
    use HasOptimusImages;

    protected $guarded = [];

    public function triggerSaved(): void
    {
        $this->syncChanges();
        $this->fireModelEvent('saved', false);
    }
}

class AsyncGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create synthetic image on public disk
        $im = imagecreatetruecolor(400, 300);
        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);

        Storage::disk('public')->put('products/sample.png', $png);
    }

    public function test_it_pregenerates_image_variants_synchronously_in_job(): void
    {
        $job = new GenerateImageVariantsJob(
            sourcePath: 'products/sample.png',
            dimensions: [[200, 150]],
            formats: ['webp', 'png']
        );

        $job->handle(app('optimus')->driver('gd'), app(\Eamirgh\Optimus\Contracts\OptimizerInterface::class));

        $cacheSubdir = config('optimus.cache_path', 'optimus');
        $files = Storage::disk('public')->files($cacheSubdir);

        $this->assertNotEmpty($files);
        $this->assertCount(2, $files); // webp and png
    }

    public function test_has_optimus_images_trait_dispatches_job_on_save(): void
    {
        Queue::fake();

        $product = new TestProductModel();
        $product->image = 'products/sample.png';
        $product->syncOriginal(); // Simulate pristine state

        // Change the image attribute
        $product->image = 'products/updated.png';
        $product->triggerSaved();

        Queue::assertPushed(GenerateImageVariantsJob::class, function ($job) {
            return $job->sourcePath === 'products/updated.png';
        });
    }
}
