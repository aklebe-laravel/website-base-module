<?php

namespace Modules\WebsiteBase\app\Services\Notification\Channels;

use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\WebsiteBase\app\Models\NotificationConcern as NotificationConcernModel;
use Modules\WebsiteBase\app\Models\User;
use Modules\WebsiteBase\app\Services\ConfigService;
use Modules\WebsiteBase\app\Services\SendNotificationService;
use Modules\WebsiteBase\app\Services\WebsiteService;

abstract class BaseChannel extends BaseService
{
    /**
     * @var string
     */
    const string name = '';

    /**
     * Default priority.
     * Highest priority wins.
     * Note this is just a default value, user can config their own channel priority.
     *
     * @var int
     */
    public int $priority = 1000;

    /**
     * @var ConfigService
     */
    protected ConfigService $websiteBaseConfig;

    /**
     * @var WebsiteService
     */
    protected WebsiteService $websiteBaseService;

    /**
     * @var SendNotificationService
     */
    protected SendNotificationService $sendNotificationService;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->websiteBaseConfig = app('website_base_config');
        $this->websiteBaseService = app(WebsiteService::class);
        $this->sendNotificationService = app(SendNotificationService::class);
    }

    /**
     * @return void
     */
    public function initChannel(): void
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
     * @param  User  $user
     *
     * @return bool
     */
    public function beforeSend(User $user): bool
    {
        if (!$this->isChannelValid()) {
            $this->error("Channel is not valid.", [__METHOD__]);
            return false;
        }

        if (!$this->canNotifyUser($user)) {
            $this->error("User cannot notified by this channel.", [__METHOD__]);
            return false;
        }

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