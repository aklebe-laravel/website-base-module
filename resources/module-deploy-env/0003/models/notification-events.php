<?php

use Modules\WebsiteBase\app\Models\NotificationConcern;
use Modules\WebsiteBase\app\Models\NotificationEvent;

return [
    'version'    => '2.0',
    // class of eloquent model
    'model'      => NotificationEvent::class,
    // create data if not exists (default true)
    'can_create' => true,
    // can update data if exists and data differ (default false)
    'can_update' => true,
    // columns to check if data already exists (AND WHERE)
    'uniques'    => ['name', 'event_code'],
    // relations to update/create
    'relations'  => [
        'notification_concerns' => [
            // relation method which have to exists
            'method'      => 'notificationConcerns',
            // column(s) to find specific #sync_relations items below
            'columns'     => 'reason_code',
            // delete items if not listed here (default: false)
            'auto_delete' => false,
        ],
        'acl_resources'         => [
            // relation method which have to exists
            'method'      => 'aclResources',
            // column(s) to find specific #sync_relations items below
            'columns'     => 'code',
            // delete items if not listed here (default: false)
            'auto_delete' => false,
        ],
        'users'                 => [
            // relation method which have to exists
            'method'      => 'users',
            // column(s) to find specific #sync_relations items below
            'columns'     => 'name',
            // delete items if not listed here (default: false)
            'auto_delete' => false,
        ],
    ],
    // data rows itself
    'data'       => [
        [
            'is_enabled'      => true,
            'event_trigger'   => 'manually',
            'name'            => 'Send User Login Data',
            'subject'         => 'Your login data: {{ config("app.name") }}',
            'event_code'      => NotificationEvent::EVENT_CODE_NOTIFY_USERS,
            // 'force_channel'   => 'email',
            'content'         => '',
            // 'content_data'    => '',
            'description'     => 'User login data notification.',
            '#sync_relations' => [
                'notification_concerns' => [
                    NotificationConcern::REASON_CODE_AUTH_USER_LOGIN_DATA,
                ],
                'users'                 => [
                    'Admin',
                ],
            ],
        ],
        [
            'is_enabled'      => true,
            'event_trigger'   => 'manually',
            'name'            => 'Send system info.',
            'subject'         => 'System Info Mail: {{ config("app.name") }}',
            'event_code'      => NotificationEvent::EVENT_CODE_NOTIFY_DEFAULT,
            // 'force_channel'   => 'email',
            'content'         => '',
            // 'content_data'  => '',
            'description'     => 'System information.',
            '#sync_relations' => [
                'notification_concerns' => [
                    NotificationConcern::REASON_CODE_SYSTEM_INFO,
                ],
                'acl_resources'         => [
                    // AclResource::RES_ADMIN,
                    // AclResource::RES_STAFF,
                    // AclResource::RES_DEVELOPER,
                ],
                'users'                 => [
                    'Admin',
                ],
            ],
        ],
        [
            'is_enabled'      => true,
            'event_trigger'   => 'manually',
            'name'            => 'Ping',
            'subject'         => 'Ping from: {{ config("app.name") }}',
            'event_code'      => NotificationEvent::EVENT_CODE_NOTIFY_DEFAULT,
            'content'         => 'PING!',
            'description'     => 'Just send a ping..',
            '#sync_relations' => [
                'notification_concerns' => [],
                'acl_resources'         => [],
                'users'                 => [
                    'Admin',
                ],
            ],
        ],
    ],
];
