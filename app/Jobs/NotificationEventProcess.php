<?php

namespace Modules\WebsiteBase\app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\SystemBase\app\Services\SystemService;
use Modules\TelegramApi\app\Services\TelegramService;
use Modules\WebsiteBase\app\Models\NotificationEvent;
use Modules\WebsiteBase\app\Models\User;
use Modules\WebsiteBase\app\Notifications\Emails\NotifyDefault;
use Modules\WebsiteBase\app\Notifications\Emails\NotifyUser;
use Modules\WebsiteBase\app\Services\ConfigService;
use Modules\WebsiteBase\app\Services\SendNotificationService;
use Modules\WebsiteBase\app\Services\WebsiteService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;

class NotificationEventProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var NotificationEvent
     */
    public NotificationEvent $event;

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
     * List of IDs or empty to use event users and acl settings
     *
     * @var array
     */
    public array $customUserList = [];

    /**
     * View parameters added to content_data
     *
     * @var array
     */
    public array $customContentData = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(NotificationEvent $event, array $customUserList = [], array $customContentData = [])
    {
        $this->websiteBaseConfig = app('website_base_config');
        $this->websiteBaseService = app(WebsiteService::class);
        $this->sendNotificationService = app(SendNotificationService::class);
        $this->event = $event->withoutRelations();
        $this->customUserList = $customUserList;
        $this->customContentData = $customContentData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Log::debug(sprintf("Handle event ID: %s, code: %s.", $this->event->id, $this->event->event_code), [__METHOD__]);

        // Check specific method name exists for this code
        $methodName = 'launch_event_'.$this->event->event_code;
        if (!method_exists($this, $methodName)) {
            Log::error('Missing method', [
                $methodName,
                __METHOD__
            ]);

            return;
        }

        // Call it ...
        $this->$methodName();
    }

    /**
     * If callback returns false, all following users will be skipped.
     *
     * @param  callable  $callback
     * @return bool
     */
    protected function runEventUsers(callable $callback): bool
    {
        /** @var Builder $userBuilder */
        if ($this->customUserList) {
            $userBuilder = app(\App\Models\User::class)->with([])->whereIn('id', $this->customUserList);
        } else {
            $userBuilder = $this->event->getEventUsers();
        }

        if ($userBuilder) {
            Log::info(sprintf("Starting progress for %s users in notification event: '%s' (id:%s)",
                $userBuilder->count(), $this->event->name, $this->event->id));

            /** @var User $user */
            foreach ($userBuilder->get() as $user) {
                if (!$callback($user)) {
                    return false;
                }
            }
        } else {
            Log::warning(sprintf("No users found for event %s", $this->event->id), [__METHOD__]);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function launch_event_notify_default(): bool
    {
        return $this->runEventUsers(function (User $user) {

            $this->sendMessage($user, ['email_class' => NotifyDefault::class]);

            return true;
        });
    }

    /**
     * @return bool
     */
    public function launch_event_token_and_notify_default(): bool
    {
        return $this->runEventUsers(function (User $user) {

            // Create a new Login Token with at least 7 days expiration if non exists
            $days = 7;
            $expire = Carbon::now()->addDays($days)->format(SystemService::dateIsoFormat8601);
            $newExpire = Carbon::now()->addDays($days + 1)->format(SystemService::dateIsoFormat8601);
            $user->getOrCreateWebsiteToken(minExpire: $expire, newExpire: $newExpire);

            $this->sendMessage($user, ['email_class' => NotifyUser::class]);

            return true;
        });

    }

    /**
     * @return bool
     */
    public function launch_event_AclGroups_attached_to_User(): bool
    {
        return $this->launch_event_notify_default();
    }

    /**
     * If returning not null, the channel is a valid channel. All stuff was checked.
     *
     * @param  User  $user
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getNotificationChannel(User $user): ?string
    {
        // 1) Check Notification Event forced channel ...
        if ($this->event->force_channel) {
            if ($user->canNotificationChannel($this->event->force_channel)) {
                return $this->event->force_channel;
            }
            // if not available, go out ...
            Log::error(sprintf("User is unable to receive messages from channel this event is forcing. Channel: '%s', User: '%s'",
                $this->event->force_channel, $user->name));
            return null;
        }

        // 2) Check Notification Concerns forced channel ...
        if ($this->event->notificationConcerns) {
            $notificationConcernChannelsFound = [];
            foreach ($this->event->notificationConcerns as $notificationConcern) {
                if ($channel = $notificationConcern->getNotificationChannel()) {
                    $notificationConcernChannelsFound[] = $channel;
                    if ($user->canNotificationChannel($channel)) {
                        return $channel;
                    }
                }
            }

            // found, but user can't ...
            if ($notificationConcernChannelsFound) {
                // if not available, go out ...
                Log::error(sprintf("User '%s' is unable to receive messages from channels: %s", $user->name,
                    implode(',', $notificationConcernChannelsFound)));
                return null;
            }

        }

        // 3) check user preferred setting ...
        if ($channel = $user->calculatedNotificationChannel()) {
            return $channel;
        }

        // Error, no channel ...
        Log::error(sprintf("No channel detected for user '%s'.", $user->name), [__METHOD__]);
        return null;
    }

    /**
     * @param  User  $user
     * @param  array  $options
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TelegramSDKException
     */
    public function sendMessage(User $user, array $options = []): bool
    {
        // we need to force the given channel ...
        if (!($channel = $this->getNotificationChannel($user))) {
            return false;
        }

        return $this->sendNotificationService->sendNotificationToChannel($user, forceChannel: $channel,
            sendToChannelEmail: function () use (
                $user, $channel, $options
            ) {

                /** @var NotifyDefault|NotifyUser $emailClass */
                if (!($emailClass = data_get($options, 'email_class'))) {
                    Log::error(sprintf("Missing email_class to send email to user: %s", $user->name), [__METHOD__]);
                    return false;
                }

                if ($this->websiteBaseConfig->get('notification.simulate', false)) {
                    Log::info("Simulating notification: ", [$channel, $user->name, $user->email]);
                } else {
                    // Send email directly (we are in queue).
                    Mail::send(new $emailClass($user, $channel, $this->event, $this->customContentData));
                }

                return true;

            }, sendToChannelTelegram: function () use ($user, $channel, $options) {

                $telegramService = app(TelegramService::class);
                $d = [
                    'view_path' => data_get($this->event->event_data ?? [], 'view_path', ''),
                    'user'      => $user,
                    'subject'   => $this->event->getSubject($channel),
                    'content'   => $this->event->getContent($channel),
                ];
                if ($message = $telegramService->prepareTelegramMessage(app('system_base')->arrayMergeRecursiveDistinct($this->customContentData,
                    $d))) {
                    $buttonsCode = data_get($this->event->event_data ?? [], 'buttons');
                    $buttons = $buttonsCode ? config('combined-module-telegram-api.button_groups.'.$buttonsCode,
                        []) : [];

                    if ($this->websiteBaseConfig->get('notification.simulate', false)) {
                        Log::info("Simulating notification: ", [$channel, $user->name, $buttonsCode]);
                        return true;
                    } else {
                        $telegramService->apiSendMessage($message, $user->getExtraAttribute('telegram_id'), $buttons);
                        return true;
                    }

                } else {
                    Log::error(sprintf("Empty message for event: %s", $this->event->getKey()), [__METHOD__]);
                    return false;
                }

            });

    }

}

