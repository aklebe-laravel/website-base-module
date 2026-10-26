<?php

namespace Modules\WebsiteBase\app\Http\Controllers;

use Illuminate\Http\Request;
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

        if ($userService->currentUser()
            ->hasAclResource(AclResource::RES_MANAGE_DESIGN)
        ) {

            /** @var NotificationEvent $notifyEvent */
            $notifyEvent = NotificationEvent::with([])
                ->whereId($id)
                ->first();

            if ($userId) {
                $user = app('user')
                    ->with([])
                    ->whereId($userId)
                    ->first();
            } else {
                if ($users = $notifyEvent->getEventUsers()) {
                    $user = $users->inRandomOrder()
                        ->first();
                }
            }

            // @todo: decide by code linke in \Modules\WebsiteBase\Jobs\NotificationEventProcess
            return new NotifyDefault($user ?? app('website_base_settings')->currentUser(), '', $notifyEvent);

        }

        return null;
    }

}
