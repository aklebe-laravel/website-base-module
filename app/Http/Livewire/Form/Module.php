<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Support\Arr;
use Modules\Form\app\Forms\Base\NativeObjectBase as NativeObjectBaseForm;
use Modules\Form\app\Http\Livewire\Form\Base\NativeObjectBase;
use Modules\SystemBase\app\Models\JsonViewResponse;
use Modules\WebsiteBase\app\Services\ConfigService;

class Module extends NativeObjectBase
{
    /**
     * Called by save() or other high level calls.
     *
     * @return JsonViewResponse
     */
    protected function saveFormData(): JsonViewResponse
    {
        // Take the form again to use their validator and update functionalities ...
        /** @var NativeObjectBaseForm $form */
        $form = $this->getFormInstance();

        $jsonResponse = new JsonViewResponse();
        if ($validatedData = $this->validateForm()) {

            /** @var ConfigService $configService */
            $configService = app('website_base_config');

            $configUpdateCount = 0;
            $allModulesConfigData = data_get($validatedData, 'config.module', []);
            foreach ($allModulesConfigData as $moduleSnakeName => $moduleConfigData) { // there should be only one
                $storeId = data_get($validatedData, 'config.store_id');

                $r = Arr::dot($moduleConfigData);
                foreach ($r as $path => $value) {

                    if ($configService->save($path, $value, $storeId, $moduleSnakeName)) {
                        $configUpdateCount++;
                    }

                }
            }

            $this->addSuccessMessage('Saved '.$configUpdateCount.' core settings.');

            return $jsonResponse;

        } else {
            $jsonResponse->setErrorMessage('Unable to load data or validation error.');

            return $jsonResponse;
        }

    }


}
