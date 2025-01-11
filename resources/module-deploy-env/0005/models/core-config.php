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
            'store_id'    => null,
            'path'        => 'notification.channels.portal.enabled',
            'value'       => '0',
            'position'    => 1520,
            'label'       => 'Enable portal notification',
            'form_input'  => 'switch',
            'description' => 'Enable portal notification.',
        ],
        [
            'store_id'    => null,
            'path'        => 'notification.channels.email.enabled',
            'value'       => '0',
            'position'    => 1510,
            'label'       => 'Enable email notification',
            'form_input'  => 'switch',
            'description' => 'Enable email notification.',
            'options'  => [
                'form' => [
                    'new_group' => true,
                ],
            ],
        ],
    ],
];

