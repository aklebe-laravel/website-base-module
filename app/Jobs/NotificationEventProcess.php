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
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Models\NotificationEvent;
use Modules\WebsiteBase\app\Models\User;
use Modules\WebsiteBase\app\Notifications\Emails\NotifyDefault;
use Modules\WebsiteBase\app\Notifications\Emails\NotifyUser;
use Modules\WebsiteBase\app\Services\CoreConfigService;
use Modules\WebsiteBase\app\Services\SendNotificationService;
use Modules\WebsiteBase\app\Services\WebsiteService;

class NotificationEventProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var NotificationEvent
     */
    public NotificationEvent $event;

    /**
     * @var CoreConfigService
     */
    protected CoreConfigService $websiteBaseConfig;

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
    public function handle(): void
    {
        // Log::debug(sprintf("Handle event ID: %s, code: %s.", $this->event->id, $this->event->event_code), [__METHOD__]);

        // Check specific method name exists for this code
        $methodName = 'launch_event_'.$this->event->event_code;
        if (!method_exists($this, $methodName)) {
            Log::error('Missing method', [
                $methodName,
                __METHOD__,
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
     *
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
     *
     * @return string|null
     */
    protected function getNotificationChannel(User $user): ?string
    {
        // 1) Check notification event forced channel ...
        if ($this->event->force_channel) {
            if ($user->canSendNotificationToChannel($this->event->force_channel)) {
                return $this->event->force_channel;
            }
            // if not available, go out ...
            Log::error(sprintf("User is unable to receive messages from channel this event is forcing. Channel: '%s', User: '%s'",
                $this->event->force_channel, $user->name));

            return null;
        }

        // 2) Check notification concerns channel, but respect user channels order ...
        if ($this->event->notificationConcerns) {
            $notificationConcernChannelsFound = [];
            // prio loop by user preferred channels..
            foreach ($user->getPreferredNotificationChannels() as $preferredChannel) {
                // check all concerns for this event
                foreach ($this->event->notificationConcerns as $notificationConcern) {
                    if ($channel = $notificationConcern->getNotificationChannel()) {
                        if ($channel === $preferredChannel) {
                            $notificationConcernChannelsFound[] = $channel;
                            if ($user->canSendNotificationToChannel($channel)) {
                                return $channel;
                            }
                        }
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
     * @param  User                       $user
     * @param  array{email_class:string}  $options
     *
     * @return bool
     */
    public function sendMessage(User $user, array $options = []): bool
    {
        // we need to force the given channel ...
        if (!($channel = $this->getNotificationChannel($user))) {
            return false;
        }

        $options['notification_event_process'] = $this;
        if ($registeredChannel = $this->sendNotificationService->getRegisteredChannel($channel)) {
            return $registeredChannel->sendMessage($user, $options);
        }

        return false;
    }

}

