<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;

/**
 * @mixin IdeHelperCoreConfig
 */
class CoreConfig extends Model
{
    use HasFactory;
    use TraitBaseModel;

    protected $table = 'core_configs';

    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

}
