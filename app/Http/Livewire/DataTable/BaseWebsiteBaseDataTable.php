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
            'builder'    => function (Builder $builder, string $filterElementKey, string $filterValue) {
                if (!$filterValue || $filterValue === self::FILTER_NOTIFICATION_CHANNEL_ALL) {
                    return;
                }
                $builder->whereHas('user_id', '>', 0);
            },
        ];
    }


}
