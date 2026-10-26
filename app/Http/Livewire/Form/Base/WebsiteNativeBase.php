<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form\Base;

use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
use Modules\WebsiteBase\app\Services\WebsiteBaseFormService;

class WebsiteNativeBase extends NativeObjectBase
{
    /**
     * @return void
     */
    protected function addStoreCommand(): void
    {
        /** @var WebsiteBaseFormService $formService */
        $formService = app(WebsiteBaseFormService::class);

        $key = 'controls_store_id';
        $config = [
            'reload'      => true,
            'default'     => null,
            'view'        => 'form::components.form.select',
            'view_params' => [
                'name'              => 'controls_store_id',
                'options'           => $formService::getFormElementStoreOptions(),
                'livewire'          => 'liveCommands',
                'livewire_live'     => true,
                'livewire_debounce' => 300,
                'css_classes'       => 'form-select',
            ],
        ];
        $this->initLiveCommand($key, $config);
    }

}
