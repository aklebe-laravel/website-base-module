<?php

namespace Modules\WebsiteBase\app\Models;

use Modules\WebsiteBase\app\Models\Base\CmsBase;

/**
 * @mixin IdeHelperCmsPage
 */
class CmsPage extends CmsBase
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $table = 'cms_pages';


}
