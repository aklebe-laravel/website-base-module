<?php

return [
    // class of eloquent model
    "model"   => \Modules\WebsiteBase\app\Models\Store::class,
    // update data if exists and data differ (default false)
    "update"  => false,
    // columns to check if data already exists (AND WHERE)
    "uniques" => ["code"],
    // data rows itself
    "data"    => [
        [
            "code" => "default",
        ],
        [
            "code" => "default_02",
            "url"  => url(''),
        ],
        [
            "code" => "default_03",
        ],
    ]
];

