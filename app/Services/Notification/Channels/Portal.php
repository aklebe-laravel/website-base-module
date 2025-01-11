<?php

namespace Modules\WebsiteBase\app\Services\Notification\Channels;

use Modules\WebsiteBase\app\Models\NotificationConcern as NotificationConcernModel;
use Modules\WebsiteBase\app\Models\User;

class Portal extends BaseChannel
{
    /**
     * @var string
     */
    const string name = 'portal';

    /**
     * @return void
     */
    public function initChannel() : void
    {

    }

    /**
     * @return bool
     */
    public function isChannelValid(): bool
    {
        return true;
    }

    /**
     * @param  User  $user
     *
     * @return bool
     */
    public function canNotifyUser(User $user): bool
    {
        return true;
    }

    /**
     * @param  User   $user
     * @param  array  $options
     *
     * @return bool
     */
    public function sendMessage(User $user, array $options = []): bool
    {
        return false;
    }

    /**
     * @param  User                      $user
     * @param  NotificationConcernModel  $concern
     * @param  array                     $viewData
     * @param  array                     $tags
     * @param  array                     $metaData
     *
     * @return bool
     */
    public function sendNotificationConcern(User $user, NotificationConcernModel $concern, array $viewData = [], array $tags = [], array $metaData = []): bool
    {
        return false;
    }

}