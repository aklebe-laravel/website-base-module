<?php

use Modules\WebsiteBase\app\Models\CoreConfig;

return [
    // class of eloquent model
    'model'   => CoreConfig::class,
    // update data if exists and data differ (default false)
    'update'  => true,
    // if update true only: don't update this fields
    'ignore_update_fields' => [
        'value'
    ],
    // columns to check if data already exists (AND WHERE)
    'uniques' => ['store_id', 'path'],
    // data rows itself
    'data'    => [
        [
            'store_id'    => null,
            'path'        => 'notification.preferred_channel',
            'value'       => '',
            'label'       => 'Preferred channel',
            'form_input'  => 'website-base::select_notification_channel',
            'description' => 'Preferred channel like portal, email, telegram, sms',
        ],
        [
            'store_id'    => null,
            'path'        => 'notification.channels.portal.enabled',
            'value'       => '0',
            'label'       => 'Enable portal notification',
            'form_input'  => 'switch',
            'description' => 'Enable portal notification.',
        ],
        [
            'store_id'    => null,
            'path'        => 'notification.channels.email.enabled',
            'value'       => '0',
            'label'       => 'Enable email notification',
            'form_input'  => 'switch',
            'description' => 'Enable email notification.',
        ],
        [
            'store_id'    => null,
            'path'        => 'notification.channels.telegram.enabled',
            'value'       => '0',
            'label'       => 'Enable telegram notification',
            'form_input'  => 'switch',
            'description' => 'Enable telegram notification.',
        ],
        [
            'store_id'    => null,
            'path'        => 'notification.channels.telegram.bot',
            'value'       => '',
            'label'       => 'Telegram bot',
            'form_input'  => 'text',
            'description' => 'Name of bot declared in config("telegram.bots"). Empty to use the default one.',
        ],
        [
            'store_id'    => null,
            'path'        => 'notification.channels.telegram.default_public_group',
            'value'       => '',
            'label'       => 'Default telegram public group',
            'form_input'  => 'website-base::select_telegram_group',
            'description' => 'Public notifications. (in telegram and in telegram_identities) telegram group or channel.',
        ],
        [
            'store_id'    => null,
            'path'        => 'notification.channels.telegram.default_staff_group',
            'value'       => '',
            'label'       => 'Default telegram staff group',
            'form_input'  => 'website-base::select_telegram_group',
            'description' => 'Staff notifications. (in telegram and in telegram_identities) telegram group or channel.',
        ],
    ],
];

