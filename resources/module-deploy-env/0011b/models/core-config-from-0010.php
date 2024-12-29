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
            'path'     => 'import.enabled',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'import.delay',
            'options'  => [
                'form' => [
                    'new_group' => true,
                ],
            ],
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'import.rate-limiter.max',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'import.rate-limiter.reset',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'import.rate-limiter.file.max',
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'import.rate-limiter.file.reset',
        ],
    ],
];

