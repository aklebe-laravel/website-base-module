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
            'position'    => 3000,
            'options'  => [
                'form' => [
                    'new_group' => true,
                ],
            ],
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'import.delay',
            'position'    => 3010,
            'options'  => [],
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'import.rate-limiter.max',
            'position'    => 3030,
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'import.rate-limiter.reset',
            'position'    => 3032,
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'import.rate-limiter.file.max',
            'position'    => 3040,
        ],
        [
            'store_id' => null,
            'module'   => 'website-base',
            'path'     => 'import.rate-limiter.file.reset',
            'position'    => 3042,
        ],
    ],
];

