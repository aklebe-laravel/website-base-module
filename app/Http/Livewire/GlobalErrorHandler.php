<?php
declare(strict_types=1);

namespace Modules\WebsiteBase\app\Http\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class GlobalErrorHandler extends Component
{
    /**
     * Handle the browser error event globally.
     */
    #[On('browser-error-detected')]
    public function handleBrowserError(array $error): void
    {
        // Log to Laravel Log-Stream (Docker Symlink)
        Log::error('Client-side error detected', [
            'message' => $error['message'],
            'file' => $error['source'],
            'line' => $error['lineno'],
            //'stack' => $error['stack'] ?? 'n/a',
            'user_id' => auth()->id() ?? 'guest',
        ]);

        // Optional: Notify Admin via TallstackUI
        // $this->toast()->error('Browser Error logged');
    }

    public function render(): string
    {
        return '<div></div>'; // Invisible root element
    }
}