# Blade Components

Laravel Optimus provides zero-overhead, performance-engineered Blade components.

## `<x-image>`

Optimizes image loading, format negotiation, and CLS (Cumulative Layout Shift) prevention.

```html
<x-image
    src="products/sneakers.jpg"
    alt="Sneakers"
    width="640"
    height="480"
    fit="cover"
    placeholder="blur"
    placeholder-color="#f1f5f9"
/>
```

### Attributes

| Attribute | Type | Default | Description |
|---|---|---|---|
| `src` | `string` | **required** | Relative path to image or asset URL |
| `alt` | `string` | `""` | Alt text for accessibility |
| `width` | `int` | `null` | Target width in pixels (enforced to prevent CLS) |
| `height` | `int` | `null` | Target height in pixels |
| `fit` | `string` | `"cover"` | Resize mode: `cover`, `contain`, `fill` |
| `quality` | `int` | `config` | Explicit compression quality (overrides DPR rules) |
| `fill` | `bool` | `false` | Sets absolute positioning with `object-fit: cover` to fill parent container |
| `placeholder`| `string` | `null` | Placeholder type: `blur`, `blurhash`, or `sqip` |
| `blurhash` | `string` | `null` | Blurhash string if `placeholder="blurhash"` |
| `loading` | `string` | `"lazy"` | Native browser loading behavior (`lazy` or `eager`) |
| `decoding` | `string` | `"async"` | Native image decoding mode |
| `picture` | `bool` | `true` | If false, renders only single `<img>` tag with `srcset` |

---

## `<x-link>`

Monitors links entering the browser viewport and performs low-priority HTML and asset prefetching.

```html
<x-link
    href="/dashboard"
    prefetch="true"
    :preconnect="['https://api.stripe.com']"
>
    Open Dashboard
</x-link>
```

- Injects a sub-1KB `IntersectionObserver` runtime that watches for links entering the viewport (with 200px threshold margin) and triggers `<link rel="prefetch">`.
- Emits `<link rel="preconnect">` and `<link rel="dns-prefetch">` in the document head when specified.

---

## `<x-script>`

Allows declarative script loading strategies similar to Next.js.

```html
<!-- Injected directly into head without deferring -->
<x-script src="/cookie-consent.js" strategy="beforeInteractive" />

<!-- Deferred execution before </body> -->
<x-script src="/analytics.js" strategy="afterInteractive" />

<!-- Injected after window 'load' event -->
<x-script src="/chat-widget.js" strategy="lazyOnload" />
```

---

## `<x-ga>`

Pre-configured Google Analytics wrapper forcing best-practice `afterInteractive` execution.

```html
<x-ga id="G-ABC12345" />
```
