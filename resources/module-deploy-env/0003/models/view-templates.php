<?php

use Modules\WebsiteBase\app\Models\ViewTemplate;

return [
    // class of eloquent model
    "model"     => ViewTemplate::class,
    // update data if exists and data differ (default false)
    "update"    => true,
    // columns to check if data already exists (AND WHERE)
    "uniques"   => ["code"],
    // relations to update/create
    "relations" => [],
    // data rows itself
    "data"      => [
        [
            "is_enabled"        => true,
            "code"              => "email_auth_register_success",
            "content"           => 'Hello {{ $user->name }}<br>You registered successfully!',
            "view_file"         => "notifications.emails.welcome",
            "parameter_variant" => ViewTemplate::PARAMETER_VARIANT_DEFAULT,
            "description"       => "User registered successfully.",
        ],
        [
            "is_enabled"        => true,
            "code"              => "email_auth_forget_password",
            "view_file"         => "notifications.emails.forget-password",
            "parameter_variant" => ViewTemplate::PARAMETER_VARIANT_DEFAULT,
            "description"       => "User forgot password.",
        ],
        [
            "is_enabled"        => true,
            "code"              => "email_user_login_info",
            "view_file"         => "notifications.emails.user_login_data",
            "parameter_variant" => ViewTemplate::PARAMETER_VARIANT_DEFAULT,
            "description"       => "Send user login data.",
        ],
        [
            "is_enabled"        => true,
            "code"              => "email_system_info",
            "view_file"         => "notifications.emails.system-info",
            "parameter_variant" => ViewTemplate::PARAMETER_VARIANT_DEFAULT,
            "description"       => "Send system info.",
        ],
        [
            "is_enabled"        => true,
            "code"              => "email_contact_request_message",
            "view_file"         => "notifications.emails.contact-request",
            "parameter_variant" => ViewTemplate::PARAMETER_VARIANT_DEFAULT,
            "description"       => "Contact request.",
        ],
        [
            "is_enabled"        => true,
            "code"              => "telegram_auth_register_success",
            "view_file"         => "notifications.telegram.welcome",
            "parameter_variant" => ViewTemplate::PARAMETER_VARIANT_DEFAULT,
            "description"       => "User registered successfully.",
        ],
        [
            "is_enabled"        => true,
            "code"              => "telegram_auth_forget_password",
            "view_file"         => "notifications.telegram.forget-password",
            "parameter_variant" => ViewTemplate::PARAMETER_VARIANT_DEFAULT,
            "description"       => "User forgot password.",
        ],
        [
            "is_enabled"        => true,
            "code"              => "telegram_user_login_info",
            "view_file"         => "notifications.telegram.user_login_data",
            "parameter_variant" => ViewTemplate::PARAMETER_VARIANT_DEFAULT,
            "description"       => "Send user login data.",
        ],
        [
            "is_enabled"        => true,
            "code"              => "telegram_system_info",
            "view_file"         => "notifications.telegram.system-info",
            "parameter_variant" => ViewTemplate::PARAMETER_VARIANT_DEFAULT,
            "description"       => "Send system info.",
        ],
        [
            "is_enabled"        => true,
            "code"              => "telegram_contact_request_message",
            "view_file"         => "notifications.telegram.contact-request",
            "parameter_variant" => ViewTemplate::PARAMETER_VARIANT_DEFAULT,
            "description"       => "Contact request.",
        ],
    ]
];
