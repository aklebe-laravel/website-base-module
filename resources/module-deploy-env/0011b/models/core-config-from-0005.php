<?php

use Modules\WebsiteBase\app\Models\CoreConfig;

return [
    // class of eloquent model
    'model'   => CoreConfig::class,
    // update data if exists and data differ (default false)
    'update'  => true,
    // columns to check if data already exists (AND WHERE)
    'uniques' => ['store_id', 'path'],
    // data rows itself
    'data'    => [
        [
            'store_id' => null,
            'module'   => 'website-base',
            'position' => 1612,
            'path'     => 'notification.preferred_channel',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'notification.channels.portal.enabled',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'notification.channels.email.enabled',
            'options'  => [
                'form' => [
                    'new_group' => true,
                ],
            ],
        ],
    ],
];

