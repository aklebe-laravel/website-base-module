<?php

namespace Modules\WebsiteBase\app\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class MailSending
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Used to limit emails by provider by using RateLimiter.
     *
     * see also MailSent
     *
     * @param  MessageSending  $event
     *
     * @return bool
     */
    public function handle(MessageSending $event): bool
    {
        if (!app('website_base_config')->getValue('email.enabled', false)) {
            $emails = collect($event->message->getTo())->map(fn($item) => $item->getAddress())->toArray();
            Log::warning("Sending emails disabled. Skip.", [$event->message->getSubject(), $emails, __METHOD__]);

            return false; // false = prevent from Mail::send()
        }

        // max attempts 0 = no limiter
        if ($maxAttempts = (int) app('website_base_config')->getValue('email.rate-limiter.max', 0)) {
            $emails = collect($event->message->getTo())->map(fn($item) => $item->getAddress())->toArray();
            // if no hits remaining, return false
            if (!($remainingCount = RateLimiter::remaining('email-rate-limiter', $maxAttempts))) {
                Log::warning("Sending emails limit reached. Skip.",
                    [$event->message->getSubject(), $emails, __METHOD__]);

                return false; // false = prevent from Mail::send()
            }
        }

        return true; // true is not important, but looks cleaner
    }
}
