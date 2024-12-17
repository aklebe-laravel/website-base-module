<?php

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
                        'function' => function ($code) {
                            // countries should not present
                            return !\Modules\WebsiteBase\app\Models\Country::with([])->count();
                        },
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
        '0005' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config.php',
                ],
            ],
        ],
        '0006' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config.php',
                ],
            ],
        ],
        '0007' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config.php',
                ],
            ],
        ],
        '0008' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config.php',
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
        '0010' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config.php',
                ],
            ],
        ],
    ],

];
