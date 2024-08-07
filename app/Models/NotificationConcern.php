<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;

/**
 * @mixin IdeHelperNotificationConcern
 */
class NotificationConcern extends Model
{
    use HasFactory;
    use TraitBaseModel;

    const REASON_CODE_CONTACT_REQUEST_MESSAGE = "contact_request_message";
    const REASON_CODE_AUTH_USER_LOGIN_DATA = "remember_user_login_data";
    const REASON_CODE_AUTH_FORGET_PASSWORD = "auth_forget_password";
    const REASON_CODE_AUTH_REGISTER_SUCCESS = "auth_register_success";
    const REASON_CODE_SYSTEM_INFO = "system_info";
    const REASON_CODE_USER_ASSIGNED_TO_TRADER = "market_user_assigned_to_trader";
    const REASON_CODE_USER_ASSIGNED_TO_ACL_GROUP = "market_user_assigned_to_acl_group";

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $table = 'notification_concerns';

    /**
     * tags and meta_data should always cast from json to an array and via versa
     *
     * @var string[]
     */
    protected $casts = [
        'tags'      => 'array',
        'meta_data' => 'array',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'is_valid'
    ];

    /**
     * @return BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * @return BelongsTo
     */
    public function notificationTemplate()
    {
        return $this->belongsTo(NotificationTemplate::class);
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        /** @var NotificationTemplate $notificationTemplate */
        if ($notificationTemplate = $this->notificationTemplate) {
            /** @var ViewTemplate $viewTemplate */
            if ($viewTemplate = $notificationTemplate->viewTemplate) {
                return $viewTemplate->getContent();
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        /** @var NotificationTemplate $notificationTemplate */
        if ($notificationTemplate = $this->notificationTemplate) {
            return $notificationTemplate->subject;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getNotificationChannel(): string
    {
        return $this->notificationTemplate->notification_channel ?? '';
    }

    /**
     * scope scopeValidItems()
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeValidItems(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('is_enabled', true);
            $q->where(function (Builder $b1) {
                $b1->where('store_id', app('website_base_settings')->getStore()->getKey());
            });
            $q->where(function (Builder $b1) {
                $b1->whereDoesntHave('notificationTemplate')
                    ->orWhereHas('notificationTemplate', function (Builder $b2) {
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
        $result = $this->is_enabled && ($this->store->id === app('website_base_settings')
                    ->getStore()
                    ->getKey()) && (!$this->notificationTemplate || $this->notificationTemplate->isValid);
        return Attribute::make(get: fn() => $result);
    }

}
