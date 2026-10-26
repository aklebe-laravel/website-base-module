<?php

use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;
use Modules\WebsiteBase\app\Models\ModelAttribute;

return [
    'version'    => '2.0',
    // class of eloquent model
    'model'      => ModelAttribute::class,
    // create data if not exists (default true)
    'can_create' => true,
    // can update data if exists and data differ (default false)
    'can_update' => true,
    // columns to check if data already exists (AND WHERE)
    'uniques'    => ['module', 'code'], // since 1.0.0 'module','code'
    // data rows itself
    'data'       => [
        [
            'module'      => 'website-base',
            'code'        => ExtraAttributeModel::ATTR_PREFERRED_NOTIFICATION_CHANNELS,
            'description' => 'Preferred notification channels',
        ],
    ],
];

