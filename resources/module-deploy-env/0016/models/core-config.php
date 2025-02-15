<?php

use Modules\WebsiteBase\app\Models\CoreConfig;

return [
    // class of eloquent model
    'model'                => CoreConfig::class,
    // update data if exists and data differ (default false)
    'update'               => true,
    // if update true only: don't update this fields
    'ignore_update_fields' => [
        'value',
    ],
    // columns to check if data already exists (AND WHERE)
    'uniques'              => ['store_id', 'path'],
    // data rows itself
    'data'                 => [
        [
            'store_id'   => null,
            'module'     => 'website-base',
            'path'       => 'notification.acl_group.staff',
            'form_input' => 'select',
        ],
        [
            'store_id'   => null,
            'module'     => 'website-base',
            'path'       => 'notification.acl_group.support',
            'form_input' => 'select',
        ],
        [
            'store_id'   => null,
            'module'     => 'website-base',
            'path'       => 'notification.acl_group.admin',
            'form_input' => 'select',
        ],
        [
            'store_id'   => null,
            'module'     => 'website-base',
            'path'       => 'notification.user.sender',
            'form_input' => 'select',
        ],
        [
            'store_id'   => null,
            'module'     => 'website-base',
            'path'       => 'notification.preferred_channel',
            'form_input' => 'select',
        ],
    ],
];

