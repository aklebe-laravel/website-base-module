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
        $config = [
            'reload'  => false,
            'default' => 0,
        ];
        $this->initLiveCommand($key, $config);
    }

}
