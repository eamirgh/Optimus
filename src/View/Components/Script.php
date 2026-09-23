<?php

namespace Eamirgh\Optimus\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Script extends Component
{
    public function __construct(
        public ?string $src = null,
        public string $strategy = 'afterInteractive',
        public ?string $id = null
    ) {}

    public function render(): View|string
    {
        return function (array $data) {
            $slot = trim((string) ($data['slot'] ?? ''));
            $idAttr = $this->id ? ' id="' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8') . '"' : '';

            if ($this->strategy === 'beforeInteractive') {
                if ($this->src) {
                    return sprintf('<script src="%s"%s></script>', htmlspecialchars($this->src, ENT_QUOTES, 'UTF-8'), $idAttr);
                }
                return sprintf('<script%s>%s</script>', $idAttr, $slot);
            }

            if ($this->strategy === 'lazyOnload') {
                if ($this->src) {
                    $js = sprintf(
                        "window.addEventListener('load',function(){var s=document.createElement('script');s.src='%s';%sdocument.body.appendChild(s);});",
                        addslashes($this->src),
                        $this->id ? "s.id='" . addslashes($this->id) . "';" : ''
                    );
                    return sprintf('<script%s>%s</script>', $idAttr, $js);
                }

                $js = sprintf("window.addEventListener('load',function(){%s});", $slot);
                return sprintf('<script%s>%s</script>', $idAttr, $js);
            }

            // Default: 'afterInteractive'
            if ($this->src) {
                return sprintf('<script src="%s" defer%s></script>', htmlspecialchars($this->src, ENT_QUOTES, 'UTF-8'), $idAttr);
            }

            return sprintf('<script%s>document.addEventListener("DOMContentLoaded",function(){%s});</script>', $idAttr, $slot);
        };
    }
}
