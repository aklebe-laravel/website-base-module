<?php

use Modules\Acl\app\Models\AclGroup;
use Modules\WebsiteBase\app\Models\CoreConfig;
use Modules\WebsiteBase\app\Models\User;

return [
    // class of eloquent model
    'model'                => CoreConfig::class,
    // update data if exists and data differ (default false)
    'update'               => true,
    // if update true only: don't update this fields
    'ignore_update_fields' => [
        'value',
    ],
    // columns to check if data already exists (AND WHERE)
    'uniques'              => ['store_id', 'path'],
    // data rows itself
    'data'                 => [
        [
            'store_id'    => null,
            'module'      => 'website-base',
            'path'        => 'notification.acl_group.staff',
            'value'       => AclGroup::with([])->where('name', 'Staff-Notifications')->first()?->getKey(),
            'position'    => 1630,
            'label'       => 'Staff Notifications',
            'form_input'  => 'website-base::select_acl_group',
            'description' => 'used to notify staff members',
        ],
        [
            'store_id'    => null,
            'module'      => 'website-base',
            'path'        => 'notification.acl_group.support',
            'value'       => AclGroup::with([])->where('name', 'Support-Notifications')->first()?->getKey(),
            'position'    => 1625,
            'label'       => 'Support Notifications',
            'form_input'  => 'website-base::select_acl_group',
            'description' => 'used to notify support members',
        ],
        [
            'store_id'    => null,
            'module'      => 'website-base',
            'path'        => 'notification.acl_group.admin',
            'value'       => AclGroup::with([])->where('name', 'Admin-Notifications')->first()?->getKey(),
            'position'    => 1620,
            'label'       => 'Admin Notifications',
            'form_input'  => 'website-base::select_acl_group',
            'description' => 'used to notify admin members',
        ],
        [
            'store_id'    => null,
            'module'      => 'website-base',
            'path'        => 'notification.user.sender',
            'value'       => User::with([])->where('name', 'NoReplyNotificationUser')->first()?->getKey(),
            'position'    => 1610,
            'label'       => 'Sender Email',
            'form_input'  => 'website-base::select_puppet_user',
            'description' => 'identity to send notifies from',
        ],
    ],
];

