<?php

namespace Modules\WebsiteBase\app\Services;

use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\WebsiteBase\app\Jobs\NotificationEventProcess;
use Modules\WebsiteBase\app\Models\NotificationEvent;

class NotificationEventService extends BaseService
{
    /**
     *
     * @param  int  $eventId
     * @param  array  $customUserList
     * @param  array  $customContentData
     * @return bool
     */
    public function launch(int $eventId, array $customUserList = [], array $customContentData = []): bool
    {
        /** @var NotificationEvent $event */
        // do no add validItems() here, this should be filtered outside
        if ($event = NotificationEvent::with([])->whereId($eventId)->first()) {

            NotificationEventProcess::dispatch($event, $customUserList, $customContentData);

            return true;
        }

        $this->error(__('Event not found or disabled/invalid.'), [__METHOD__]);

        return false;
    }

}