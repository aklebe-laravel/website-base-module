<?php

use Modules\WebsiteBase\app\Models\ModelAttribute;

return [
    // class of eloquent model
    "model"   => ModelAttribute::class,
    // update data if exists and data differ (default false)
    "update"  => true,
    // columns to check if data already exists (AND WHERE)
    "uniques" => [],
    // data rows itself
    "data"    => [
        [
            'code'              => 'name',
            "description"        => "Name of this model",
        ],
        [
            'code'              => 'negotiable',
            "description"        => "Negotiable",
        ],
        [
            'code'              => 'email',
            "description"        => "Email address",
        ],
        [
            'code'              => 'firstname',
            "description"        => "Firstname",
        ],
        [
            'code'              => 'lastname',
            "description"        => "Lastname",
        ],
        [
            'code'              => 'description',
            "description"        => "Description",
        ],
        [
            'code'              => 'extended_description',
            "description"        => "Extended Description",
        ],
        [
            'code'              => 'short_description',
            "description"        => "Short description",
        ],
        [
            'code'              => 'meta_description',
            "description"        => "Meta description",
        ],
        [
            'code'              => 'url',
            "description"        => "Url",
        ],
        [
            'code'              => 'image',
            "description"        => "Image",
        ],
        [
            'code'              => 'price',
            "description"        => "Price",
        ],
        [
            'code'              => 'currency',
            "description"        => "Currency",
        ],
        [
            'code'              => 'qty',
            "description"        => "Quantity",
        ],
        [
            'code'              => 'payment_method',
            "description"        => "Payment method",
        ],
        [
            'code'              => 'address',
            "description"        => "Address",
        ],
        [
            'code'              => 'shipping_method',
            "description"        => "Shipping method",
        ],
        [
            'code'              => 'user_bio',
            "description"        => "User Bio",
        ],
        [
            'code'              => 'user_register_hint',
            "description"        => "User Register Hint",
        ],
        [
            'code'              => 'telegram_id',
            "description"        => "Telegram ID",
        ],
        [
            'code'              => 'use_telegram',
            "description"        => "Use telegram for communications",
        ],
        [
            'code'              => 'preferred_notification_channel',
            "description"        => "Like email or telegram",
        ],
    ]
];

