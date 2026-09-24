<?php

namespace Eamirgh\Optimus\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Link extends Component
{
    protected static bool $observerInjected = false;
    public bool $prefetch;
    public bool $preload;

    public function __construct(
        public string $href,
        bool|string $prefetch = true,
        bool|string $preload = false,
        public ?string $as = null,
        public ?string $rel = null,
        public ?string $class = null,
        public array|string|null $preconnect = null
    ) {
        $this->prefetch = is_bool($prefetch) ? $prefetch : filter_var($prefetch, FILTER_VALIDATE_BOOLEAN);
        $this->preload = is_bool($preload) ? $preload : filter_var($preload, FILTER_VALIDATE_BOOLEAN);
    }

    public function render(): View|string
    {
        $classAttr = $this->class ? ' class="' . htmlspecialchars($this->class, ENT_QUOTES, 'UTF-8') . '"' : '';
        $relParts = array_filter(explode(' ', (string) $this->rel));

        $dataPrefetch = $this->prefetch ? ' data-optimus-prefetch' : '';

        $hintsHtml = '';

        // If resource hints specified
        if ($this->preconnect) {
            $domains = is_array($this->preconnect) ? $this->preconnect : [$this->preconnect];
            foreach ($domains as $domain) {
                $hintsHtml .= sprintf("<link rel=\"preconnect\" href=\"%s\">\n", htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'));
                $hintsHtml .= sprintf("<link rel=\"dns-prefetch\" href=\"%s\">\n", htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'));
            }
        }

        // If preload requested
        if ($this->preload) {
            $asAttr = $this->as ? ' as="' . htmlspecialchars($this->as, ENT_QUOTES, 'UTF-8') . '"' : '';
            $hintsHtml .= sprintf("<link rel=\"preload\" href=\"%s\"%s>\n", htmlspecialchars($this->href, ENT_QUOTES, 'UTF-8'), $asAttr);
        }

        // Sub-1KB IntersectionObserver prefetching runtime
        $script = '';
        if ($this->prefetch && ! static::$observerInjected) {
            static::$observerInjected = true;
            $script = <<<'HTML'
<script>(function(){if(!('IntersectionObserver' in window))return;var o=new IntersectionObserver(function(e){e.forEach(function(t){if(t.isIntersecting){var u=t.target.getAttribute('href');if(u){var l=document.createElement('link');l.rel='prefetch';l.href=u;document.head.appendChild(l);o.unobserve(t.target)}}})},{rootMargin:'200px'});document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('a[data-optimus-prefetch]').forEach(function(a){o.observe(a)})})})();</script>
HTML;
        }

        $relAttr = ! empty($relParts) ? ' rel="' . implode(' ', $relParts) . '"' : '';

        return sprintf(
            '%s<a href="%s"%s%s%s>{{ $slot }}</a>%s',
            $hintsHtml,
            htmlspecialchars($this->href, ENT_QUOTES, 'UTF-8'),
            $classAttr,
            $relAttr,
            $dataPrefetch,
            $script
        );
    }

    public static function resetObserverState(): void
    {
        static::$observerInjected = false;
    }
}
