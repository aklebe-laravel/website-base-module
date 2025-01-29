<?php

namespace Modules\WebsiteBase\app\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Mailables\Address;
use Modules\Acl\app\Models\AclGroup;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\WebsiteBase\app\Events\ValidNotificationChannel;
use Modules\WebsiteBase\app\Models\NotificationConcern;
use Modules\WebsiteBase\app\Models\NotificationConcern as NotificationConcernModel;
use Modules\WebsiteBase\app\Models\User as WebsiteBaseUser;
use Modules\WebsiteBase\app\Services\Notification\Channels\BaseChannel;

class SendNotificationService extends BaseService
{
    /**
     * @var ConfigService
     */
    protected ConfigService $websiteBaseConfig;

    /**
     * @var WebsiteService
     */
    protected WebsiteService $websiteBaseService;

    /**
     * @var array
     */
    private array $registeredChannels = [];

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->websiteBaseConfig = app('website_base_config');
        $this->websiteBaseService = app(WebsiteService::class);
    }

    /**
     * @param  string  $notificationConcernCode
     * @param  mixed   $userOrUserList  List of user ids, or user instances, or just one of them
     * @param  array   $viewData
     * @param  array   $tags
     * @param  array   $metaData
     *
     * @return bool
     */
    public function sendNotificationConcern(string $notificationConcernCode, WebsiteBaseUser|array|int $userOrUserList, array $viewData = [], array $tags = [], array $metaData = []): bool
    {
        if (!$userOrUserList) {
            return false;
        }

        if (!is_array($userOrUserList)) {
            $userOrUserList = [$userOrUserList];
        }

        foreach ($userOrUserList as $userItem) {

            $user = $userItem;
            if (is_int($user) || is_string($user)) {
                $user = app(User::class)->where('id', $user)->first();
            }

            if (!($user instanceof User)) {
                $this->error("Invalid User!", [__METHOD__, $userItem]);
                continue;
            }

            if (!($channel = $user->calculatedNotificationChannel())) {
                $this->error("Invalid Notification Channel!", [$channel, $user->name, __METHOD__]);
                return false;
            }

            $this->debug(sprintf("Calculated channel: '%s' for user '%s' .", $channel, $user->name));

            if (!($notificationConcern = NotificationConcernModel::with([])
                ->validItems()
                ->where('reason_code', '=', $notificationConcernCode)
                ->whereHas('notificationTemplate', function (Builder $b2) use ($channel) {
                    $b2->where('notification_channel', $channel);
                })
                ->first())
            ) {
                if (!($notificationConcern = NotificationConcernModel::with([])
                    ->validItems()
                    ->where('reason_code', '=', $notificationConcernCode)
                    ->whereHas('notificationTemplate', function (Builder $b2) use ($channel) {
                        $b2->whereNull('notification_channel');
                    })
                    ->first())
                ) {
                    $this->error(__('Missing or invalid notification concern: :reason, channel: :channel',
                        ['reason' => $notificationConcernCode, 'channel' => $channel]));
                    continue;
                }
            }

            $tags = ($notificationConcern->tags ?? []) + $tags;
            $metaData = ($notificationConcern->meta_data ?? []) + $metaData;

            if ($c = $this->getRegisteredChannel($channel)) {
                if (!$c->sendNotificationConcern($user, $notificationConcern, $viewData, $tags, $metaData)) {
                    return false;
                }
            }

        }

        return true;
    }

    /**
     * @param  BaseChannel  $channel
     *
     * @return void
     */
    public function registerChannel(BaseChannel $channel): void
    {
        $this->registeredChannels[] = $channel;
    }

    /**
     * @return array
     */
    public function getRegisteredChannels(): array
    {
        if (!$this->registeredChannels) {
            // gather channels once
            ValidNotificationChannel::dispatch();
        }

        return $this->registeredChannels;
    }

    /**
     * @return array
     */
    public function getRegisteredChannelNames(): array
    {
        $result = [];
        if ($this->getRegisteredChannels()) {
            foreach ($this->registeredChannels as $channel) {
                $result[] = $channel::name;
            }
        }

        return $result;
    }

    /**
     * @param  string  $channelName
     *
     * @return BaseChannel|null
     */
    public function getRegisteredChannel(string $channelName): ?BaseChannel
    {
        if ($this->getRegisteredChannels()) {
            foreach ($this->registeredChannels as $channel) {
                if ($channel::name === $channelName) {
                    return $channel;
                };
            }
        }

        return null;
    }

    /**
     * @param  string  $channelName
     *
     * @return bool
     */
    public function hasRegisteredChannelName(string $channelName): bool
    {
        return $this->getRegisteredChannel($channelName) !== null;
    }

    /**
     * @param  NotificationConcernModel  $notificationConcern
     *
     * @return Address|null
     */
    public function getEmailAddressByEmailConcernOrDefaultSender(NotificationConcern $notificationConcern): ?Address
    {
        //
        $fromName = $notificationConcern->sender;
        $fromEmail = $notificationConcern->sender;
        if (!$fromEmail) {
            if ($fromUser = $this->getSender()) {
                $fromName = $fromUser->name;
                $fromEmail = $fromUser->email;
            }
        }

        if (!$fromEmail) {
            return null;
        }

        return new Address($fromEmail, $fromName);
    }

    /**
     * @return WebsiteBaseUser|null
     */
    public function getSender(): ?WebsiteBaseUser
    {
        if ($senderId = $this->websiteBaseConfig->getValue('notification.user.sender')) {
            return app(WebsiteBaseUser::class)->with([])->whereId($senderId)->first();
        }

        return null;
    }

    /**
     * @return Address|null
     */
    public function getSenderEmailAddress(): ?Address
    {
        if ($sender = $this->getSender()) {
            return new Address($sender->email, $sender->name);
        }

        return null;
    }

    /**
     * @return AclGroup|null
     */
    public function getStaffSupportUserGroup(): ?AclGroup
    {
        if ($aclGroupId = $this->websiteBaseConfig->getValue('notification.acl_group.support')) {
            return app(AclGroup::class)->with([])->whereId($aclGroupId)->first();
        }

        return null;
    }

    /**
     * @return iterable
     */
    public function getStaffSupportUsers(): iterable
    {
        if ($aclGroup = $this->getStaffSupportUserGroup()) {
            return $aclGroup->users;
        }

        return [];
    }

}