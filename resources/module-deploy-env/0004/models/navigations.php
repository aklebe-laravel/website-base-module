<?php

use Modules\Acl\app\Models\AclResource;
use Modules\WebsiteBase\app\Models\Navigation as NavigationModel;

return [
    // class of eloquent model
    "model"     => NavigationModel::class,
    // update data if exists and data differ (default false)
    "update"    => true,
    // columns to check if data already exists (AND WHERE)
    "uniques"   => ["code"],
    // relations to update/create
    "relations" => [
        "res" => [
            // relation method which have to exists
            "method"  => "parent",
            // column(s) to find specific #sync_relations items below
            "columns" => "code",
            // delete items if not listed here (default: false)
            "delete"  => false,
        ],
    ],
    // data rows itself
    "data"      => [
        [
            "label"           => "Tokens",
            "code"            => "Admin-Tokens-L3",
            "route"           => "manage-data-all",
            "route_params"    => ["Token"],
            "icon_class"      => "bi bi-qr-code",
            "position"        => 3000,
            "#sync_relations" => [
                "res" => [
                    "Admin-Users-Menu-L2",
                ],
            ],
        ],
        [
            "label"           => "Changelog",
            "code"            => "Changelog-L2",
            "route"           => "changelog",
            "icon_class"      => "bi bi-hand-index",
            "position"        => 5000,
            "#sync_relations" => [
                "res" => [
                    "Content-Overview-Menu-L1",
                ],
            ],
        ],
        [
            "label"           => "Changelog All",
            "code"            => "Changelog-All-L2",
            "acl_resources"   => [AclResource::RES_DEVELOPER],
            "route"           => "changelog",
            "route_params"    => ["all"],
            "icon_class"      => "bi bi-hand-index-fill",
            "position"        => 5000,
            "#sync_relations" => [
                "res" => [
                    "Content-Overview-Menu-L1",
                ],
            ],
        ],
        [
            "label"           => "Changelog",
            "code"            => "Admin-Changelog-L2",
            "acl_resources"   => [AclResource::RES_MANAGE_CONTENT],
            "route"           => "manage-data-all",
            "route_params"    => ["Changelog"],
            "icon_class"      => "bi bi-hand-index",
            "position"        => 18000,
            "#sync_relations" => [
                "res" => [
                    "Admin-Menu-L1",
                ],
            ],
        ],
    ],
];
