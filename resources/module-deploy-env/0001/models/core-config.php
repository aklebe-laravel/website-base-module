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
            'store_id'    => null,
            'path'        => 'site.auth.enabled',
            'value'       => '1',
            'position'    => 820,
            'label'       => 'Enable Auth',
            'form_input'  => 'switch',
            'description' => 'Auth functionality available (forms, etc...)',
        ],
        [
            'store_id'    => null,
            'path'        => 'site.auth.register.enabled',
            'value'       => '1',
            'position'    => 830,
            'label'       => 'Allow register users',
            'form_input'  => 'switch',
            'description' => 'User registering allowed',
        ],
        [
            'store_id'    => null,
            'path'        => 'site.auth.login.enabled',
            'value'       => '1',
            'position'    => 840,
            'label'       => 'Allow user login',
            'form_input'  => 'switch',
            'description' => 'User login allowed',
        ],
        [
            'store_id'    => null,
            'path'        => 'email.enabled',
            'value'       => '0',
            'label'       => 'Allow emails',
            'form_input'  => 'switch',
            'description' => 'Email transport allowed or not',
        ],
        [
            'store_id'    => null,
            'path'        => 'email.rate-limiter.max',
            'value'       => '100',
            'label'       => 'Max emails per day',
            'description' => 'Max emails per day (or better allow x per email.rate-limiter.reset)',
        ],
        [
            'store_id'    => null,
            'path'        => 'email.rate-limiter.reset',
            'value'       => '86400',
            'label'       => 'Email limit reset',
            'description' => 'Email rate limiter reset in seconds',
        ],
    ],
];

