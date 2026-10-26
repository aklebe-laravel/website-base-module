<?php

use Modules\Acl\app\Models\AclResource;
use Modules\WebsiteBase\app\Models\Navigation as NavigationModel;

return [
    'version'    => '2.0',
    // class of eloquent model
    'model'      => NavigationModel::class,
    // create data if not exists (default true)
    'can_create' => true,
    // can update data if exists and data differ (default false)
    'can_update' => true,
    // columns to check if data already exists (AND WHERE)
    'uniques'    => ['code'],
    // relations to update/create
    'relations'  => [
        'res' => [
            // relation method which have to exists
            'method'      => 'parent',
            // column(s) to find specific #sync_relations items below
            'columns'     => 'code',
            // delete items if not listed here (default: false)
            'auto_delete' => false,
        ],
    ],
    // data rows itself
    'data'       => [
        [
            'label'         => 'Admin',
            'code'          => 'Admin-Menu-L1',
            'acl_resources' => [AclResource::RES_STAFF],
            'icon_class'    => 'cog',
            'position'      => 920,
        ],
        [
            'label'           => 'Admin Panel',
            'code'            => 'Admin-Panel-Menu-L2',
            'route'           => 'admin-panel',
            'route_params'    => ['page' => 'start'],
            'acl_resources'   => [AclResource::RES_ADMIN],
            'icon_class'      => 'cog',
            'position'        => 1000,
            '#sync_relations' => [
                'res' => [
                    'Admin-Menu-L1',
                ],
            ],
        ],
        [
            'label'           => 'Admin Panel',
            'code'            => 'Admin-Panel-Menu-L3',
            'route'           => 'admin-panel',
            'route_params'    => ['page' => 'start'],
            'icon_class'      => 'cog',
            'position'        => 1000,
            '#sync_relations' => [
                'res' => [
                    'Admin-Panel-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'Status',
            'code'            => 'Admin-Status-Menu-L3',
            'route'           => 'admin-panel',
            'route_params'    => ['page' => 'status'],
            'icon_class'      => 'book-image',
            'position'        => 1100,
            '#sync_relations' => [
                'res' => [
                    'Admin-Panel-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'Settings',
            'code'            => 'Admin-Settings-Menu-L3',
            'route'           => 'admin-panel',
            'route_params'    => ['page' => 'settings'],
            'icon_class'      => 'cog',
            'position'        => 1200,
            '#sync_relations' => [
                'res' => [
                    'Admin-Panel-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'Users',
            'code'            => 'Admin-Users-Menu-L2',
            'route'           => 'manage-data-all',
            'route_params'    => ['User'],
            'acl_resources'   => [AclResource::RES_MANAGE_USERS],
            'icon_class'      => 'users',
            'position'        => 1500,
            '#sync_relations' => [
                'res' => [
                    'Admin-Menu-L1',
                ],
            ],
        ],
        [
            'label'           => 'Users',
            'code'            => 'Admin-Users-L3',
            'route'           => 'manage-data-all',
            'route_params'    => ['User'],
            'icon_class'      => 'user',
            'position'        => 1000,
            '#sync_relations' => [
                'res' => [
                    'Admin-Users-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'User Groups',
            'code'            => 'Admin-User-Groups-L3',
            'route'           => 'manage-data-all',
            'route_params'    => ['AclGroup'],
            'icon_class'      => 'users',
            'position'        => 1100,
            '#sync_relations' => [
                'res' => [
                    'Admin-Users-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'Group Resources',
            'code'            => 'Admin-Group-Resources-L3',
            'route'           => 'manage-data-all',
            'route_params'    => ['AclResource'],
            'icon_class'      => 'check',
            'position'        => 1200,
            '#sync_relations' => [
                'res' => [
                    'Admin-Users-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'CMS',
            'code'            => 'Admin-Cms-Menu-L2',
            'route'           => 'manage-data-all',
            'route_params'    => ['CmsPage'],
            'acl_resources'   => [AclResource::RES_MANAGE_CONTENT],
            'icon_class'      => 'newspaper',
            'position'        => 2000,
            '#sync_relations' => [
                'res' => [
                    'Admin-Menu-L1',
                ],
            ],
        ],
        [
            'label'           => 'Cms Pages',
            'code'            => 'Admin-CMS-Pages-L3',
            'route'           => 'manage-data-all',
            'route_params'    => ['CmsPage'],
            'icon_class'      => 'newspaper',
            'position'        => 1000,
            '#sync_relations' => [
                'res' => [
                    'Admin-Cms-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'Cms Contents',
            'code'            => 'Admin-CMS-Content-L3',
            'route'           => 'manage-data-all',
            'route_params'    => ['CmsContent'],
            'icon_class'      => 'file-text',
            'position'        => 1200,
            '#sync_relations' => [
                'res' => [
                    'Admin-Cms-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'View Templates',
            'code'            => 'Admin-CMS-Views-L3',
            'route'           => 'manage-data-all',
            'route_params'    => ['ViewTemplate'],
            'icon_class'      => 'newspaper',
            'position'        => 1200,
            '#sync_relations' => [
                'res' => [
                    'Admin-Cms-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'Notifications',
            'code'            => 'Admin-Notifications-Menu-L2',
            'route'           => 'manage-data-all',
            'route_params'    => ['NotificationConcern'],
            'icon_class'      => 'mail',
            'position'        => 4000,
            '#sync_relations' => [
                'res' => [
                    'Admin-Menu-L1',
                ],
            ],
        ],
        [
            'label'           => 'Notification Events',
            'code'            => 'Admin-Notification-Events-Menu-L3',
            'route'           => 'manage-data-all',
            'route_params'    => ['NotificationEvent'],
            'acl_resources'   => [AclResource::RES_MANAGE_USERS],
            'icon_class'      => 'bell',
            'position'        => 1000,
            '#sync_relations' => [
                'res' => [
                    'Admin-Notifications-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'Notification Concerns',
            'code'            => 'Admin-Notification-Concerns-L3',
            'route'           => 'manage-data-all',
            'route_params'    => ['NotificationConcern'],
            'icon_class'      => 'mail',
            'position'        => 1100,
            '#sync_relations' => [
                'res' => [
                    'Admin-Notifications-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'Notification Templates',
            'code'            => 'Admin-Notification-Templates-L3',
            'route'           => 'manage-data-all',
            'route_params'    => ['NotificationTemplate'],
            'icon_class'      => 'mail',
            'position'        => 1200,
            '#sync_relations' => [
                'res' => [
                    'Admin-Notifications-Menu-L2',
                ],
            ],
        ],
        [
            'label'           => 'View Templates',
            'code'            => 'Admin-View-Templates-L3',
            'route'           => 'manage-data-all',
            'route_params'    => ['ViewTemplate'],
            'icon_class'      => 'newspaper',
            'position'        => 2000,
            '#sync_relations' => [
                'res' => [
                    'Admin-Notifications-Menu-L2',
                ],
            ],
        ],
        //[
        //    'label'         => 'Content',
        //    'code'          => 'Content-Overview-Menu-L1',
        //    'route'         => 'content-pages-overview',
        //    'acl_resources' => [AclResource::RES_MEMBER],
        //    'icon_class'    => 'file-text',
        //    'position'      => 2000,
        //],
        //[
        //    'label'           => 'Overview',
        //    'code'            => 'Content-Overview-Menu-L2',
        //    'route'           => 'content-pages-overview',
        //    'icon_class'      => 'file-text',
        //    'position'        => 1000,
        //    '#sync_relations' => [
        //        'res' => [
        //            'Content-Overview-Menu-L1',
        //        ],
        //    ],
        //],
        //[
        //    'label'           => 'House Rule',
        //    'code'            => 'Content-house-rule-Menu-L2',
        //    'route'           => 'cms-page',
        //    'route_params'    => ['uri' => 'hausordnung'],
        //    'icon_class'      => 'home',
        //    'position'        => 1100,
        //    '#sync_relations' => [
        //        'res' => [
        //            'Content-Overview-Menu-L1',
        //        ],
        //    ],
        //],
        //[
        //    'label'           => 'FAQ',
        //    'code'            => 'Content-FAQ-Menu-L2',
        //    'route'           => 'cms-page',
        //    'route_params'    => ['uri' => 'faq'],
        //    'icon_class'      => 'circle-question-mark',
        //    'position'        => 1200,
        //    '#sync_relations' => [
        //        'res' => [
        //            'Content-Overview-Menu-L1',
        //        ],
        //    ],
        //],
        //[
        //    'label'           => 'Help',
        //    'code'            => 'Content-Help-Menu-L2',
        //    'route'           => 'cms-page',
        //    'route_params'    => ['uri' => 'faq'],
        //    'icon_class'      => 'circle-question-mark',
        //    'position'        => 3000,
        //    '#sync_relations' => [
        //        'res' => [
        //            'Content-Overview-Menu-L1',
        //        ],
        //    ],
        //],
        //[
        //    'label'           => 'Contact',
        //    'code'            => 'Content-Contact-Menu-L2',
        //    'route'           => 'contact',
        //    'icon_class'      => 'phone',
        //    'position'        => 4000,
        //    '#sync_relations' => [
        //        'res' => [
        //            'Content-Overview-Menu-L1',
        //        ],
        //    ],
        //],
    ],
];
