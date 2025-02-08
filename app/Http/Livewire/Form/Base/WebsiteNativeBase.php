<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form\Base;

use Modules\Form\app\Http\Livewire\Form\Base\ModelBase;

class WebsiteNativeBase extends ModelBase
{
    /**
     * @return void
     */
    protected function addStoreFilter(): void
    {
        $key = 'core_config.store_id';
        // Use session if exists. Otherwise, use a default.
        $v = (int) $this->getLiveFiltersSession($key, 0);
        $this->setLiveFilter($key, $v, true);
    }

}
