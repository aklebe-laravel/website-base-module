<?php

namespace Modules\WebsiteBase\app\Listeners;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Acl\app\Services\UserService;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Models\User;

class UserOnline
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
     * Handle the event.
     *
     * @param  object  $event
     *
     * @return void
     */
    public function handle($event)
    {
        // Update user last visited ...
        /** @var User $user */
        if ($user = Auth::user()) {
            if (!$user->canLogin()) {
                Log::info("Forced User Logout automatically. ",
                    [$user->getKey(), $user->name, date(SystemService::dateIsoFormat8601), __METHOD__]);
                /** @var UserService $userService */
                $userService = app(UserService::class);
                $userService->destroyUserSession();
                return;
            }

            // avoid update too much (every api/async call)
            if ((!$user->last_visited_at) || ($user->last_visited_at->diffInRealSeconds('NOW') > 4)) {
                // quick first level update only, no extra attributes, no events
                $user->updateWithoutEvents(['last_visited_at' => time()]);
                // $user->refresh();
            }
        }
    }
}
