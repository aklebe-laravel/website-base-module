<?php

use Modules\Acl\app\Models\AclGroup;

return [
    // class of eloquent model
    'model'     => AclGroup::class,
    // update data if exists and data differ (default false)
    'update'    => true,
    // columns to check if data already exists (AND WHERE)
    'uniques'   => ['name'],
    // relations to update/create
    'relations' => [
        'res'  => [
            // relation method which have to exists
            'method'  => 'aclResources',
            // column(s) to find specific #sync_relations items below
            'columns' => 'code',
            // delete items if not listed here (default: false)
            'delete'  => false,
        ],
        'users' => [
            // relation method which have to exists
            'method'  => 'users',
            // column(s) to find specific #sync_relations items below
            'columns' => 'email',
            // delete items if not listed here (default: false)
            'delete'  => false,
        ],
    ],
    // data rows itself
    'data'      => [
        [
            'name'            => 'Staff-Notifications',
            'description'     => 'Staff Notifications',
            '#sync_relations' => [
                'res'   => [
                    'notifications.staff',
                ],
                'users' => [
                    'SiteOwner1@local.test',
                ],
            ],
        ],
        [
            'name'            => 'Support-Notifications',
            'description'     => 'Support Notifications',
            '#sync_relations' => [
                'res'   => [
                    'notifications.support',
                ],
                'users' => [
                    'SiteOwner1@local.test',
                ],
            ],
        ],
        [
            'name'            => 'Admin-Notifications',
            'description'     => 'Admin Notifications',
            '#sync_relations' => [
                'res'   => [
                    'notifications.admin',
                ],
                'users' => [
                    'SiteOwner1@local.test',
                ],
            ],
        ],
    ],
];
