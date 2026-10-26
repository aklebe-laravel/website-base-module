<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;

/**
 * @mixin IdeHelperChangelog
 */
class Changelog extends Model
{
    use TraitBaseModel;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $table = 'changelogs';

    /**
     * acl_resources should cast from json to an array and via versa
     *
     * @var string[]
     */
    protected $casts = [
        'acl_resources' => 'array',
    ];

}
