<?php

namespace Modules\WebsiteBase\app\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\SystemBase\app\Services\ModuleService;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected string $moduleNamespace = 'Modules\WebsiteBase\app\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map(): void
    {
        $this->mapApiRoutes();

        $this->mapPwaRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the PWA routes (manifest, service worker, offline page).
     *
     * Without the "web" group on purpose: browsers request these files often (the service worker on every
     * update check), they need no session, cookies or CSRF, and must work for guests.
     *
     * @return void
     */
    protected function mapPwaRoutes(): void
    {
        Route::namespace($this->moduleNamespace)->group(ModuleService::getPath('pwa.php', 'WebsiteBase', 'routes'));
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->namespace($this->moduleNamespace)->group(ModuleService::getPath('web.php',
            'WebsiteBase', 'routes'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(ModuleService::getPath('api.php', 'WebsiteBase', 'routes'));
    }
}
