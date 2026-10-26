<?php

namespace Modules\WebsiteBase\app\Models;

use Modules\WebsiteBase\app\Models\Base\CmsBase;

/**
 * @mixin IdeHelperCmsContent
 */
class CmsContent extends CmsBase
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $table = 'cms_contents';

}
