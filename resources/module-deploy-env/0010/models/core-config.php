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
            'path'        => 'import.enabled',
            'value'       => '0',
            'label'       => 'Enable imports',
            'form_input'  => 'switch',
            'description' => 'Import allowed or not',
        ],
        [
            'store_id'    => null,
            'path'        => 'import.delay',
            'value'       => '0',
            'label'       => 'Import delay',
            'description' => 'Import delay in seconds after import was launched.',
        ],
        [
            'store_id'    => null,
            'path'        => 'import.rate-limiter.max',
            'value'       => '10',
            'label'       => 'Max imports',
            'description' => 'Max imports per day (or better: per import.rate-limiter.reset)',
        ],
        [
            'store_id'    => null,
            'path'        => 'import.rate-limiter.reset',
            'value'       => '86400',
            'label'       => 'Import limit reset',
            'description' => 'Import rate limiter reset in seconds',
        ],
        [
            'store_id'    => null,
            'path'        => 'import.rate-limiter.file.max',
            'value'       => '1',
            'label'       => 'Import limit file',
            'description' => 'Max imports for a specific file per import.rate-limiter.file.reset',
        ],
        [
            'store_id'    => null,
            'path'        => 'import.rate-limiter.file.reset',
            'value'       => '300',
            'label'       => 'Import limit file reset',
            'description' => 'Import rate limiter per file reset in seconds',
        ],
    ],
];

