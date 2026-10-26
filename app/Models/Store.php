<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;
use Modules\WebsiteBase\database\factories\StoreFactory;

/**
 * @mixin IdeHelperStore
 */
class Store extends Model
{
    use TraitAttributeAssignment;
    use TraitBaseModel;
    use HasFactory;

    protected $table = 'stores';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * You can use this instead of newFactory()
     * @var string
     */
    public static string $factory = StoreFactory::class;

}
