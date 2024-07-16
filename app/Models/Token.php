<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;

/**
 * @mixin IdeHelperToken
 */
class Token extends Model
{
    use HasFactory;
    use TraitBaseModel;

    const PURPOSE_LOGIN = 'LOGIN';
    const PURPOSE_MAKE_TRADER = 'MAKE_TRADER';

    const PURPOSE_LIST = [
        self::PURPOSE_LOGIN,
        self::PURPOSE_MAKE_TRADER,
    ];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $table = 'tokens';

    /**
     * values should cast from json to an array and via versa
     *
     * @var string[]
     */
    protected $casts = [
        'values' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo($this::$userClassName);
    }

}
