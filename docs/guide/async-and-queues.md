# Async Queue & Scaling

For high-scale applications, generating images synchronously on first page load introduces latency. Optimus supports background variant pregeneration.

## `HasOptimusImages` Trait

Attach the `HasOptimusImages` trait to any Eloquent model with image attributes:

```php
namespace App\Models;

use Eamirgh\Optimus\Concerns\HasOptimusImages;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasOptimusImages;

    // Attributes monitored for image uploads (default: image, avatar, cover, thumbnail)
    protected array $optimusImages = ['featured_image', 'gallery_image'];
}
```

Whenever a model is saved and an image attribute is created or changed, Optimus dispatches a `GenerateImageVariantsJob` to your Laravel queue workers.

## `GenerateImageVariantsJob`

The job loops through all configured breakpoint dimensions in `config/optimus.php`, processes the modern formats (AVIF and WebP), runs secondary compression binaries, and writes them to your storage disk or S3 bucket.

```php
use Eamirgh\Optimus\Jobs\GenerateImageVariantsJob;

GenerateImageVariantsJob::dispatch('products/hero.png');
```

## Cache Maintenance: `optimus:clear-stale`

Over time, deleted products or outdated assets leave orphaned files in your cache directory.

Run the Artisan command to prune files older than the configured TTL (default 30 days):

```bash
# Preview files eligible for deletion
php artisan optimus:clear-stale --dry-run

# Purge files older than 14 days
php artisan optimus:clear-stale --ttl=1209600
```

Add it to your application schedule in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('optimus:clear-stale')->weekly();
```
