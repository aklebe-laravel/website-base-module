<?php

return [
    // class of eloquent model
    "model"     => \Modules\WebsiteBase\app\Models\NotificationTemplate::class,
    // update data if exists and data differ (default false)
    "update"    => true,
    // columns to check if data already exists (AND WHERE)
    "uniques"   => ["code", "notification_channel"],
    // relations to update/create
    "relations" => [
        "view_template" => [
            // relation method which have to exists
            "method" => "viewTemplate",
            // column(s) to find specific #sync_relations items below
            "columns" => "code",
            // delete items if not listed here (default: false)
            "delete" => false,
        ],
    ],
    // data rows itself
    "data"      => [
        [
            "is_enabled"           => true,
            "code"                 => "auth_register_success",
            "notification_channel" => "email",
            "subject"              => 'You registered successfully: {{ config("app.name") }}',
            "description"          => "User registered successfully.",
            "#sync_relations"      => [
                "view_template" => [
                    "email_auth_register_success",
                ]
            ],
        ],
        [
            "is_enabled"           => true,
            "code"                 => "auth_forget_password",
            "notification_channel" => "email",
            "subject"              => 'Forget your password? {{ config("app.name") }}',
            "description"          => "User forgot password.",
            "#sync_relations"      => [
                "view_template" => [
                    "email_auth_forget_password",
                ]
            ],
        ],
        [
            "is_enabled"           => true,
            "code"                 => "user_login_info",
            "notification_channel" => "email",
            "subject"              => 'Your login data: {{ config("app.name") }}',
            "description"          => "Send user login data.",
            "#sync_relations"      => [
                "view_template" => [
                    "email_user_login_info",
                ]
            ],
        ],
        [
            "is_enabled"           => true,
            "code"                 => "system_info",
            "notification_channel" => "email",
            "subject"              => 'System info: {{ config("app.name") }}',
            "description"          => "Send system info.",
            "#sync_relations"      => [
                "view_template" => [
                    "email_system_info",
                ]
            ],
        ],
        [
            "is_enabled"           => true,
            "code"                 => "contact_request_message",
            "notification_channel" => "email",
            "subject"              => 'Contact Request: {{ config("app.name") }}',
            "description"          => "Contact request.",
            "#sync_relations"      => [
                "view_template" => [
                    "email_contact_request_message",
                ]
            ],
        ],
        [
            "is_enabled"           => true,
            "code"                 => "auth_register_success",
            "notification_channel" => "telegram",
            "subject"              => 'You registered successfully: {{ config("app.name") }}',
            "description"          => "User registered successfully.",
            "#sync_relations"      => [
                "view_template" => [
                    "telegram_auth_register_success",
                ]
            ],
        ],
        [
            "is_enabled"           => true,
            "code"                 => "auth_forget_password",
            "notification_channel" => "telegram",
            "subject"              => 'Forget your password? {{ config("app.name") }}',
            "description"          => "User forgot password.",
            "#sync_relations"      => [
                "view_template" => [
                    "telegram_auth_forget_password",
                ]
            ],
        ],
        [
            "is_enabled"           => true,
            "code"                 => "user_login_info",
            "notification_channel" => "telegram",
            "subject"              => 'Your login data: {{ config("app.name") }}',
            "description"          => "Send user login data.",
            "#sync_relations"      => [
                "view_template" => [
                    "telegram_user_login_info",
                ]
            ],
        ],
        [
            "is_enabled"           => true,
            "code"                 => "system_info",
            "notification_channel" => "telegram",
            "subject"              => 'System info: {{ config("app.name") }}',
            "description"          => "Send system info.",
            "#sync_relations"      => [
                "view_template" => [
                    "telegram_system_info",
                ]
            ],
        ],
        [
            "is_enabled"           => true,
            "code"                 => "contact_request_message",
            "notification_channel" => "telegram",
            "subject"              => 'Contact Request: {{ config("app.name") }}',
            "description"          => "Contact request.",
            "#sync_relations"      => [
                "view_template" => [
                    "telegram_contact_request_message",
                ]
            ],
        ],
    ]
];
