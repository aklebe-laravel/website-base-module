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
            "path"        => "notification.preferred_channel",
            "value"       => "",
            "form_input"  => "website-base::select_notification_channel",
            "description" => "Preferred channel like portal, email, telegram, sms",
        ],
        [
            'store_id'    => null,
            "path"        => "notification.channels.portal.enabled",
            "value"       => "0",
            "form_input"  => "switch",
            "description" => "Enable portal notification.",
        ],
        [
            'store_id'    => null,
            "path"        => "notification.channels.email.enabled",
            "value"       => "0",
            "form_input"  => "switch",
            "description" => "Enable email notification.",
        ],
        [
            'store_id'    => null,
            "path"        => "notification.channels.telegram.enabled",
            "value"       => "0",
            "form_input"  => "switch",
            "description" => "Enable telegram notification.",
        ],
        [
            'store_id'    => null,
            "path"        => "notification.channels.telegram.bot",
            "value"       => "",
            "form_input"  => "text",
            "description" => "Name of bot declared in config('telegram.bots'). Empty to use the default one.",
        ],
        [
            'store_id'    => null,
            "path"        => "notification.channels.telegram.default_public_group",
            "value"       => "",
            "form_input"  => "website-base::select_telegram_group",
            "description" => "Public notifications. (in telegram and in telegram_identities) telegram group or channel.",
        ],
        [
            'store_id'    => null,
            "path"        => "notification.channels.telegram.default_staff_group",
            "value"       => "",
            "form_input"  => "website-base::select_telegram_group",
            "description" => "Staff notifications. (in telegram and in telegram_identities) telegram group or channel.",
        ],
    ]
];

