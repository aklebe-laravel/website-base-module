<?php

namespace Modules\WebsiteBase\app\Models\Base;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Modules\Acl\app\Models\AclGroup;
use Modules\Acl\app\Models\AclResource;
use Modules\WebsiteBase\app\Models\NotificationEvent;
use Modules\WebsiteBase\app\Services\NotificationEventService;

trait UserTrait
{
    /**
     * General boot...() info: To use bootSomethingLikeThis() or bootUserTrait()
     * there must be at least declared a __construct() in the implemented class
     * which calls parent::__construct()
     *
     * Context: Adding relation events for attached user groups
     *
     * @return void
     */
    public static function bootUserTrait()
    {
        static::belongsToManyAttached(function ($relation, $parent, $ids) {
            /** @var User|UserTrait $parent */
            Log::info("{$relation} relations has been attached to user {$parent->name}.", [$ids]);

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

        });

        static::belongsToManyDetached(function ($relation, $parent, $ids) {
            // Log::info("Roles has been detached to user {$parent->name}.", [$relation, $ids]);
        });
    }

    /**
     * @param  array  $found
     * @param  User  $user
     * @param  NotificationEvent  $e
     * @param  string  $label
     * @return void
     */
    private static function gatherNotifyEventForUser(array &$found, User $user, NotificationEvent $e,
        string $label): void
    {
        $f = Arr::get($found, $user->getKey(), []);
        $f[$e->getKey()][] = $label;
        Arr::set($found, $user->getKey(), $f);
    }

    /**
     * Override this instead of declare $appends with all parent declarations.
     *
     * @return array|string[]
     */
    protected function getArrayableAppends()
    {
        return parent::getArrayableAppends() + [
                'acl_resources',
            ];
    }

    /**
     * @return BelongsToMany
     */
    public function aclGroups(): BelongsToMany
    {
        return $this->belongsToMany(AclGroup::class)->withTimestamps()->withPivot(['created_at', 'updated_at']);
    }

    /**
     * Check user has any of the acl resources.
     *
     * @param  mixed  $codes
     * @param  array  $orCodes
     * @return bool
     * @todo: caching
     */
    public function hasAclResource(mixed $codes, array $orCodes = [AclResource::RES_ADMIN]): bool
    {
        if (!is_array($codes)) {
            $codes = [$codes];
        }
        $resources = $this->aclResources;
        if ($orCodes && ($resources->whereIn('code', $orCodes)->count() > 0)) {
            return true;
        }
        return ($resources->whereIn('code', $codes)->count() > 0);
    }

    /**
     * @param  array  $aclResources
     * @return Builder
     * @todo: Try make scope instead of create new instance
     *
     */
    public static function withAclResources(array $aclResources): Builder
    {
        $builder = app(static::class)->query();
        $builder->distinct();
        $builder->select('users.*');
        $builder->join('acl_resources', function ($join) use ($aclResources) {
            $join->whereIn('code', $aclResources);
        });
        $builder->join('acl_group_acl_resource', 'acl_group_acl_resource.acl_resource_id', '=', 'acl_resources.id');
        $builder->join('acl_groups', 'acl_groups.id', '=', 'acl_group_acl_resource.acl_group_id');
        //            $builder->join('acl_group_user', 'acl_group_user.acl_group_id', '=', 'acl_group_acl_resource.acl_group_id');
        $builder->join('acl_group_user', function ($join) {
            $join->on('acl_group_user.acl_group_id', '=', 'acl_group_acl_resource.acl_group_id')
                ->on("acl_group_user.user_id", "=", "users.id");
        });
        //        $builder->groupBy('users.id');

        return $builder;
    }

    /**
     * @param  array  $aclResources
     * @return Builder
     * @todo: Try make scope instead of create new instance
     *
     */
    public static function withNoAclResources(array $aclResources): Builder
    {
        $result = app(static::class)->withAclResources($aclResources)->pluck('id');
        $builder = app(static::class)->query();
        $builder->distinct();
        $builder->whereNotIn('id', $result);
        return $builder;
    }

    /**
     * Attribute
     *
     * @return Attribute
     */
    protected function aclResources(): Attribute
    {
        return Attribute::make(get: function () {

            return AclResource::with([])->whereHas('aclGroups.users', function ($query) {
                return $query->where('id', '=', $this->getKey());
            })->get();

        });
    }

}
