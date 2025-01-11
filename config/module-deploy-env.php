<?php

use Illuminate\Database\Eloquent\Model;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;
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
        '0001'  => [
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
        '0002'  => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'navigations.php',
                ],
            ],
        ],
        '0003'  => [
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
        '0004'  => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'navigations.php',
                ],
            ],
        ],
        '0005'  => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config.php',
                ],
            ],
        ],
        '0006'  => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config.php',
                ],
            ],
        ],
        '0007'  => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config.php',
                ],
            ],
        ],
        '0008'  => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config.php',
                ],
            ],
        ],
        '0009'  => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'navigations.php',
                ],
            ],
        ],
        '0010'  => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config.php',
                ],
            ],
        ],
        '0011a' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config-from-0001.php',
                ],
            ],
        ],
        '0011b' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config-from-0001.php',
                    'core-config-from-0005.php',
                    'core-config-from-0006.php',
                    'core-config-from-0007.php',
                    'core-config-from-0008.php',
                    'core-config-from-0010.php',
                ],
            ],
        ],
        '0012'  => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'navigations.php',
                ],
            ],
        ],
        '0013'  => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'model-attributes.php',
                    'model-attribute-assignments.php',
                    'acl-resources.php',
                    'acl-groups.php',
                    'users.php',
                    'core-config.php',
                ],
            ],
        ],
        '0014'  => [
            [
                'cmd'     => 'functions',
                'sources' => [
                    'preferred_notification_channel' => function (string $cmd, string $functionKey) {

                        /**
                         * 1) Move all 'preferred_notification_channel' values into 'preferred_notification_channels'
                         * 2) Remove 'preferred_notification_channel' values
                         * 3) Remove 'preferred_notification_channel' assignments
                         */

                        $websiteService = app(WebsiteService::class);
                        $oldAttributeName = 'preferred_notification_channel';

                        //\Illuminate\Support\Facades\Log::debug("In function: $cmd - $functionKey");
                        $websiteService->runAllExtraAttributes('preferred_notification_channel',
                            function (Model $foundModel, $attributeAssignmentAsType) use ($websiteService, $oldAttributeName) {

                            $newAttributeName = 'preferred_notification_channels';
                            /** @var Model|TraitAttributeAssignment $foundModel */
                            if ($attributeAssignmentAsType->value) {
                                if ($newAttribute = $foundModel->getExtraAttribute($newAttributeName)) {
                                    if (!in_array($attributeAssignmentAsType->value, $newAttribute)) {
                                        $newAttribute = \Illuminate\Support\Arr::prepend($newAttribute, $attributeAssignmentAsType->value);
                                    }
                                } else {
                                    $newAttribute = [$attributeAssignmentAsType->value];
                                }

                                // save the new one
                                $foundModel->saveModelAttributeTypeValue($newAttributeName, $newAttribute);

                            }

                            // delete the old one (also if empty)
                            $foundModel->deleteModelAttributeTypeValue($oldAttributeName);

                        }, function(ModelAttributeAssignment $attributeAssignment) use ($oldAttributeName) {
                                // delete $oldAttributeName assignments ...
                                $attributeAssignment->delete();
                        });

                        // clear extra attribute specific cache
                        $websiteService->getExtraAttributeCache()->flush();

                    },
                ],
            ],
        ],
    ],

];
