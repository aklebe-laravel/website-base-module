<?php

use Modules\WebsiteBase\app\Models\NotificationConcern;

return [
    // class of eloquent model
    "model"     => NotificationConcern::class,
    // update data if exists and data differ (default false)
    "update"    => true,
    // columns to check if data already exists (AND WHERE)
    "uniques"   => ["reason_code", "notificationTemplate.code", "notificationTemplate.notification_channel"],
    // "uniques"   => ["store_id", "notification_template_id", "reason_code"], // not working, because the columns have to be declared in each model data below
    // relations to update/create
    "relations" => [
        "store"                 => [
            // relation method which have to exists
            "method"  => "store",
            // column(s) to find specific #sync_relations items below
            "columns" => "code",
            // delete items if not listed here (default: false)
            "delete"  => false,
        ],
        "notification_template" => [
            // relation method which have to exists
            "method"  => "notificationTemplate",
            // column(s) to search #sync_relations items below
            "columns" => ["code", "notification_channel"],
            // delete items if not listed here (default: false)
            "delete"  => false,
        ],
    ],
    // data rows itself
    "data"      => [
        [
            "is_enabled"                                => true,
            "reason_code"                               => NotificationConcern::REASON_CODE_AUTH_REGISTER_SUCCESS,
            "notificationTemplate.code"                 => NotificationConcern::REASON_CODE_AUTH_REGISTER_SUCCESS,
            "notificationTemplate.notification_channel" => "email",
            "sender"                                    => '',
            "description"                               => "User registered successfully.",
            "tags"                                      => [
                "user",
                "customer",
                "registered",
            ],
            "meta_data"                                 => [],
            "#sync_relations"                           => [
                "store"                 => [
                    "default",
                ],
                "notification_template" => [
                    ["auth_register_success", "email"],
                ],
            ],
        ],
        [
            "is_enabled"                                => true,
            "reason_code"                               => NotificationConcern::REASON_CODE_AUTH_FORGET_PASSWORD,
            "notificationTemplate.code"                 => NotificationConcern::REASON_CODE_AUTH_FORGET_PASSWORD,
            "notificationTemplate.notification_channel" => "email",
            "sender"                                    => '',
            "description"                               => "User forgot password.",
            "tags"                                      => [
                "user",
                "customer",
                "password",
                "forget",
            ],
            "meta_data"                                 => [],
            "#sync_relations"                           => [
                "store"                 => [
                    "default",
                ],
                "notification_template" => [
                    ["auth_forget_password", "email"],
                ],
            ],
        ],
        [
            "is_enabled"                                => true,
            "reason_code"                               => NotificationConcern::REASON_CODE_AUTH_USER_LOGIN_DATA,
            "notificationTemplate.code"                 => "user_login_info",
            "notificationTemplate.notification_channel" => "email",
            "sender"                                    => '',
            "description"                               => "Send user login data.",
            "tags"                                      => [
                "user",
                "customer",
                "login",
                "remember",
            ],
            "meta_data"                                 => [],
            "#sync_relations"                           => [
                "store"                 => [
                    "default",
                ],
                "notification_template" => [
                    ["user_login_info", "email"],
                ],
            ],
        ],
        [
            "is_enabled"                                => true,
            "reason_code"                               => NotificationConcern::REASON_CODE_SYSTEM_INFO,
            "notificationTemplate.code"                 => NotificationConcern::REASON_CODE_SYSTEM_INFO,
            "notificationTemplate.notification_channel" => "email",
            "sender"                                    => '',
            "description"                               => "Send system info.",
            "tags"                                      => [
                "system",
                "information",
            ],
            "meta_data"                                 => [],
            "#sync_relations"                           => [
                "store"                 => [
                    "default",
                ],
                "notification_template" => [
                    ["system_info", "email"],
                ],
            ],
        ],
        [
            "is_enabled"                                => true,
            "reason_code"                               => NotificationConcern::REASON_CODE_CONTACT_REQUEST_MESSAGE,
            "notificationTemplate.code"                 => NotificationConcern::REASON_CODE_CONTACT_REQUEST_MESSAGE,
            "notificationTemplate.notification_channel" => "email",
            "sender"                                    => '',
            "description"                               => "Contact request.",
            "tags"                                      => [
                "user",
                "customer",
                "contact",
                "request",
            ],
            "meta_data"                                 => [],
            "#sync_relations"                           => [
                "store"                 => [
                    "default",
                ],
                "notification_template" => [
                    ["contact_request_message", "email"],
                ],
            ],
        ],
        [
            "is_enabled"                                => true,
            "reason_code"                               => NotificationConcern::REASON_CODE_AUTH_REGISTER_SUCCESS,
            "notificationTemplate.code"                 => NotificationConcern::REASON_CODE_AUTH_REGISTER_SUCCESS,
            "notificationTemplate.notification_channel" => "telegram",
            "sender"                                    => '',
            "description"                               => "User registered successfully.",
            "tags"                                      => [],
            "meta_data"                                 => [],
            "#sync_relations"                           => [
                "store"                 => [
                    "default",
                ],
                "notification_template" => [
                    ["auth_register_success", "telegram"],
                ],
            ],
        ],
        [
            "is_enabled"                                => true,
            "reason_code"                               => NotificationConcern::REASON_CODE_AUTH_FORGET_PASSWORD,
            "notificationTemplate.code"                 => NotificationConcern::REASON_CODE_AUTH_FORGET_PASSWORD,
            "notificationTemplate.notification_channel" => "telegram",
            "sender"                                    => '',
            "description"                               => "User forgot password.",
            "tags"                                      => [],
            "meta_data"                                 => [],
            "#sync_relations"                           => [
                "store"                 => [
                    "default",
                ],
                "notification_template" => [
                    ["auth_forget_password", "telegram"],
                ],
            ],
        ],
        [
            "is_enabled"                                => true,
            "reason_code"                               => NotificationConcern::REASON_CODE_AUTH_USER_LOGIN_DATA,
            "notificationTemplate.code"                 => "user_login_info",
            "notificationTemplate.notification_channel" => "telegram",
            "sender"                                    => '',
            "description"                               => "Send user login data.",
            "tags"                                      => [],
            "meta_data"                                 => [],
            "#sync_relations"                           => [
                "store"                 => [
                    "default",
                ],
                "notification_template" => [
                    ["user_login_info", "telegram"],
                ],
            ],
        ],
        [
            "is_enabled"                                => true,
            "reason_code"                               => NotificationConcern::REASON_CODE_SYSTEM_INFO,
            "notificationTemplate.code"                 => NotificationConcern::REASON_CODE_SYSTEM_INFO,
            "notificationTemplate.notification_channel" => "telegram",
            "sender"                                    => '',
            "description"                               => "Send system info.",
            "tags"                                      => [],
            "meta_data"                                 => [],
            "#sync_relations"                           => [
                "store"                 => [
                    "default",
                ],
                "notification_template" => [
                    ["system_info", "telegram"],
                ],
            ],
        ],
        [
            "is_enabled"                                => true,
            "reason_code"                               => NotificationConcern::REASON_CODE_CONTACT_REQUEST_MESSAGE,
            "notificationTemplate.code"                 => NotificationConcern::REASON_CODE_CONTACT_REQUEST_MESSAGE,
            "notificationTemplate.notification_channel" => "telegram",
            "sender"                                    => '',
            "description"                               => "Contact request.",
            "tags"                                      => [],
            "meta_data"                                 => [],
            "#sync_relations"                           => [
                "store"                 => [
                    "default",
                ],
                "notification_template" => [
                    ["contact_request_message", "telegram"],
                ],
            ],
        ],
    ],
];
