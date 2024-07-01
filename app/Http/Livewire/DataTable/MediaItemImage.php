<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;

class MediaItemImage extends MediaItem
{
    public string $modelName = 'MediaItem';

    /**
     * The base builder before all filter manipulations.
     * Usually used for all collections (default, selected, unselected), but can overwritten.
     *
     * @param  string  $collectionName
     *
     * @return Builder|null
     * @throws \Exception
     */
    public function getBaseBuilder(string $collectionName): ?Builder
    {
        $builder = parent::getBaseBuilder($collectionName);
        $builder = $builder->where('media_type', \Modules\WebsiteBase\app\Models\MediaItem::MEDIA_TYPE_IMAGE);
        return $builder;
    }


}
