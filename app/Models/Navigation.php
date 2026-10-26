<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;

/**
 * @mixin IdeHelperNavigation
 */
class Navigation extends Model
{
    use TraitBaseModel;

    //use HasFactory;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * properties should always cast from JSON to an array and via versa
     *
     * @var string[]
     */
    protected $casts = [
        'acl_resources' => 'array',
        'groups'        => 'array',
        'route_params'  => 'array',
        'tags'          => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class);
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id', 'id')->orderBy('position', 'asc')
            // nested children ...
            ->with('children');
    }

    /**
     * @return Builder
     */
    public static function getTree(): Builder
    {
        /** @var Builder $categories */
        $categories = static::query()->where(function (Builder $query): void {
            $query->where('parent_id', 0)->orWhereNull('parent_id');
        })->with(['children'])->orderBy('position', 'asc');

        return $categories;
    }
}
