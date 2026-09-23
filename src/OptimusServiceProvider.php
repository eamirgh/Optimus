<?php

namespace Eamirgh\Optimus;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Eamirgh\Optimus\Contracts\OptimizerInterface;
use Eamirgh\Optimus\Contracts\ImageDriverInterface;
use Eamirgh\Optimus\Security\SignatureGenerator;
use Eamirgh\Optimus\Security\DimensionValidator;
use Eamirgh\Optimus\Security\ConcurrencyLock;
use Eamirgh\Optimus\Negotiation\FormatNegotiator;
use Eamirgh\Optimus\Optimization\SecondaryOptimizer;
use Eamirgh\Optimus\Support\OptimusUrlGenerator;
use Eamirgh\Optimus\Http\Controllers\OptimusController;
use Eamirgh\Optimus\Commands\ClearStaleCacheCommand;
use Eamirgh\Optimus\View\Components\Image;
use Eamirgh\Optimus\View\Components\Link;
use Eamirgh\Optimus\View\Components\Script;
use Eamirgh\Optimus\View\Components\GoogleAnalytics;
use Eamirgh\Optimus\View\Directives\OptimusBladeDirectives;

class OptimusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/optimus.php', 'optimus');

        $this->app->singleton(SignatureGenerator::class, function ($app) {
            $key = $app['config']->get('optimus.key', $app['config']->get('app.key'));
            return new SignatureGenerator($key);
        });

        $this->app->singleton(DimensionValidator::class, function ($app) {
            $config = $app['config']->get('optimus');
            return new DimensionValidator(
                allowedDimensions: $config['allowed_dimensions'] ?? [],
                maxWidth: $config['max_width'] ?? 3840,
                maxHeight: $config['max_height'] ?? 2160
            );
        });

        $this->app->singleton(FormatNegotiator::class, function () {
            return new FormatNegotiator();
        });

        $this->app->singleton(ConcurrencyLock::class, function ($app) {
            return new ConcurrencyLock(
                $app['cache']->store(),
                (int) $app['config']->get('optimus.lock_timeout', 10)
            );
        });

        $this->app->singleton(OptimizerInterface::class, function ($app) {
            $cfg = $app['config']->get('optimus.optimizers', []);
            return new SecondaryOptimizer(
                enabled: $cfg['enabled'] ?? true,
                binaries: $cfg['binaries'] ?? []
            );
        });

        $this->app->singleton('optimus', function ($app) {
            return new OptimusManager($app);
        });

        $this->app->bind(ImageDriverInterface::class, function ($app) {
            return $app['optimus']->driver();
        });

        $this->app->singleton(OptimusUrlGenerator::class, function ($app) {
            return new OptimusUrlGenerator(
                $app->make(SignatureGenerator::class),
                $app['config']->get('optimus.cdn_url'),
                'optimus'
            );
        });

        $this->app->bind(OptimusController::class, function ($app) {
            return new OptimusController(
                $app->make(SignatureGenerator::class),
                $app->make(DimensionValidator::class),
                $app->make(FormatNegotiator::class),
                $app->make(ConcurrencyLock::class),
                $app->make(ImageDriverInterface::class),
                $app->make(OptimizerInterface::class)
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/optimus.php' => config_path('optimus.php'),
            ], 'optimus-config');

            $this->commands([
                ClearStaleCacheCommand::class,
            ]);
        }

        $this->registerRoutes();
        $this->registerBladeComponents();
    }

    protected function registerRoutes(): void
    {
        Route::get('optimus/{path}', OptimusController::class)
            ->where('path', '.*')
            ->name('optimus.serve');
    }

    protected function registerBladeComponents(): void
    {
        Blade::component('image', Image::class);
        Blade::component('link', Link::class);
        Blade::component('script', Script::class);
        Blade::component('ga', GoogleAnalytics::class);

        Blade::directive('optimusCssBg', function ($expression) {
            return "<?php echo \Eamirgh\Optimus\View\Directives\OptimusBladeDirectives::compileCssBg({$expression}); ?>";
        });
    }
}
