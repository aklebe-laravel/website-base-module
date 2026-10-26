<?php
declare(strict_types=1);

namespace Modules\WebsiteBase\app\View\Components\Popup;

use Illuminate\View\Component;

class Popup extends Component
{
    public function __construct(
        public string $title,
        public string $stableId = 'popup-default-1',
        public ?string $content = null,
        public ?string $componentView = null,
        public string $position = 'top-center',
        public array $customData = [],
    ) {
        //Log::debug("Data:", $data);
    }

    public function render(): \Illuminate\View\View
    {
        return view('components.popup.popup');
    }
}
