<?php

namespace Modules\WebsiteBase\app\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\TelegramApi\app\Services\TelegramService;
use Modules\WebsiteBase\app\Models\NotificationConcern as NotificationConcernModel;
use Modules\WebsiteBase\app\Notifications\Emails\NotificationConcern as NotificationConcernEmail;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;

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
     * @var TelegramService
     */
    private TelegramService $telegramService;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->websiteBaseConfig = app('website_base_config');
        $this->websiteBaseService = app(WebsiteService::class);
        $this->telegramService = app(TelegramService::class);
    }

    /**
     * @param  string  $notificationConcernCode
     * @param  mixed  $userOrUserList  List of user ids or user instances, or just one item
     * @param  array  $viewData
     * @param  array  $tags
     * @param  array  $metaData
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TelegramSDKException
     */
    public function sendNotificationConcern(string $notificationConcernCode, mixed $userOrUserList,
        array $viewData = [], array $tags = [], array $metaData = []): bool
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

            if ($channel = $user->calculatedNotificationChannel()) {
                $this->debug(sprintf("Calculated channel: '%s' for user '%s' .", $channel, $user->name));
            }

            if (!($notificationConcern = NotificationConcernModel::with([])
                ->validItems()
                ->where('reason_code', '=', $notificationConcernCode)
                ->whereHas('notificationTemplate', function (Builder $b2) use ($channel) {
                    $b2->where('notification_channel', $channel);
                })
                ->first())) {
                if (!($notificationConcern = NotificationConcernModel::with([])
                    ->validItems()
                    ->where('reason_code', '=', $notificationConcernCode)
                    ->whereHas('notificationTemplate', function (Builder $b2) use ($channel) {
                        $b2->whereNull('notification_channel');
                    })
                    ->first())) {
                    $this->error(__('Missing or invalid notification concern: :reason, channel: :channel',
                        ['reason' => $notificationConcernCode, 'channel' => $channel]));
                    continue;
                }
            }

            $tags = ($notificationConcern->tags ?? []) + $tags;
            $metaData = ($notificationConcern->meta_data ?? []) + $metaData;

            return $this->sendNotificationToChannel($user, forceChannel: $channel, sendToChannelEmail: function () use (
                $user, $notificationConcern, $viewData, $tags, $metaData
            ) {
                // Send email by queue.
                Mail::send(new NotificationConcernEmail($user, $notificationConcern, $viewData, $tags, $metaData));
                $this->info("Sent mail to user: ", [
                    $user->name,
                    $user->email,
                    __METHOD__
                ]);
                return true;
            }, sendToChannelTelegram: function () use ($user, $notificationConcern, $viewData, $tags, $metaData) {
                $d = [
                    // 'view_path' => '...',
                    'user'    => $user,
                    'subject' => $notificationConcern->getSubject(),
                    'content' => $notificationConcern->getContent(),
                ];
                if ($message = $this->telegramService->prepareTelegramMessage(app('system_base')->arrayMergeRecursiveDistinct($viewData,
                    $d))) {
                    $buttons = config('combined-module-telegram-api.button_groups.website_link', []);
                    $this->telegramService->apiSendMessage($message, $user->getExtraAttribute('telegram_id'), $buttons);
                    return true;
                } else {
                    $this->error(sprintf("Empty telegram message for notification concern: %s",
                        $notificationConcern->getKey()), [__METHOD__]);
                    return false;
                }
            });

        }

        return true;
    }

    /**
     * @param  \Modules\WebsiteBase\app\Models\User  $user
     * @param  string|null  $forceChannel  if empty use user preferred channel
     * @param  callable|null  $sendToChannelEmail
     * @param  callable|null  $sendToChannelTelegram
     * @param  callable|null  $sendToChannelPortal
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function sendNotificationToChannel(\Modules\WebsiteBase\app\Models\User $user, ?string $forceChannel = null,
        ?callable $sendToChannelEmail = null, ?callable $sendToChannelTelegram = null,
        ?callable $sendToChannelPortal = null): bool
    {
        $channel = $forceChannel ?: $user->calculatedNotificationChannel();
        switch ($channel) {

            case WebsiteService::NOTIFICATION_CHANNEL_EMAIL:

                if (!$this->websiteBaseService->isEmailEnabled()) {
                    $this->warning(sprintf("Email disabled. Email was not sent to user: %s", $user->name),
                        [__METHOD__]);
                    return false;
                }

                if (!$this->websiteBaseConfig->get('notification.channels.email.enabled', false)) {
                    $this->warning(sprintf("Notification email disabled. Email was not sent to user: %s", $user->name),
                        [__METHOD__]);
                    return false;
                }

                $this->debug(sprintf("Sending email to user: %s", $user->name), [$user->email, __METHOD__]);

                if ($this->websiteBaseConfig->get('notification.simulate', false)) {
                    $this->info("Simulating notification: ", [$channel, $user->name, $user->email]);
                    return true;
                } else {
                    return $sendToChannelEmail();
                }

            case WebsiteService::NOTIFICATION_CHANNEL_TELEGRAM:

                if (!$this->websiteBaseService->isTelegramEnabled()) {
                    return false;
                }

                if (!$this->websiteBaseConfig->get('notification.channels.telegram.enabled', false)) {
                    $this->error(sprintf("Telegram disabled. Telegram message was not sent to user: %s", $user->name),
                        [__METHOD__]);
                    return false;
                }

                $this->debug(sprintf("Sending telegram message to user: %s", $user->name), [__METHOD__]);

                if ($this->websiteBaseConfig->get('notification.simulate', false)) {
                    $this->info("Simulating notification: ", [$channel, $user->name]);
                    return true;
                } else {
                    return $sendToChannelTelegram();
                }

            default:
                $this->warning(sprintf("Channel not supported: %s.", $channel), [__METHOD__]);
                return false;

        }
    }

}