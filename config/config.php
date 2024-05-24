<?php

return [
    'name' => 'WebsiteBase',

    /*
    |--------------------------------------------------------------------------
    | Cache settings
    |--------------------------------------------------------------------------
    |
    | caching ttl etc ...
    |
    */
    'cache'                 => [
        'extra_attributes'       => [
            'prefix' => 'ModelExtraAttributes::',
            'ttl'    => env('MODULE_WEBSITEBASE_CACHE_EXTRA_ATTRIBUTE_TTL', 10),
        ],
        'extra_attribute_entity' => [
            'prefix' => 'ModelExtraAttributesEntity::',
            'ttl'    => env('MODULE_WEBSITEBASE_CACHE_EXTRA_ATTRIBUTE_ENTITY_TTL', 10),
        ],
    ],

];
