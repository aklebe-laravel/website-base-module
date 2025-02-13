<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;

trait BaseWebsiteBaseDataTable
{
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
                $builder->whereHas('user_id', '>', 0);
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
