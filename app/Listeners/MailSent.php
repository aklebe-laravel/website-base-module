<?php

namespace Modules\WebsiteBase\app\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MailSent
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
     * see also MailSending
     *
     * @param  MessageSent  $event
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(MessageSent $event): void
    {
        // max attempts 0 = no limiter
        if ($maxAttempts = (int) app('website_base_config')->get('email.rate-limiter.max', 0)) {
            $emails = collect($event->message->getTo())->map(fn($item) => $item->getAddress())->toArray();
            $secondsToReset = (int) app('website_base_config')->get('email.rate-limiter.reset', 60 * 60 * 24);

            $executed = RateLimiter::attempt('email-rate-limiter', $maxAttempts, function () {
                // executing and incrementing the hit counter ...
            }, $secondsToReset);

            if (!$executed) {
                Log::warning("Email not sent. Limit reached. Skip.",
                    [$event->message->getSubject(), $emails, __METHOD__]);
            }

        }
    }
}
