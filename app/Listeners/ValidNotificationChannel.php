<?php

namespace Modules\WebsiteBase\app\Listeners;

use Modules\WebsiteBase\app\Events\ValidNotificationChannel as ValidNotificationChannelAliasEvent;
use Modules\WebsiteBase\app\Services\Notification\Channels\Email;
use Modules\WebsiteBase\app\Services\Notification\Channels\Portal;
use Modules\WebsiteBase\app\Services\SendNotificationService;

class ValidNotificationChannel
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @param  ValidNotificationChannelAliasEvent  $event
     *
     * @return void
     */
    public function handle(ValidNotificationChannelAliasEvent $event): void
    {
        $notificationService = app(SendNotificationService::class);

        // add email channel
        $channel = app(Email::class);
        if ($channel->isChannelValid()) {
            $notificationService->registerChannel($channel);
        }

        // add portal channel
        $channel = app(Portal::class);
        if ($channel->isChannelValid()) {
            $notificationService->registerChannel($channel);
        }
    }
}
