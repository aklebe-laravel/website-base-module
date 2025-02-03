<?php

namespace Modules\WebsiteBase\tests\Feature;

use Illuminate\Support\Facades\Log;
use Modules\SystemBase\tests\TestCase;
use Modules\WebsiteBase\app\Models\NotificationEvent;
use Modules\WebsiteBase\app\Models\User;
use Modules\WebsiteBase\app\Services\NotificationEventService;
use Modules\WebsiteBase\app\Services\SendNotificationService;

class SendNotificationTest extends TestCase
{
    protected ?User $emailUser = null;


    /**
     * @return bool
     */
    public function prepareUserForEmail(): bool
    {
        if ($this->emailUser) {
            return true;
        }

        if ($users = app(User::class)->frontendItems()->inRandomOrder()->limit(100)->first()) {
            /** @var User $user */
            foreach ($users->get() as $user) {
                if ($user->canUseEmail()) {
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
            $validatedData['user'],
            ['contactData' => $validatedData]);

        // Mail::assertSent(NotificationConcernEmail::class); // works together with Mail::fake() only

        $this->assertTrue($result);
    }

    /**
     * 1) Make sure there is a user with valid email
     * 2) Send event notification to both users
     *
     * @return void
     */
    public function test_launch_notification_event()
    {
        $this->prepareUserForEmail();

        if ($notificationEvent = NotificationEvent::with([])->where('name', 'Send User Login Data')->first()) {
            /** @var NotificationEventService $service */
            $service = app(NotificationEventService::class);
            $result = $service->launch($notificationEvent->getKey(), [$this->emailUser->id]);

            $this->assertTrue($result);
        } else {
            $this->fail("Notification Event not found.");
        }
    }
}
