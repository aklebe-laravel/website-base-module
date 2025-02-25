<?php

namespace Modules\WebsiteBase\app\Models\Base;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCmsBase
 */
class CmsBase extends Model
{
    use TraitBaseModel;

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeCurrentStoreItems(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('store_id', app('website_base_settings')->getStoreId());
            $q->orWhereNull('store_id');
            $q->orderBy('store_id', 'desc');
        });
    }

    /**
     * same like currentStoreItems() but also must be enabled
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeFrontendItems(Builder $query): Builder
    {
        return $query->currentStoreItems()->where(function ($q) {
            $q->where('is_enabled', true);
        });
    }
}
