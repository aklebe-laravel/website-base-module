<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Livewire\Attributes\On;
use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

class MediaItem extends ModelBase
{
    /**
     * Its overwritten
     *
     * @param  mixed  $mediaItemId
     *
     * @return void
     */
    #[On('upload-process-finished')]
    public function uploadProcessFinished(mixed $mediaItemId): void
    {
        // do not fill relationUpdates here ...

        //
        $this->reopenFormIfNeeded();
    }
}
