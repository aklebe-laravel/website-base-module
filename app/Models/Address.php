<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;
use Modules\WebsiteBase\database\factories\AddressFactory;

/**
 * @mixin IdeHelperAddress
 */
class Address extends Model
{
    use HasFactory;
    use TraitBaseModel;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $table = 'addresses';

    /**
     * You can use this instead of newFactory()
     * @var string
     */
    public static string $factory = AddressFactory::class;

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo($this::$userClassName);
    }
}
