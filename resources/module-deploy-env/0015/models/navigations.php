<?php

use Modules\Acl\app\Models\AclResource;
use Modules\WebsiteBase\app\Models\Navigation as NavigationModel;

return [
    // class of eloquent model
    'model'     => NavigationModel::class,
    // update data if exists and data differ (default false)
    'update'    => true,
    // columns to check if data already exists (AND WHERE)
    'uniques'   => ['code'],
    // relations to update/create
    'relations' => [
        'res' => [
            // relation method which have to exists
            'method'  => 'parent',
            // column(s) to find specific #sync_relations items below
            'columns' => 'code',
            // delete items if not listed here (default: false)
            'delete'  => false,
        ],
    ],
    // to delete
    'delete'    => [
        'Home-Menu-L1',
    ],
    // data rows itself
    'data'      => [
        [
            'label'           => 'Logs',
            'code'            => 'Admin-Logs-L2',
            'acl_resources'   => [AclResource::RES_DEVELOPER, AclResource::RES_ADMIN],
            'route'           => 'log-viewer.index', // name of route 'developer-log-view-R2D2-xyz', see config/log-viewer.php
            'icon_class'      => 'bi bi-list-columns',
            'position'        => 19000,
            '#sync_relations' => [
                'res' => [
                    'Admin-Menu-L1',
                ],
            ],
        ],
    ],
];
