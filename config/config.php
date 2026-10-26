<?php

return [
    'name' => 'WebsiteBase',

    'module_website_public' => env('MODULE_WEBSITE_PUBLIC'),

    // Where to go after a login when no page was requested before (route name or path).
    // Another module can set its own start page, e.g. config(['website-base.login_redirect' => 'my.route']).
    'login_redirect'        => env('MODULE_WEBSITE_BASE_LOGIN_REDIRECT', '/'),

    /*
    |--------------------------------------------------------------------------
    | Cache settings
    |--------------------------------------------------------------------------
    |
    | caching ttl etc ...
    |
    */
    'cache'                 => [
        'core_config'       => [
            'prefix' => 'core_config_cache_',
            'ttl'    => env('MODULE_SYSTEM_BASE_CACHE_CORE_CONFIG_TTL', 1),
        ],
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
