<?php

namespace Modules\WebsiteBase\app\Models\Base;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Modules\Acl\app\Models\AclGroup;
use Modules\WebsiteBase\app\Models\NotificationEvent;
use Modules\WebsiteBase\app\Services\NotificationEventService;

trait UserTrait
{
    use \Modules\Acl\app\Models\Base\UserTrait;

    /**
     * General boot...() info: Static Setup for this object like events
     * General initialize...() info: executed for every new instance
     *
     * Context: Adding relation events for attached user groups
     *
     * @return void
     */
    public static function bootUserTrait(): void
    {
        static::belongsToManyAttached(function ($relation, $parent, $ids) {
            /** @var User|UserTrait $parent */
            Log::info("'$relation' relations has been attached to user '{$parent->name}'.", [$relation => $ids]);

            // filter out other relations than ->aclGroups()
            if ($relation === 'aclGroups') {
                /** @var NotificationEventService $notificationEventService */
                $notificationEventService = app(NotificationEventService::class);

                // gather events for users to send mail once
                $found = [];
                if ($attachedAclGroups = AclGroup::with([])->whereIn('id', $ids)->get()) {

                    /** @var AclGroup $attachedAclGroup */
                    foreach ($attachedAclGroups as $attachedAclGroup) {

                        // find event for specific acl groups
                        $notifyEventCode = NotificationEvent::EVENT_CODE_ACL_GROUP_ATTACHED_USERS;
                        $eventBuilder = NotificationEvent::validItems()
                            ->where('event_code', $notifyEventCode)
                            ->whereJsonContains('event_data', ['acl_group' => $attachedAclGroup->name]);
                        /** @var NotificationEvent $e */
                        if ($eventBuilder->count()) {
                            foreach ($eventBuilder->get() as $e) {
                                self::gatherNotifyEventForUser($found, $parent, $e, $attachedAclGroup->name);
                            }
                            // next one ...
                            continue;
                        }

                        // else: find event for all acl groups
                        $eventBuilder = NotificationEvent::validItems()
                            ->where('event_code', $notifyEventCode)
                            ->whereJsonContains('event_data', ['acl_group' => '*']);
                        /** @var NotificationEvent $e */
                        if ($eventBuilder->count()) {
                            foreach ($eventBuilder->get() as $e) {
                                self::gatherNotifyEventForUser($found, $parent, $e, $attachedAclGroup->name);
                            }
                        }
                    }

                    // Send what we gathered
                    foreach ($found as $userId => $eventData) {
                        foreach ($eventData as $eventId => $eventData2) {
                            $notificationEventService->launch($eventId, [$userId], ['acl_group_names' => $eventData2]);
                        }
                    }

                }

            }

        });

        static::belongsToManyDetached(function ($relation, $parent, $ids) {
            // Log::info("Roles has been detached to user {$parent->name}.", [$relation, $ids]);
        });
    }

    /**
     * @param  array              $found
     * @param  User               $user
     * @param  NotificationEvent  $e
     * @param  string             $label
     *
     * @return void
     */
    private static function gatherNotifyEventForUser(array &$found, User $user, NotificationEvent $e, string $label): void
    {
        $f = Arr::get($found, $user->getKey(), []);
        $f[$e->getKey()][] = $label;
        Arr::set($found, $user->getKey(), $f);
    }

}
