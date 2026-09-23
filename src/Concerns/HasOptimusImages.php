<?php

namespace Eamirgh\Optimus\Concerns;

use Eamirgh\Optimus\Jobs\GenerateImageVariantsJob;
use Eamirgh\Optimus\Support\OptimusUrlGenerator;

trait HasOptimusImages
{
    public static function bootHasOptimusImages(): void
    {
        static::saved(function ($model) {
            $imageAttributes = $model->optimusImages();

            foreach ($imageAttributes as $attr) {
                if ($model->wasChanged($attr) && ! empty($model->{$attr})) {
                    GenerateImageVariantsJob::dispatch($model->{$attr});
                }
            }
        });
    }

    /**
     * Define which attributes represent optimus images.
     *
     * @return array<string>
     */
    public function optimusImages(): array
    {
        return property_exists($this, 'optimusImages')
            ? $this->optimusImages
            : ['image', 'avatar', 'cover', 'thumbnail'];
    }

    /**
     * Generate an Optimus transformed URL for a model image attribute.
     */
    public function optimusUrl(string $attribute, array $options = []): string
    {
        $path = $this->{$attribute} ?? null;
        if (! $path) {
            return '';
        }

        return app(OptimusUrlGenerator::class)->url($path, $options);
    }
}
