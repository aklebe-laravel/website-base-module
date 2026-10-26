<?php

declare(strict_types=1);

namespace Modules\WebsiteBase\app\View\Components\Dropdown;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Dropdown extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $triggerClass = '',
        public bool $hover = false,
        public string $width = 'min-w-[14rem]',
        public string $placement = 'bottom-start' // Default placement: bottom-start, bottom-end, top-start, etc.
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Factory|Htmlable|Closure|string
    {
        return view('website-base::components.dropdown.dropdown', [
            'component' => $this,
        ]);
    }
}
