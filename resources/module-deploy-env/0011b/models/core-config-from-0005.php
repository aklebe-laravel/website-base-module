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
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'notification.channels.telegram.enabled',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'notification.channels.telegram.bot',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'notification.channels.telegram.default_public_group',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'notification.channels.telegram.default_staff_group',
        ],
    ],
];

