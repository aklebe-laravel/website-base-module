<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Livewire\Attributes\On;
use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;
use Modules\WebsiteBase\app\Http\Livewire\DataTable\BaseWebsiteBaseDataTable;

class MediaItemImport extends ModelBase
{
    use BaseWebsiteBaseDataTable;

    /**
     * Runs on every request, after the component is mounted or hydrated, but before any update methods are called
     *
     * @return void
     */
    protected function initBooted(): void
    {
        parent::initBooted();

        $this->addBaseWebsiteMessageBoxes();
    }

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
