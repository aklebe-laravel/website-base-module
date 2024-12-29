<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;
use Modules\WebsiteBase\database\factories\CoreConfigFactory;

/**
 * @mixin IdeHelperCoreConfig
 */
class CoreConfig extends Model
{
    use HasFactory;
    use TraitBaseModel;

    /**
     * @var string
     */
    protected $table = 'core_configs';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * properties should always cast from json to an array and via versa
     *
     * @var string[]
     */
    protected $casts = [
        'options' => 'array',
    ];

    /**
     * You can use this instead of newFactory()
     *
     * @var string
     */
    public static string $factory = CoreConfigFactory::class;

    /**
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

}
