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
            "path"        => "site.public",
            "value"       => "0",
            "form_input"  => "switch",
            "description" => "Site is public (1) or forced to login for everyone (0).",
        ],
        [
            'store_id'    => null,
            "path"        => "site.auth",
            "value"       => "1",
            "form_input"  => "switch",
            "description" => "Auth functionality available (forms, etc...)",
        ],
        [
            'store_id'    => null,
            "path"        => "site.auth.register.enabled",
            "value"       => "1",
            "form_input"  => "switch",
            "description" => "User registering allowed",
        ],
        [
            'store_id'    => null,
            "path"        => "site.auth.login.enabled",
            "value"       => "1",
            "form_input"  => "switch",
            "description" => "User login allowed",
        ],
        [
            'store_id'    => null,
            "path"        => "email.enabled",
            "value"       => "0",
            "form_input"  => "switch",
            "description" => "Email transport allowed or not",
        ],
        [
            'store_id'    => null,
            "path"        => "email.rate-limiter.max",
            "value"       => "100",
            "description" => "Max emails per day or more true: per email.rate-limiter.reset",
        ],
        [
            'store_id'    => null,
            "path"        => "email.rate-limiter.reset",
            "value"       => "86400",
            "description" => "Email Rate Limiter Reset in Seconds",
        ],
    ],
];

