<?php

namespace Modules\WebsiteBase\app\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Acl\app\Http\Controllers\Controller;
use Modules\Acl\app\Models\AclResource;
use Modules\Acl\app\Services\UserService;
use Modules\WebsiteBase\app\Models\NotificationEvent;
use Modules\WebsiteBase\app\Notifications\Emails\NotifyDefault;

class PreviewNotifyEventController extends Controller
{
    public function show(Request $request, $id, $userId = null)
    {
        /** @var UserService $userService */
        $userService = app(UserService::class);

        if ($userService->hasUserResource(Auth::user(), AclResource::RES_MANAGE_DESIGN)) {

            /** @var NotificationEvent $notifyEvent */
            $notifyEvent = NotificationEvent::with([])->whereId($id)->first();

            if ($userId) {
                $user = app(User::class)->with([])->whereId($userId)->first();
            } else {
                if ($users = $notifyEvent->getEventUsers()) {
                    $user = $users->inRandomOrder()->first();
                }
            }

            // @todo: decide by code linke in \Modules\WebsiteBase\Jobs\NotificationEventProcess
            return new NotifyDefault($user ?? Auth::user(), '', $notifyEvent);

        }

        return null;
    }

}
