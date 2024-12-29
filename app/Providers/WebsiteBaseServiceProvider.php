<?php

namespace Modules\WebsiteBase\app\Providers;

use Modules\SystemBase\app\Providers\Base\ModuleBaseServiceProvider;
use Modules\SystemBase\app\Services\ModuleService;
use Modules\WebsiteBase\app\Console\AttributeCleanups;
use Modules\WebsiteBase\app\Models\MediaItem;
use Modules\WebsiteBase\app\Models\User;
use Modules\WebsiteBase\app\Services\ConfigService;
use Modules\WebsiteBase\app\Services\MediaService;
use Modules\WebsiteBase\app\Services\Setting;
use Modules\WebsiteBase\app\Services\WebsiteService;

class WebsiteBaseServiceProvider extends ModuleBaseServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected string $moduleName = 'WebsiteBase';

    /**
     * @var string $moduleNameLower
     */
    protected string $moduleNameLower = 'website-base';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        // add aliases before parent::register() ...
        $modelList = ModuleService::getAllClassesInPath($this->moduleName, 'model');
        $this->modelAliases = array_merge($this->modelAliases, $modelList);

        parent::register();

        $this->app->singleton('website_base_settings', Setting::class);
        $this->app->singleton('website_base_media', MediaService::class);
        $this->app->singleton('website_base_config', ConfigService::class);
        $this->app->singleton(WebsiteService::class, WebsiteService::class);
        $this->app->bind('media', MediaItem::class);

        // Important to get Modules\WebsiteBase\Models\User when accessing app(\App\Models\User::class)
        $this->app->bind(\App\Models\User::class, User::class);
        // user shorthand
        $this->app->bind('user', User::class);

        // This is also important to overwrite the user successfully!
        \Illuminate\Support\Facades\Config::set('auth.providers.users.model', User::class);

        $this->app->register(ScheduleServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(BroadcastServiceProvider::class);
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        $this->commands([
            AttributeCleanups::class,
        ]);
    }


}
