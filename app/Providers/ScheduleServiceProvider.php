<?php

namespace Modules\WebsiteBase\app\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\SystemBase\app\Providers\Base\ScheduleBaseServiceProvider;
use Modules\WebsiteBase\app\Models\MediaItem as WebsiteBaseMediaItem;
use Modules\WebsiteBase\app\Services\MediaService;
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

        /**
         * Delete unused media files.
         */
        $schedule->call(function () {
            $mediaService = app(MediaService::class);

            // for all valid media types ...
            foreach (WebsiteBaseMediaItem::MEDIA_TYPES as $mediaType => $data) {
                $mediaService->deleteUnusedMediaFiles($mediaType);
            }

            //
            app('system_base')->logExecutionTime('Finished removed unused media files.');
        })->monthlyOn(25, '03:00');

    }

}
