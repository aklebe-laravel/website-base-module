<?php

return [
    'name' => 'WebsiteBase',

    'module_website_public' => env('MODULE_WEBSITE_PUBLIC'),

    // Where to go after a login when no page was requested before (route name or path).
    // Another module can set its own start page, e.g. config(['website-base.login_redirect' => 'my.route']).
    'login_redirect'        => env('MODULE_WEBSITE_BASE_LOGIN_REDIRECT', '/'),

    // Time zone of this installation for what people see and type in (e.g. 'Europe/Berlin'), empty = app.timezone.
    // Internally everything stays UTC (app.timezone). A user can choose an own one in the profile (extra attribute
    // 'timezone'). See Setting::installationTimezone(), displayTimezone(), toDisplayTime().
    'timezone'              => env('APP_DISPLAY_TIMEZONE', ''),

    /*
    |--------------------------------------------------------------------------
    | Progressive web app (PWA)
    |--------------------------------------------------------------------------
    |
    | Makes the website installable (Android, iOS, desktop) and shows an offline page when the server can't be
    | reached. See README "Progressive web app". Empty colors fall back to the theme (theme.json "tokens":
    | "pwa.theme_color", "pwa.background_color") and then to PwaService::DEFAULT_COLOR.
    |
    */
    'pwa'                   => [
        // Default: on in production only. A service worker that stays registered in dev is confusing.
        'enabled'          => (bool) env('MODULE_WEBSITE_BASE_PWA_ENABLED', env('APP_ENV') === 'production'),
        // empty = app.name
        'name'             => env('MODULE_WEBSITE_BASE_PWA_NAME', ''),
        // empty = name
        'short_name'       => env('MODULE_WEBSITE_BASE_PWA_SHORT_NAME', ''),
        // empty = theme token, then PwaService::DEFAULT_COLOR
        'theme_color'      => env('MODULE_WEBSITE_BASE_PWA_THEME_COLOR', ''),
        'background_color' => env('MODULE_WEBSITE_BASE_PWA_BACKGROUND_COLOR', ''),
        // Seconds a page load may take until the offline page is shown (VPN off: connects can hang a long time)
        'navigate_timeout' => (int) env('MODULE_WEBSITE_BASE_PWA_NAVIGATE_TIMEOUT', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache settings
    |--------------------------------------------------------------------------
    |
    | caching ttl etc ...
    |
    */
    'cache'                 => [
        'core_config'            => [
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
