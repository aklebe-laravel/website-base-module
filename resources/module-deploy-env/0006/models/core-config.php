<?php

return [
    // class of eloquent model
    "model"   => \Modules\WebsiteBase\app\Models\CoreConfig::class,
    // update data if exists and data differ (default false)
    "update"  => true,
    // columns to check if data already exists (AND WHERE)
    "uniques" => ["store_id", "path"],
    // data rows itself
    "data"    => [
        [
            'store_id'    => null,
            "path"        => "channels.email.enabled",
            "value"       => "1",
            "form_input"  => "switch",
            "description" => "Enable email in general.",
        ],
        [
            'store_id'    => null,
            "path"        => "channels.telegram.enabled",
            "value"       => "0",
            "form_input"  => "switch",
            "description" => "Enable telegram in general.",
        ],
    ]
];

