<?php

namespace Modules\WebsiteBase\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Acl\app\Models\AclResource;
use Modules\Acl\app\Services\UserService as UserServiceAlias;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;

/**
 * @mixin IdeHelperNotificationEvent
 */
class NotificationEvent extends Model
{
    use HasFactory;
    use TraitBaseModel;

    const EVENT_TRIGGER_MANUALLY = 'manually';
    const EVENT_TRIGGER_AUTO = 'auto';

    const VALID_EVENT_TRIGGERS = [
        self::EVENT_TRIGGER_MANUALLY,
        self::EVENT_TRIGGER_AUTO,
    ];

    /**
     * Run through all event users and send the given notification concern or just the content
     */
    const EVENT_CODE_NOTIFY_DEFAULT = 'notify_default';

    /**
     * This works like default, but also creates a token
     */
    const EVENT_CODE_NOTIFY_USERS = 'token_and_notify_default';

    /**
     *
     */
    const EVENT_CODE_ACL_GROUP_ATTACHED_USERS = 'AclGroups_attached_to_User';

    /**
     * Determine the method in NotificationEventProcess like 'launch_event_xxx'
     */
    const VALID_EVENT_CODES = [
        self::EVENT_CODE_NOTIFY_DEFAULT,
        self::EVENT_CODE_NOTIFY_USERS,
        self::EVENT_CODE_ACL_GROUP_ATTACHED_USERS,
    ];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string[]
     */
    protected $casts = [
        'event_data'   => 'array',
        'content_data' => 'array',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'is_valid'
    ];

    /**
     * scope scopeValidItems()
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeValidItems(Builder $query)
    {
        return $query->where(function (Builder $q) {
            $q->where('is_enabled', true);
            $q->where(function (Builder $b1) {
                $b1->whereNull('expires_at');
                $b1->orWhere('expires_at', '>', date(SystemService::dateIsoFormat8601));
            });
            $q->where(function (Builder $b1) {
                $b1->whereDoesntHave('notificationConcerns')
                    ->orWhereHas('notificationConcerns', function (Builder $b2) {
                        $b2->validItems();
                    });
            });

        });
    }

    /**
     * @return Attribute
     */
    protected function isValid(): Attribute
    {
        $result = $this->is_enabled && (!$this->expires_at || $this->expires_at > date(SystemService::dateIsoFormat8601)) && ((!$this->notificationConcerns->count()) || $this->notificationConcerns()
                    ->validItems()
                    ->count());
        return Attribute::make(get: fn() => $result);
    }

    /**
     * @return BelongsToMany
     */
    public function notificationConcerns(): BelongsToMany
    {
        return $this->belongsToMany(NotificationConcern::class)->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function aclResources(): BelongsToMany
    {
        return $this->belongsToMany(AclResource::class)->withTimestamps();
    }

    /**
     * @param  string  $channel
     * @return NotificationConcern|null
     */
    public function getNotificationConcernByChannel(string $channel = ''): ?NotificationConcern
    {
        // Check first specific channel if given
        if ($channel) {
            foreach ($this->notificationConcerns as $notificationConcern) {
                if ($notificationConcern->getNotificationChannel() === $channel) {
                    return $notificationConcern;
                }
            }
        }

        // If both not set its also a valid concern
        foreach ($this->notificationConcerns as $notificationConcern) {
            if (!$notificationConcern->getNotificationChannel() && !$channel) {
                return $notificationConcern;
            }
        }

        return null;
    }

    /**
     * Prefer content from notification concern. If not exists use content itself.
     *
     * @param  string  $channel
     * @return string|null
     */
    public function getContent(string $channel = ''): ?string
    {
        if ($this->notificationConcerns && $this->notificationConcerns->count()) {
            if ($notificationConcern = $this->getNotificationConcernByChannel($channel)) {
                return $notificationConcern->getContent();
            }
        }

        return $this->content;
    }

    /**
     * Prefer subject from notification concern. If not exists use subject itself.
     *
     * @param  string  $channel
     * @return string|null
     */
    public function getSubject(string $channel = ''): mixed
    {
        if ($this->notificationConcerns && $this->notificationConcerns->count()) {
            if ($notificationConcern = $this->getNotificationConcernByChannel($channel)) {
                return $notificationConcern->getSubject();
            }
        }

        return $this->subject;
    }

    /**
     * Get event users by acl resources or configured users.
     *
     * @return Builder|null
     */
    public function getEventUsers(): ?Builder
    {
        $res = $this->aclResources->pluck('id')->toArray();
        $userIds = $this->users->pluck('id')->toArray();
        if ($res || $userIds) {
            return UserServiceAlias::getUserBuilderByAclResourcesOrUserIds($res, $userIds, false);
        }

        return null;
    }

}
