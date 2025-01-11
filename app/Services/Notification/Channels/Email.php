<?php

namespace Modules\WebsiteBase\app\Services\Notification\Channels;

use Illuminate\Support\Facades\Mail;
use Modules\WebsiteBase\app\Jobs\NotificationEventProcess;
use Modules\WebsiteBase\app\Models\NotificationConcern as NotificationConcernModel;
use Modules\WebsiteBase\app\Models\User;
use Modules\WebsiteBase\app\Notifications\Emails\NotificationConcern as NotificationConcernEmail;
use Modules\WebsiteBase\app\Notifications\Emails\NotifyDefault;
use Modules\WebsiteBase\app\Notifications\Emails\NotifyUser;

class Email extends BaseChannel
{
    /**
     * @var string
     */
    const string name = 'email';

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
        if (!$this->websiteBaseConfig->getValue('notification.channels.email.enabled', false)) {
            $this->warning("Notification email disabled.", [__METHOD__]);

            return false;
        }
        return true;
    }

    /**
     * @param  User  $user
     *
     * @return bool
     */
    public function canNotifyUser(User $user): bool
    {
        //return $user->canLogin() && !!$user->email && !$user->hasFakeEmail();
        return !!$user->email && !$user->hasFakeEmail();
    }

    /**
     * @param  User  $user
     *
     * @return bool
     */
    public function beforeSend(User $user): bool
    {
        if (!parent::beforeSend($user)) {
            return false;
        }

        if ($this->websiteBaseConfig->getValue('notification.simulate', false)) {
            $this->info("Simulating notification: ", [self::name, $user->name, $user->email]);

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
        if (!$this->beforeSend($user)) {
            return false;
        }

        /** @var NotificationEventProcess $notificationEventProcess */
        $notificationEventProcess = $options['notification_event_process'];

        $this->debug(sprintf("Sending email to user: %s", $user->name), [$user->email, __METHOD__]);

        /** @var NotifyDefault|NotifyUser $emailClass */
        if (!($emailClass = data_get($options, 'email_class'))) {
            $this->error(sprintf("Missing email_class to send email to user: %s", $user->name), [__METHOD__]);

            return false;
        }

        if ($this->websiteBaseConfig->getValue('notification.simulate', false)) {
            $this->info("Simulating notification: ", [self::name, $user->name, $user->email]);
        } else {
            // Send email directly (we are in queue).
            Mail::send(new $emailClass($user, self::name, $notificationEventProcess->event, $notificationEventProcess->customContentData));
        }

        return true;

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
        // Send email by queue.
        Mail::send(new NotificationConcernEmail($user, $concern, $viewData, $tags, $metaData));
        $this->info("Sent mail to user: ", [
            $user->name,
            $user->email,
            __METHOD__
        ]);
        return true;
    }

}