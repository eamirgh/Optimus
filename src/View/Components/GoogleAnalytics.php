<?php

namespace Eamirgh\Optimus\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class GoogleAnalytics extends Component
{
    public function __construct(
        public string $id
    ) {}

    public function render(): View|string
    {
        $id = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<script src="https://www.googletagmanager.com/gtag/js?id={$id}" defer></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$id}');
</script>
HTML;
    }
}
