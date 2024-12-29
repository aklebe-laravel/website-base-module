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
            'path'        => 'channels.email.enabled',
            'value'       => '1',
            'label'       => 'Enable channel emails',
            'form_input'  => 'switch',
            'description' => 'Enable channel emails',
        ],
        [
            'store_id'    => null,
            'path'        => 'channels.telegram.enabled',
            'value'       => '0',
            'label'       => 'Enable channel telegram',
            'form_input'  => 'switch',
            'description' => 'Enable channel telegram',
        ],
    ],
];

