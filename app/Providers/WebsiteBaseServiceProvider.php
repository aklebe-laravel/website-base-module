<?php

declare(strict_types = 1);

namespace Modules\WebsiteBase\app\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Modules\Acl\app\Services\UserService;
use Modules\SystemBase\app\Providers\Base\ModuleBaseServiceProvider;
use Modules\SystemBase\app\Services\ModuleService;
use Modules\WebsiteBase\app\Console\AttributeCleanups;
use Modules\WebsiteBase\app\Console\ChangeLog;
use Modules\WebsiteBase\app\Console\CoreConfig;
use Modules\WebsiteBase\app\Console\User;
use Modules\WebsiteBase\app\Models\MediaItem;
use Modules\WebsiteBase\app\Models\User as ModuleUser;
use App\Models\User as AppUser;
use Modules\WebsiteBase\app\Services\CmsService;
use Modules\WebsiteBase\app\Services\CoreConfigService;
use Modules\WebsiteBase\app\Services\CreateChangeLogService;
use Modules\WebsiteBase\app\Services\WebsiteBaseFormService;
use Modules\WebsiteBase\app\Services\MediaService;
use Modules\WebsiteBase\app\Services\Notification\Channels\Email;
use Modules\WebsiteBase\app\Services\Notification\Channels\Portal;
use Modules\WebsiteBase\app\Services\NotificationEventService;
use Modules\WebsiteBase\app\Services\SendNotificationService;
use Modules\WebsiteBase\app\Services\Setting;
use Modules\WebsiteBase\app\Services\WebsiteService;
use Modules\WebsiteBase\app\View\Components\Dropdown\Dropdown;
use Modules\WebsiteBase\app\View\Components\Menu\Main;
use Modules\WebsiteBase\app\View\Components\Popup\Popup;
use Illuminate\Database\Eloquent\Relations\Relation;

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

        $this->app->singleton(Setting::class);
        $this->app->singleton('website_base_settings', Setting::class);
        $this->app->singleton(MediaService::class);
        $this->app->singleton('website_base_media', MediaService::class);
        $this->app->singleton(CoreConfigService::class);
        $this->app->singleton('website_base_config', CoreConfigService::class);
        $this->app->singleton(WebsiteService::class);
        $this->app->singleton('website_base_service', WebsiteService::class);
        $this->app->singleton(SendNotificationService::class);
        $this->app->singleton(NotificationEventService::class);
        $this->app->singleton(CreateChangeLogService::class);
        $this->app->singleton(CmsService::class);
        $this->app->singleton(UserService::class);
        $this->app->singleton(WebsiteBaseFormService::class);
        $this->app->singleton(Email::class);
        $this->app->singleton(Portal::class);

        $this->app->bind('media', MediaItem::class);

        // ---------------------------------------------------
        // User Bind:
        // Bind to the concrete base class
        // ONLY BIND AppUser! Do not every derived class!
        // Just use app(\App\Models\User) everywhere, nothing else!
        // ---------------------------------------------------
        $this->app->bind(AppUser::class, ModuleUser::class);
        // Shorthand
        $this->app->bind('user', ModuleUser::class);

        // Bind to the core framework interface for injection
        //$this->app->bind(Authenticatable::class, ModuleUser::class);

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

        // 1. Override the auth driver configuration dynamically
        Config::set('auth.providers.users.database.model', ModuleUser::class);

        // 2. Enforce morph map to prevent data corruption in polymorphic tables
        Relation::morphMap([
            'users' => ModuleUser::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                AttributeCleanups::class,
                CoreConfig::class,
                ChangeLog::class,
                User::class,
            ]);
        }

        //
        Relation::enforceMorphMap([
            'media_item' => MediaItem::class,
        ]);

        Blade::component('website-base::menu-main', Main::class);
        Blade::component('website-base::popup', Popup::class);

        //// Views mit Namespace laden
        //$this->loadViewsFrom(
        //    __DIR__ . '/../resources/views',
        //    'website-base'
        //);

        // Blade Components explizit registrieren
        $this->loadViewComponentsAs('website-base', [
            'dropdown' => Dropdown::class,
        ]);

        // Optional: Einzelne Alias-Registrierung als Fallback
        Blade::component('website-base::dropdown', Dropdown::class);
    }


}
