<?php

use App\Models\User;

return [
    'version'    => '2.0',
    // class of eloquent model
    'model'      => User::class,
    // create data if not exists (default true)
    'can_create' => true,
    // can update data if exists and data differ (default false)
    'can_update' => true,
    // if update true only: don't update this fields
    //'ignore_update_fields' => [
    //    'password'
    //],
    // columns to check if data already exists (AND WHERE)
    'uniques'    => ['email'],
    // relations to update/create
    'relations'  => [
        'res' => [
            // relation method which have to exists
            'method'      => 'aclGroups',
            // column(s) to find specific #sync_relations items below
            'columns'     => 'name',
            // delete items if not listed here (default: false)
            'auto_delete' => false,
        ],
    ],
    // data rows itself
    'data'       => [
        [
            'name'            => 'Admin',
            'email'           => 'admin@local.test',
            'password'        => '1234567',
            'shared_id'       => uniqid('js_suid_'),
            '#sync_relations' => [
                'res' => [
                    'Admins',
                    'Site Owners',
                ],
            ],
        ],
    ],
];
