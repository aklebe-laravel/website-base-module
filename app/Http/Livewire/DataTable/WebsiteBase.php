<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
use Modules\SystemBase\app\Services\CacheService;
use Modules\WebsiteBase\app\Models\Store;

class WebsiteBase extends BaseDataTable
{
    /**
     * @return void
     */
    protected function addStoreFilter(): void
    {
        $systemService = app('system_base');
        $options = app(CacheService::class)->remember('dt_filter_element.select_store.options', 2, function () use ($systemService) {
            $o = $systemService->toHtmlSelectOptions(
                Store::orderBy('code', 'ASC')->get(),
                ['code', 'id'],
                'id',
            );

            $o = Arr::prepend($o, $systemService->allSelectOptionsRaw[$systemService::selectValueAll], $systemService::selectValueAll);
            $o = Arr::prepend($o, $systemService->allSelectOptionsRaw[$systemService::selectValueNoChoice], $systemService::selectValueNoChoice);

            return $o;
        });

        $this->addFilterElement('select-store', [
            'label'      => 'Module',
            'default'    => app('website_base_settings')->getStoreId(),
            'position'   => 1700, // between elements rows and search
            'soft_reset' => true,
            'css_group'  => 'col-12 col-md-3 text-start',
            'css_item'   => '',
            'options'    => $options,
            'builder'    => function (Builder $builder, string $filterElementKey, string|int $filterValue) use ($systemService) {

                if ($filterValue != $systemService::selectValueAll) {
                    $builder->where('store_id', ($filterValue == $systemService::selectValueNoChoice) ? null : $filterValue);
                }
            },
            'view'       => 'website-base::livewire.js-dt.filters.default-elements.select-store',
        ]);

    }
}
