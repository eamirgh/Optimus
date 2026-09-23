<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Eamirgh\Optimus\Drivers\CloudinaryDriver;
use Eamirgh\Optimus\Drivers\GdDriver;
use Eamirgh\Optimus\Drivers\ImgproxyDriver;
use Eamirgh\Optimus\Negotiation\FormatNegotiator;
use Eamirgh\Optimus\OptimusServiceProvider;
use Eamirgh\Optimus\Placeholders\BlurPlaceholder;
use Eamirgh\Optimus\Placeholders\BlurhashPlaceholder;
use Eamirgh\Optimus\Placeholders\SqipPlaceholder;
use Eamirgh\Optimus\Security\DimensionValidator;
use Eamirgh\Optimus\Security\SignatureGenerator;
use Eamirgh\Optimus\Support\OptimusUrlGenerator;
use Eamirgh\Optimus\View\Components\Image;
use Eamirgh\Optimus\View\Components\Link;
use Eamirgh\Optimus\View\Components\Script;
use Eamirgh\Optimus\View\Directives\OptimusBladeDirectives;
use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\Concerns\CreatesApplication;

class OptimusBenchmarkRunner
{
    use CreatesApplication;

    protected $app;

    public function __construct()
    {
        $this->app = $this->createApplication();
        $this->app->register(OptimusServiceProvider::class);
        $this->app['config']->set('optimus.key', 'benchmark-secret-key-32-chars-long!');
    }

    protected function getPackageProviders($app)
    {
        return [OptimusServiceProvider::class];
    }

    public function run(): void
    {
        echo "===============================================================\n";
        echo " Laravel Optimus Comprehensive Benchmark Suite\n";
        echo " PHP Version : " . PHP_VERSION . "\n";
        echo " Date        : " . date('Y-m-d H:i:s') . "\n";
        echo "===============================================================\n\n";

        $this->benchmarkSignatureGeneration(20000);
        $this->benchmarkDimensionValidation(50000);
        $this->benchmarkFormatNegotiation(50000);
        $this->benchmarkEdgeUrlGeneration(25000);
        $this->benchmarkPlaceholderSvgGeneration(10000);
        $this->benchmarkImageComponentRendering(5000);
        $this->benchmarkLinkAndScriptRendering(5000);
        $this->benchmarkCssBgDirective(10000);
        $this->benchmarkGdImageProcessing(50);

        echo "===============================================================\n";
        echo " All 9 Optimus benchmarks completed successfully.\n";
        echo "===============================================================\n";
    }

    // 1. Signature Generation & Timing Attack-Safe Validation
    protected function benchmarkSignatureGeneration(int $iterations): void
    {
        $generator = new SignatureGenerator('secret-benchmark-key-32-bytes!!');
        $params = ['w' => 1280, 'h' => 720, 'q' => 80, 'fm' => 'webp', 'fit' => 'cover'];

        $startTime = microtime(true);
        $startMem = memory_get_usage();

        for ($i = 0; $i < $iterations; $i++) {
            $sig = $generator->generate("products/banner_{$i}.jpg", $params);
            $generator->validate($sig, "products/banner_{$i}.jpg", $params);
        }

        $duration = microtime(true) - $startTime;
        $opsSec = round($iterations / $duration);
        $memDiff = (memory_get_usage() - $startMem) / 1024;

        printf("1. HMAC-SHA256 Signing & Validation\n");
        printf("   Iterations : %s\n", number_format($iterations));
        printf("   Throughput : %s ops/sec\n", number_format($opsSec));
        printf("   Duration   : %.4f sec\n", $duration);
        printf("   Memory     : +%.2f KB\n\n", $memDiff);
    }

