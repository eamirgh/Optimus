---
layout: home

hero:
  name: "Laravel Optimus"
  text: "High-Performance Image & Asset Engine"
  tagline: "Content-negotiated AVIF/WebP, responsive Blade components, HMAC security, edge CDN drivers, and sub-1KB link prefetching."
  actions:
    - theme: brand
      text: Get Started
      link: /guide/getting-started
    - theme: alt
      text: View on GitHub
      link: https://github.com/eamirgh/optimus

features:
  - icon: 🖼️
    title: Smart `<x-image>` Component
    details: Automatic <picture> markup, modern format negotiation (AVIF & WebP), 1x/1.5x/2x dynamic quality srcset, CLS prevention, and blur/sqip placeholders.
  - icon: ⚡
    title: Instant Link Prefetching
    details: The `<x-link>` component injects a tiny IntersectionObserver runtime for viewport HTML prefetching and automatically injects preconnect/dns-prefetch hints.
  - icon: 📜
    title: Flexible Script Strategies
    details: Control loading order with `<x-script>` via beforeInteractive, afterInteractive, and lazyOnload strategies, plus built-in Google Analytics (<x-ga>).
  - icon: 🔌
    title: Strategy Drivers (Local & Edge)
    details: Switch seamlessly between local engines (GD, Imagick) and high-scale edge transformation services (Imgproxy, Cloudinary).
  - icon: 🛡️
    title: HMAC Security & DoS Protection
    details: Cryptographic URL signing, strict dimension whitelisting, and atomic cache locks preventing thundering herd cache stampedes.
  - icon: 🚀
    title: Monolith & Distributed Scaling
    details: Low-scale on-the-fly generation with Nginx try_files warm cache bypass, or asynchronous background queue pregeneration with S3/CDN delivery.
---
