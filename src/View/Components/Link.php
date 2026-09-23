<?php

namespace Eamirgh\Optimus\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Link extends Component
{
    protected static bool $observerInjected = false;

    public function __construct(
        public string $href,
        public bool $prefetch = true,
        public bool $preload = false,
        public ?string $as = null,
        public ?string $rel = null,
        public ?string $class = null,
        public array|string|null $preconnect = null
    ) {}

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

        return function (array $data) use ($classAttr, $dataPrefetch, $relAttr, $hintsHtml, $script) {
            $slot = $data['slot'] ?? '';
            $anchor = sprintf(
                '<a href="%s"%s%s%s>%s</a>',
                htmlspecialchars($this->href, ENT_QUOTES, 'UTF-8'),
                $classAttr,
                $relAttr,
                $dataPrefetch,
                $slot
            );

            return $hintsHtml . $anchor . $script;
        };
    }

    public static function resetObserverState(): void
    {
        static::$observerInjected = false;
    }
}
