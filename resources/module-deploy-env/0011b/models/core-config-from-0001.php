<?php

use Modules\WebsiteBase\app\Models\CoreConfig;

return [
    // class of eloquent model
    'model'   => CoreConfig::class,
    // update data if exists and data differ (default false)
    'update'  => true,
    // if update true only: don't update this fields
    'ignore_update_fields' => [
        'value'
    ],
    // columns to check if data already exists (AND WHERE)
    'uniques' => ['store_id', 'path'],
    // data rows itself
    'data'    => [
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'site.public',
        ],
        // this replaced the old 'site.auth', more data for first insert needed
        [
            'store_id'    => null,
            'path'        => 'site.auth.enabled',
            'module'      => 'website-base',
            'value'       => '1',
            'form_input'  => 'switch',
            'description' => 'Auth functionality available (forms, etc...)',
            'options'     => [
                'form' => [
                    'new_group' => true,
                ],
            ],
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'site.auth.register.enabled',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'site.auth.login.enabled',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'email.enabled',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'email.rate-limiter.max',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'email.rate-limiter.reset',
        ],
    ],
];

