<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;
use Modules\WebsiteBase\Models\IdeHelperModelAttribute;


/**
 * @mixin IdeHelperModelAttribute
 */
class ModelAttribute extends Model
{
    use HasFactory;
    use TraitBaseModel;

    protected $guarded = [];

}
