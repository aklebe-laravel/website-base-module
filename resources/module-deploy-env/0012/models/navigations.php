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
    // data rows itself
    'data'      => [
        [
            'label'           => 'Modules',
            'code'            => 'Admin-Modules-L2',
            'acl_resources'   => [AclResource::RES_ADMIN],
            'route'           => 'admin-panel',
            'route_params'    => ["page" => "modules"],
            'icon_class'      => 'bi bi-gear-wide',
            'position'        => 1010,
            '#sync_relations' => [
                'res' => [
                    'Admin-Panel-Menu-L2',
                ],
            ],
        ],
    ],
];
