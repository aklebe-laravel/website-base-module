<?php

use Modules\WebsiteBase\app\Models\CoreConfig;

return [
    // class of eloquent model
    "model"   => CoreConfig::class,
    // update data if exists and data differ (default false)
    "update"  => false,
    // columns to check if data already exists (AND WHERE)
    "uniques" => ["store_id", "path"],
    // data rows itself
    "data"    => [
        [
            'store_id'    => null,
            "path"        => "import.enabled",
            "value"       => "0",
            "form_input"  => "switch",
            "description" => "Import allowed or not",
        ],
        [
            'store_id'    => null,
            "path"        => "import.delay",
            "value"       => "0",
            "description" => "Import delay in seconds after import was launched.",
        ],
        [
            'store_id'    => null,
            "path"        => "import.rate-limiter.max",
            "value"       => "10",
            "description" => "Max imports per day or more true: per import.rate-limiter.reset",
        ],
        [
            'store_id'    => null,
            "path"        => "import.rate-limiter.reset",
            "value"       => "86400",
            "description" => "Import rate limiter reset in seconds",
        ],
        [
            'store_id'    => null,
            "path"        => "import.rate-limiter.file.max",
            "value"       => "1",
            "description" => "Max imports for a specific file per import.rate-limiter.file.reset",
        ],
        [
            'store_id'    => null,
            "path"        => "import.rate-limiter.file.reset",
            "value"       => "300",
            "description" => "Import rate limiter per file reset in seconds",
        ],
    ],
];

