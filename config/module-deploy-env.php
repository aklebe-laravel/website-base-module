<?php

use Modules\WebsiteBase\app\Services\WebsiteService;

return [

    /*
    |--------------------------------------------------------------------------
    | Config of deployments. Can be updated by adding new identifiers.
    | See module DeployEnv README.md
    |--------------------------------------------------------------------------
    */

    'deployments' => [
        // Identifier to remember this deployment was already done.
        '0001' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'stores.php', // stores before users
                    'model-attributes.php',
                    'model-attribute-assignments.php',
                    'users.php',
                    'core-config.php',
                ],
            ],
            [
                'cmd'        => 'raw_sql',
                'conditions' => [
                    [
                        //'function' => [WebsiteService::class, 'hasNoCountries'],
                        'function' => ['website_base_service', 'hasNoCountries'],
                    ],
                ],
                'sources'    => [
                    'countries.sql',
                    'currencies.sql',
                    'regions-de.sql',
                ],
            ],
        ],
        '0002' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'navigations.php',
                ],
            ],
        ],
        '0003' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'view-templates.php',
                    'notification-templates.php',
                    'notification-concerns.php',
                    'notification-events.php',
                ],
            ],
        ],
        '0004' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'navigations.php',
                ],
            ],
        ],
        '0009' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'navigations.php',
                ],
            ],
        ],
        '0012' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'navigations.php',
                ],
            ],
        ],
        '0013' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'model-attributes.php',
                    'model-attribute-assignments.php',
                    'acl-resources.php',
                    'acl-groups.php',
                    'users.php',
                ],
            ],
        ],
        '0015' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'navigations.php',
                ],
            ],
        ],
        '0016' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'model-attribute-assignments.php',
                ],
            ],
        ],
        '0017' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'users.php',
                ],
            ],
        ],
        '0018' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    // user setting 'timezone' (time zone of the times shown to the user)
                    'model-attributes.php',
                    'model-attribute-assignments.php',
                ],
            ],
        ],
    ],

];
