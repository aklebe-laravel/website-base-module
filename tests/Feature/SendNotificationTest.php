<?php

namespace Modules\WebsiteBase\tests\Feature;

use Illuminate\Support\Facades\Log;
use Modules\SystemBase\tests\TestCase;
use Modules\WebsiteBase\app\Models\NotificationEvent;
use Modules\WebsiteBase\app\Models\User;
use Modules\WebsiteBase\app\Services\NotificationEventService;
use Modules\WebsiteBase\app\Services\SendNotificationService;
use Modules\WebsiteBase\app\Services\WebsiteService;
use Modules\WebsiteBase\app\Services\WebsiteTelegramService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SendNotificationTest extends TestCase
{
    protected ?User $telegramUser = null;
    protected ?User $emailUser = null;

    /**
     * @return bool
     */
    public function prepareUserForTelegram(): bool
    {
        if ($this->telegramUser) {
            return true;
        }

        if ($user = app(User::class)->frontendItems()->inRandomOrder()->first()) {
            if ($telegramReceiverID = env('TESTING_TELEGRAM_RECEIVER_ID', '')) {
                Log::debug(sprintf("Preparing user for telegram: %s", $user->name));
                $user->setExtraAttribute('telegram_id', $telegramReceiverID);
                $user->setExtraAttribute('use_telegram', true);
                $user->setExtraAttribute('preferred_notification_channel',
                    WebsiteService::NOTIFICATION_CHANNEL_TELEGRAM);

                // save it
                if ($user->save()) {
                    $this->telegramUser = $user;
                    return true;
                } else {
                    $this->assertTrue(false, "Failed toi save user with telegram id");
                }
            } else {
                $this->assertTrue(false, "Missing env TESTING_TELEGRAM_RECEIVER_ID");
            }
        } else {
            $this->assertTrue(false, "No valid user found");
        }
        return false;
    }

    /**
     * @return bool
     */
    public function prepareUserForEmail(): bool
    {
        if ($this->emailUser) {
            return true;
        }

        /** @var WebsiteTelegramService $websiteTelegramService */
        $websiteTelegramService = app(WebsiteTelegramService::class);
        $telegramUsers = $websiteTelegramService->findUsersHavingTelegramId();
        if ($users = app(User::class)->frontendItems()->whereNotIn('id', $telegramUsers)->inRandomOrder()->first()) {
            foreach ($users->get() as $user) {
                if (!$user->hasFakeEmail()) {
                    $this->emailUser = $user;
                    return true;
                }
            }
        }

        $this->fail("No valid user found.");
        //return false;
    }

    /**
     * 1) Make sure there is a user with valid email
     * 2) Send event concern to this user
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TelegramSDKException
     */
    public function test_send_email()
    {
        // Mail::fake();
        $this->prepareUserForEmail();

        Log::debug(sprintf("Try to send email to user %s:%s ...", $this->emailUser->id, $this->emailUser->name));
        $validatedData = ['user' => $this->emailUser];
        /** @var SendNotificationService $sendNotificationService */
        $sendNotificationService = app(SendNotificationService::class);
        $result = $sendNotificationService->sendNotificationConcern('remember_user_login_data',
            $validatedData['user'], ['contactData' => $validatedData]);

        // Mail::assertSent(NotificationConcernEmail::class); // works together with Mail::fake() only

        $this->assertTrue($result);
    }

    // @todo: repair
    ///**
    // * 1) Make sure there is a user with valid telegram id
    // * 2) Send event concern to this user
    // *
    // *
    // * @return void
    // * @throws ContainerExceptionInterface
    // * @throws NotFoundExceptionInterface
    // * @throws TelegramSDKException
    // */
    //public function test_send_telegram()
    //{
    //    $this->prepareUserForTelegram();
    //
    //    $validatedData = ['user' => $this->telegramUser];
    //    Log::debug(sprintf("Try to send telegram message to user %s:%s ...", $this->telegramUser->id,
    //        $this->telegramUser->name));
    //    /** @var SendNotificationService $sendNotificationService */
    //    $sendNotificationService = app(SendNotificationService::class);
    //    $result = $sendNotificationService->sendNotificationConcern('remember_user_login_data',
    //        $validatedData['user'], ['contactData' => $validatedData]);
    //
    //    $this->assertTrue($result);
    //}

    /**
     * 1) Make sure there is a user with valid email and another user with valid telegram id
     * 2) Send event notification to both users
     *
     * @return void
     */
    public function test_launch_notification_event()
    {
        $this->prepareUserForTelegram();
        $this->prepareUserForEmail();

        if ($notificationEvent = NotificationEvent::with([])->where('name', 'Send User Login Data')->first()) {
            /** @var NotificationEventService $service */
            $service = app(NotificationEventService::class);
            $result = $service->launch($notificationEvent->getKey(), [$this->telegramUser->id, $this->emailUser->id]);

            $this->assertTrue($result);
        } else {
            $this->fail("Notification Event not found.");
        }
    }
}
