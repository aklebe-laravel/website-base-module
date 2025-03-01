<?php

use App\Models\User;

return [
    // class of eloquent model
    'model'     => User::class,
    // update data if exists and data differ (default false)
    'update'    => true,
    // if update true only: don't update this fields
    //'ignore_update_fields' => [
    //    'password'
    //],
    // columns to check if data already exists (AND WHERE)
    'uniques'   => ['email'],
    // relations to update/create
    'relations' => [
        'res' => [
            // relation method which have to exists
            'method'  => 'aclGroups',
            // column(s) to find specific #sync_relations items below
            'columns' => 'name',
            // delete items if not listed here (default: false)
            'delete'  => false,
        ],
    ],
    // data rows itself
    'data'      => [
        [
            'name'            => 'Trader1',
            'email'           => 'trader1@local.test',
            'password'        => '1234567',
            'shared_id'       => uniqid('js_suid_'),
            '#sync_relations' => [
                'res' => [
                    'Traders',
                ],
            ],
        ],
        [
            'name'            => 'Trader2',
            'email'           => 'trader2@local.test',
            'password'        => '1234567',
            'shared_id'       => uniqid('js_suid_'),
            '#sync_relations' => [
                'res' => [
                    'Traders',
                ],
            ],
        ],
        [
            'name'            => 'Trader3',
            'email'           => 'trader3@local.test',
            'password'        => '1234567',
            'shared_id'       => uniqid('js_suid_'),
            '#sync_relations' => [
                'res' => [
                    'Traders',
                ],
            ],
        ],
        [
            'name'            => 'Trader4',
            'email'           => 'trader4@local.test',
            'password'        => '1234567',
            'shared_id'       => uniqid('js_suid_'),
            '#sync_relations' => [
                'res' => [
                    'Traders',
                ],
            ],
        ],
    ],
];
