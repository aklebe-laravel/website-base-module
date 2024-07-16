<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use HasFactory;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * appends will be filled dynamically for this instance by static::retrieved()
     *
     * @var array
     */
    protected $appends = ['extra_attributes'];

    /**
     * properties should always cast from json to an array and via versa
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
    public function parent()
    {
        return $this->belongsTo(self::class);
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->orderBy('position', 'asc');
    }

    /**
     * @return Builder
     */
    public static function getRootCategories(): Builder
    {
        /** @var Builder $categories */
        $categories = self::with([])->where('parent_id', '=', 0)->orWhereNull('parent_id')->orderBy('position', 'asc');
        return $categories;
    }
}
