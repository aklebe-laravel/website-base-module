<?php

use Modules\WebsiteBase\app\Models\ModelAttribute;
use Modules\WebsiteBase\app\Models\User as UserModel;

return [
    // class of eloquent model
    'model'   => ModelAttribute::class,
    // update data if exists and data differ (default false)
    'update'  => true,
    // columns to check if data already exists (AND WHERE)
    'uniques' => ['module','code'], // since 1.0.0 'module','code'
    // data rows itself
    'data'    => [
        [
            'module'      => 'website-base',
            'code'        => UserModel::ATTR_NOTIFICATION_CHANNELS,
            'description' => 'Preferred notification channels',
        ],
    ],
];

