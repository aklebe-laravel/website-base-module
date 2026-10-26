<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;

class MediaItemImage extends MediaItem
{
    /**
     * The base builder before all filter manipulations.
     * Usually used for all collections (default, selected, unselected), but can be overwritten.
     *
     * @param  string|null  $collectionName
     *
     * @return Builder|null
     * @throws \Exception
     */
    public function getBaseBuilder(?string $collectionName = null): ?Builder
    {
        $builder = parent::getBaseBuilder($collectionName);

        return $builder->images();
    }
}
