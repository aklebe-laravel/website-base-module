<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Modules\WebsiteBase\app\Services\WebsiteService;

trait BaseWebsiteBaseDataTable
{
    /**
     * Add messagebox buttons and call it in initBooted()
     *
     * @return void
     */
    protected function addBaseWebsiteMessageBoxes(): void
    {
        // @todo: 'data-table' is messed ... more performant is to let similar dts decide
        app(WebsiteService::class)->provideMessageBoxButtons(category: 'data-table');
    }

    /**
     * @return array[]
     */
    protected function getFilterDefaultBuilderForValidUserId(): array
    {
        return [
            'builder' => function (Builder $builder, string $filterElementKey, string $filterValue) {
                if (!$filterValue || $filterValue === self::FILTER_NOTIFICATION_CHANNEL_ALL) {
                    return;
                }
                $builder->where('user_id', '>', 0);
            },
        ];
    }

    /**
     * @return array[]
     */
    protected function getFilterOptionsForImages(): array
    {
        return [
            ... app('system_base')->selectOptionsCompact[app('system_base')::selectValueNoChoice],
            'images'    => [
                'label'   => __('With Images'),
                'builder' => function (Builder $builder, string $filterElementKey, string $filterValue) {
                    $builder->whereHas('images');
                    //Log::debug("Builder added to product filter '$filterElementKey' to '$filterValue'");
                },
            ],
            'no_images' => [
                'label'   => __('Without Images'),
                'builder' => function (Builder $builder, string $filterElementKey, string $filterValue) {
                    $builder->whereDoesntHave('images');
                    //Log::debug("Builder added to product filter '$filterElementKey' to '$filterValue'");
                },
            ],
        ];
    }

}
