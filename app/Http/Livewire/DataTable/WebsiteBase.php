<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
use Modules\Form\app\Services\FormService;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;
use Modules\WebsiteBase\app\Services\WebsiteBaseFormService;

class WebsiteBase extends BaseDataTable
{
    /**
     * @return void
     */
    protected function addStoreFilter(): void
    {
        /** @var FormService $formService */
        $formService = app(FormService::class);
        /** @var WebsiteBaseFormService $websiteBaseFormService */
        $websiteBaseFormService = app(WebsiteBaseFormService::class);
        $formService->registerFormElement(ExtraAttributeModel::ATTR_STORE, fn($x) => $websiteBaseFormService::getFormElementStore($x));

        /** @var SystemService $systemService */
        $systemService = app('system_base');

        $this->addFilterElement('select-store', [
            'label'      => 'Module',
            'default'    => app('website_base_settings')->getStoreId(),
            'position'   => 1700, // between elements rows and search
            'soft_reset' => true,
            'css_group'  => 'col-12 col-md-3 text-start',
            'css_item'   => '',
            'options'    => $websiteBaseFormService::getFormElementStoreOptions(),
            'builder'    => function (Builder $builder, string $filterElementKey, string|int $filterValue) use ($systemService) {

                if ($filterValue != $systemService::selectValueAll) {
                    $builder->where('store_id', ($filterValue == $systemService::selectValueNoChoice) ? null : $filterValue);
                }
            },
            'view'       => 'website-base::livewire.js-dt.filters.default-elements.select-store',
        ]);

    }
}