    // 2. Dimension Whitelist Enforcement
    protected function benchmarkDimensionValidation(int $iterations): void
    {
        $allowed = config('optimus.allowed_dimensions');
        $validator = new DimensionValidator($allowed, 3840, 2160);

        $testCases = [
            [640, 480],
            [1280, 720],
            [1920, 1080],
            [320, 240],
            [999, 999], // invalid case
        ];
        $count = count($testCases);

        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $tc = $testCases[$i % $count];
            $validator->isValid($tc[0], $tc[1]);
        }

        $duration = microtime(true) - $startTime;
        $opsSec = round($iterations / $duration);

        printf("2. Dimension Whitelist Validation (DoS Guard)\n");
        printf("   Iterations : %s\n", number_format($iterations));
        printf("   Throughput : %s checks/sec\n", number_format($opsSec));
        printf("   Duration   : %.4f sec\n\n", $duration);
    }

    // 3. Format Negotiation (Accept Header Parsing)
    protected function benchmarkFormatNegotiation(int $iterations): void
    {
        $negotiator = new FormatNegotiator();
        $accept = 'text/html,application/xhtml+xml,image/avif,image/webp,image/apng,*/*;q=0.8';

        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $negotiator->negotiate($accept, null, 'jpeg', ['avif', 'webp', 'jpeg']);
        }

        $duration = microtime(true) - $startTime;
        $opsSec = round($iterations / $duration);

        printf("3. Format Negotiation (Accept Header -> AVIF/WebP)\n");
        printf("   Iterations : %s\n", number_format($iterations));
        printf("   Throughput : %s ops/sec\n", number_format($opsSec));
        printf("   Duration   : %.4f sec\n\n", $duration);
    }

    // 4. Edge URL Generation (Imgproxy + Cloudinary)
    protected function benchmarkEdgeUrlGeneration(int $iterations): void
    {
        $imgproxy = new ImgproxyDriver([
            'base_url' => 'https://img.cdn.com',
            'key' => bin2hex('secret-key-32-bytes-long-here!!'),
            'salt' => bin2hex('secret-salt-32-bytes-long-here!!'),
        ]);

        $cloudinary = new CloudinaryDriver([
            'cloud_name' => 'acme-prod',
        ]);

        $options = ['width' => 1280, 'height' => 720, 'fit' => 'cover', 'format' => 'webp', 'quality' => 80];

        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $imgproxy->generateUrl("https://storage.cdn.com/photos/item_{$i}.jpg", $options);
            $cloudinary->generateUrl("photos/item_{$i}.jpg", $options);
        }

        $duration = microtime(true) - $startTime;
        $opsSec = round(($iterations * 2) / $duration);

        printf("4. Edge Driver URL Generation (Imgproxy & Cloudinary)\n");
        printf("   Total URLs : %s\n", number_format($iterations * 2));
        printf("   Throughput : %s urls/sec\n", number_format($opsSec));
        printf("   Duration   : %.4f sec\n\n", $duration);
    }

    // 5. Placeholder SVG Generation
    protected function benchmarkPlaceholderSvgGeneration(int $iterations): void
    {
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            BlurPlaceholder::makeSvg(64, 48, '#3b82f6');
            SqipPlaceholder::make(64, 48);
            BlurhashPlaceholder::toDataUri('L6PZfSi_.AyE_3t7t7R**0o#DgR4', 32, 32);
        }

        $duration = microtime(true) - $startTime;
        $opsSec = round(($iterations * 3) / $duration);

        printf("5. Placeholder Generation (Blur, Sqip, Blurhash Data URIs)\n");
        printf("   Total SVGs : %s\n", number_format($iterations * 3));
        printf("   Throughput : %s placeholders/sec\n", number_format($opsSec));
        printf("   Duration   : %.4f sec\n\n", $duration);
    }

    // 6. Blade <x-image> Rendering
    protected function benchmarkImageComponentRendering(int $iterations): void
    {
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $component = new Image(
                src: "products/item_{$i}.jpg",
                alt: "Product {$i}",
                width: 640,
                height: 360,
                fit: 'cover',
                placeholder: 'blur'
            );
            $html = (string) $component->render();
        }

        $duration = microtime(true) - $startTime;
        $opsSec = round($iterations / $duration);

        printf("6. Blade <x-image> Component Render (<picture> + 1x/1.5x/2x srcset + blur)\n");
        printf("   Iterations : %s\n", number_format($iterations));
        printf("   Throughput : %s renders/sec\n", number_format($opsSec));
        printf("   Duration   : %.4f sec\n\n", $duration);
    }

    // 7. Blade <x-link> & <x-script> Rendering
    protected function benchmarkLinkAndScriptRendering(int $iterations): void
    {
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $link = new Link(
                href: "/products/item-{$i}",
                prefetch: true,
                preconnect: ['https://cdn.example.com']
            );
            $renderLink = $link->render();
            $linkHtml = is_callable($renderLink) ? $renderLink(['slot' => 'View']) : (string) $renderLink;

            $script = new Script(
                src: "https://cdn.example.com/bundle_{$i}.js",
                strategy: 'afterInteractive',
                id: "script-{$i}"
            );
            $renderScript = $script->render();
            $scriptHtml = is_callable($renderScript) ? $renderScript(['slot' => '']) : (string) $renderScript;
        }

        $duration = microtime(true) - $startTime;
        $opsSec = round(($iterations * 2) / $duration);

        printf("7. Blade <x-link> & <x-script> Component Rendering\n");
        printf("   Total Tags : %s\n", number_format($iterations * 2));
        printf("   Throughput : %s components/sec\n", number_format($opsSec));
        printf("   Duration   : %.4f sec\n\n", $duration);
    }

    // 8. Blade @optimusCssBg Compilation
    protected function benchmarkCssBgDirective(int $iterations): void
    {
        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            OptimusBladeDirectives::compileCssBg("backgrounds/hero_{$i}.png", [
                'width' => 1920,
                'height' => 1080,
            ]);
        }

        $duration = microtime(true) - $startTime;
        $opsSec = round($iterations / $duration);

        printf("8. @optimusCssBg Directive Compilation (cross-browser image-set)\n");
        printf("   Iterations : %s\n", number_format($iterations));
        printf("   Throughput : %s rules/sec\n", number_format($opsSec));
        printf("   Duration   : %.4f sec\n\n", $duration);
    }

    // 9. GD Driver Image Processing
    protected function benchmarkGdImageProcessing(int $iterations): void
    {
        $driver = new GdDriver();

        // Synthetic 640x480 source image
        $im = imagecreatetruecolor(640, 480);
        $color = imagecolorallocate($im, 40, 120, 220);
        imagefilledrectangle($im, 0, 0, 640, 480, $color);
        ob_start();
        imagepng($im);
        $sourceBinary = ob_get_clean();
        imagedestroy($im);

        $startTime = microtime(true);
        $startMem = memory_get_usage();

        $format = function_exists('imagewebp') ? 'webp' : 'jpeg';

        for ($i = 0; $i < $iterations; $i++) {
            $driver->process($sourceBinary, [
                'width' => 320,
                'height' => 240,
                'fit' => 'cover',
                'format' => $format,
                'quality' => 80,
            ]);
        }

        $duration = microtime(true) - $startTime;
        $opsSec = round($iterations / $duration, 2);
        $avgMs = ($duration / $iterations) * 1000;
        $peakMem = (memory_get_peak_usage() - $startMem) / 1024 / 1024;

        printf("9. GD Driver Resampling (640x480 -> 320x240 %s with alpha)\n", strtoupper($format));
        printf("   Iterations : %d\n", $iterations);
        printf("   Throughput : %.2f ops/sec (%.2f ms/op)\n", $opsSec, $avgMs);
        printf("   Duration   : %.4f sec\n", $duration);
        printf("   Peak Memory: %.2f MB\n\n", $peakMem);
    }
}

$runner = new OptimusBenchmarkRunner();
$runner->run();
