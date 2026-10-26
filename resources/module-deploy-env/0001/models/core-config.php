<?php

use Modules\Acl\app\Models\AclGroup;
use Modules\WebsiteBase\app\Models\CoreConfig;
use Modules\WebsiteBase\app\Models\User;

return [
    'version'              => '2.0',
    // class of eloquent model
    'model'                => CoreConfig::class,
    // update data if exists and data differ (default false)
    'can_update'           => true,
    // if update true only: don't update this fields
    'ignore_update_fields' => [
        'value',
    ],
    // columns to check if data already exists (AND WHERE)
    'uniques'              => ['store_id', 'module', 'path'],
    // data rows itself
    'data'                 => [
        [
            'store_id'    => null,
            'path'        => 'site.auth.enabled',
            'value'       => '1',
            'position'    => 820,
            'label'       => 'Enable Auth',
            'form_input'  => 'switch',
            'description' => 'Auth functionality available (forms, etc...)',
            'module'      => 'website-base',
        ],
        [
            'store_id'    => null,
            'path'        => 'site.auth.register.enabled',
            'value'       => '1',
            'position'    => 830,
            'label'       => 'Allow register users',
            'form_input'  => 'switch',
            'description' => 'User registering allowed',
            'module'      => 'website-base',
        ],
        [
            'store_id'    => null,
            'path'        => 'site.auth.login.enabled',
            'value'       => '1',
            'position'    => 840,
            'label'       => 'Allow user login',
            'form_input'  => 'switch',
            'description' => 'User login allowed',
            'module'      => 'website-base',
        ],
        [
            'store_id'    => null,
            'path'        => 'email.enabled',
            'value'       => '0',
            'label'       => 'Allow emails',
            'form_input'  => 'switch',
            'description' => 'Email transport allowed or not',
            'module'      => 'website-base',
            'position'    => 1310,
            'options'     => [
                'form' => [
                    'new_group' => true,
                ],
            ],
        ],
        [
            'store_id'    => null,
            'path'        => 'email.rate-limiter.max',
            'value'       => '100',
            'label'       => 'Max emails per day',
            'description' => 'Max emails per day (or better allow x per email.rate-limiter.reset)',
            'module'      => 'website-base',
            'position'    => 1370,
        ],
        [
            'store_id'    => null,
            'path'        => 'email.rate-limiter.reset',
            'value'       => '86400',
            'label'       => 'Email limit reset',
            'description' => 'Email rate limiter reset in seconds',
            'module'      => 'website-base',
            'position'    => 1380,
        ],
        [
            'store_id'    => null,
            'path'        => 'notification.channels.portal.enabled',
            'value'       => '0',
            'position'    => 1520,
            'label'       => 'Enable portal notification',
            'form_input'  => 'switch',
            'description' => 'Enable portal notification.',
            'module'      => 'website-base',
        ],
        [
            'store_id'    => null,
            'path'        => 'notification.channels.email.enabled',
            'value'       => '0',
            'position'    => 1510,
            'label'       => 'Enable email notification',
            'form_input'  => 'switch',
            'description' => 'Enable email notification.',
            'module'      => 'website-base',
            'options'     => [
                'form' => [
                    'new_group' => true,
                ],
            ],
        ],
        [
            'store_id'    => null,
            'path'        => 'broadcast.enabled',
            'position'    => 1320,
            'value'       => '0',
            'label'       => 'Enable broadcast',
            'form_input'  => 'switch',
            'description' => 'Enable pusher/broadcast.',
            'module'      => 'website-base',
        ],
        [
            'store_id'    => null,
            'path'        => 'notification.simulate',
            'value'       => '0',
            'position'    => 1600,
            'label'       => 'Simulate notifications',
            'form_input'  => 'switch',
            'description' => 'Fake all notifications and send them to log.',
            'module'      => 'website-base',
        ],
        [
            'store_id'    => null,
            'path'        => 'import.enabled',
            'value'       => '0',
            'label'       => 'Enable imports',
            'form_input'  => 'switch',
            'description' => 'Import allowed or not',
            'module'      => 'website-base',
            'position'    => 3000,
            'options'     => [
                'form' => [
                    'new_group' => true,
                ],
            ],
        ],
        [
            'store_id'    => null,
            'path'        => 'import.delay',
            'value'       => '0',
            'label'       => 'Import delay',
            'description' => 'Import delay in seconds after import was launched.',
            'module'      => 'website-base',
            'position'    => 3010,
        ],
        [
            'store_id'    => null,
            'path'        => 'import.rate-limiter.max',
            'value'       => '10',
            'label'       => 'Max imports',
            'description' => 'Max imports per day (or better: per import.rate-limiter.reset)',
            'module'      => 'website-base',
            'position'    => 3030,
        ],
        [
            'store_id'    => null,
            'path'        => 'import.rate-limiter.reset',
            'value'       => '86400',
            'label'       => 'Import limit reset',
            'description' => 'Import rate limiter reset in seconds',
            'module'      => 'website-base',
            'position'    => 3032,
        ],
        [
            'store_id'    => null,
            'path'        => 'import.rate-limiter.file.max',
            'value'       => '1',
            'label'       => 'Import limit file',
            'description' => 'Max imports for a specific file per import.rate-limiter.file.reset',
            'module'      => 'website-base',
            'position'    => 3040,
        ],
        [
            'store_id'    => null,
            'path'        => 'import.rate-limiter.file.reset',
            'value'       => '300',
            'label'       => 'Import limit file reset',
            'description' => 'Import rate limiter per file reset in seconds',
            'module'      => 'website-base',
            'position'    => 3042,
        ],
        [
            'store_id'   => null,
            'module'     => 'website-base',
            'path'       => 'notification.acl_group.staff',
            'form_input' => 'select',
        ],
        [
            'store_id'   => null,
            'module'     => 'website-base',
            'path'       => 'notification.acl_group.support',
            'form_input' => 'select',
        ],
        [
            'store_id'   => null,
            'module'     => 'website-base',
            'path'       => 'notification.acl_group.admin',
            'form_input' => 'select',
        ],
        [
            'store_id'   => null,
            'module'     => 'website-base',
            'path'       => 'notification.user.sender',
            'form_input' => 'select',
        ],
        [
            'store_id'   => null,
            'module'     => 'website-base',
            'path'       => 'notification.preferred_channel',
            'form_input' => 'select',
            'position'   => 1612,
        ],
        [
            'store_id'    => null,
            'module'      => 'website-base',
            'path'        => 'notification.acl_group.staff',
            'value'       => AclGroup::with([])
                ->where('name', 'Staff-Notifications')
                ->first()
                ?->getKey(),
            'position'    => 1630,
            'label'       => 'Staff Notifications',
            'form_input'  => 'website-base::select_acl_group',
            'description' => 'used to notify staff members',
        ],
        [
            'store_id'    => null,
            'module'      => 'website-base',
            'path'        => 'notification.acl_group.support',
            'value'       => AclGroup::with([])
                ->where('name', 'Support-Notifications')
                ->first()
                ?->getKey(),
            'position'    => 1625,
            'label'       => 'Support Notifications',
            'form_input'  => 'website-base::select_acl_group',
            'description' => 'used to notify support members',
        ],
        [
            'store_id'    => null,
            'module'      => 'website-base',
            'path'        => 'notification.acl_group.admin',
            'value'       => AclGroup::with([])
                ->where('name', 'Admin-Notifications')
                ->first()
                ?->getKey(),
            'position'    => 1620,
            'label'       => 'Admin Notifications',
            'form_input'  => 'website-base::select_acl_group',
            'description' => 'used to notify admin members',
        ],
        [
            'store_id'    => null,
            'module'      => 'website-base',
            'path'        => 'notification.user.sender',
            'value'       => User::with([])
                ->where('name', 'NoReplyNotificationUser')
                ->first()
                ?->getKey(),
            'position'    => 1610,
            'label'       => 'Sender Email',
            'form_input'  => 'website-base::select_puppet_user',
            'description' => 'identity to send notifies from',
        ],
    ],
];

