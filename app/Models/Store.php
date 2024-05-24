<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;
use Modules\WebsiteBase\Models\IdeHelperStore;


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
     * appends will be filled dynamically for this instance by ModelWithAttributesLoaded
     *
     * @var array
     */
    protected $appends = ['extra_attributes'];

}
