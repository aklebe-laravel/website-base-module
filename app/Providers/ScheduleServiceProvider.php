<?php

namespace Modules\WebsiteBase\app\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\SystemBase\app\Providers\Base\ScheduleBaseServiceProvider;
use Modules\WebsiteBase\app\Services\WebsiteService;

class ScheduleServiceProvider extends ScheduleBaseServiceProvider
{
    protected function bootEnabledSchedule(Schedule $schedule): void
    {
        /**
         * Update all telegram bots and save their groups,channels and users.
         * Additionally, adjust the env var TELEGRAM_BOTS_UPDATE_CACHE_TTL to minimize traffic.
         */
        $schedule->call(function () {

            /** @var WebsiteService $websiteService */
            $websiteService = app(WebsiteService::class);
            $websiteService->cleanupExtraAttributes();

        })->everyTwoHours();

    }

}
