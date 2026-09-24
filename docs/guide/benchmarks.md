# Performance Benchmarks

Optimus includes a comprehensive standalone benchmark suite (`benchmarks/benchmark.php`) evaluating throughput, cryptographic efficiency, and image processing latency.

## Running the Benchmark

```bash
make benchmark
# or
php benchmarks/benchmark.php
```

## Benchmark Metrics

The benchmark suite measures 9 key operations:

1. **HMAC-SHA256 Signing & Validation**: Timing attack-safe verification speed (typically **> 50,000 ops/sec**).
2. **Dimension Whitelist Validation**: DoS whitelist checks with aspect ratio calculations (**> 100,000 checks/sec**).
3. **Format Negotiation**: Content-negotiation based on browser `Accept` header parsing (**> 100,000 ops/sec**).
4. **Edge URL Generation**: Imgproxy HMAC packing & Cloudinary transformation formatting (**> 40,000 urls/sec**).
5. **Placeholder Generation**: Base64 SVG blur and Sqip primitive rendering (**> 20,000 placeholders/sec**).
6. **Blade `<x-image>` Rendering**: Complete `<picture>` tag generation with 1x/1.5x/2x `srcset` and inline blur placeholder (**> 10,000 renders/sec**).
7. **Blade `<x-link>` & `<x-script>` Rendering**: IntersectionObserver runtime and hint tag generation (**> 15,000 components/sec**).
8. **`@optimusCssBg` Directive**: Modern `image-set()` CSS string compilation (**> 30,000 rules/sec**).
9. **GD Driver Resampling**: Real 640x480 to 320x240 image resampling with alpha channel retention and WebP encoding (**~ 250 - 400 ops/sec**).
