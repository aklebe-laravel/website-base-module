<?php

use Modules\Acl\app\Models\AclResource;

return [
    'version'    => '2.0',
    // class of eloquent model
    'model'      => AclResource::class,
    // create data if not exists (default true)
    'can_create' => true,
    // can update data if exists and data differ (default false)
    'can_update' => true,
    // columns to check if data already exists (AND WHERE)
    'uniques'    => ['code'],
    // data rows itself
    'data'       => [
        [
            'code'        => 'notifications.staff',
            'name'        => 'Staff Notifications',
            'description' => 'Staff Notifications',
        ],
        [
            'code'        => 'notifications.support',
            'name'        => 'Support Notifications',
            'description' => 'Support Notifications',
        ],
        [
            'code'        => 'notifications.admin',
            'name'        => 'Admin Notifications',
            'description' => 'Admin Notifications',
        ],
    ],
];

