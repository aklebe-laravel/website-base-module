<?php

namespace Modules\WebsiteBase\app\Models;

use App\Models\User as AppUser;
use Chelout\RelationshipEvents\Concerns\HasBelongsToManyEvents;
use Chelout\RelationshipEvents\Concerns\HasOneEvents;
use Chelout\RelationshipEvents\Traits\HasDispatchableEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Acl\app\Models\AclResource;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;
use Modules\WebsiteBase\app\Models\Base\TraitBaseMedia;
use Modules\WebsiteBase\app\Models\Base\UserTrait;
use Modules\WebsiteBase\app\Services\Notification\Channels\BaseChannel;
use Modules\WebsiteBase\app\Services\SendNotificationService;
use Throwable;

/**
 * @mixin IdeHelperUser
 */
class User extends AppUser
{
    use HasFactory, Notifiable, TraitAttributeAssignment, TraitBaseMedia, UserTrait, HasDispatchableEvents, HasOneEvents, HasBelongsToManyEvents;

    /**
     * Default media type. Should be overwritten by delivered class.
     */
    const string MEDIA_TYPE = MediaItem::MEDIA_TYPE_IMAGE;

    /**
     * Default media object type. Should be overwritten by delivered class.
     */
    const string MEDIA_OBJECT_TYPE = MediaItem::OBJECT_TYPE_USER_AVATAR;

    /**
     *
     */
    const string ATTRIBUTE_MODEL_IDENT = AppUser::class;

    /**
     * guarded [] too slow in user ?!?!
     * This way we redefine fillable
     *
     * @var array
     */
    // protected $guarded = [];
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_visited_at',
        'shared_id',
        'is_enabled',
        'is_deleted',
        'options',
        'order_to_delete_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at'  => 'datetime',
        'last_visited_at'    => 'datetime',
        'order_to_delete_at' => 'datetime',
        'password'           => 'hashed',
        'options'            => 'array',
    ];

    /**
     * @todo: remove this later, use $fillable
     * @var array
     */
    protected $guarded = [];

    /**
     * Multiple bootable model traits is not working
     * https://github.com/laravel/framework/issues/40645
     *
     * parent::construct() will not (or too early) be called without this construct()
     * so all trait boots also were not called.
     *
     * Important for \Modules\Acl\Models\Base\TraitBaseModel::bootTraitBaseModel
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @return BelongsToMany
     */
    public function notificationEvents(): BelongsToMany
    {
        return $this->belongsToMany(NotificationEvent::class);
    }

    /**
     * @return BelongsToMany
     */
    public function avatars(): BelongsToMany
    {
        return $this->images()->userAvatars();
    }

    /**
     * @return HasMany
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    /**
     * @return Builder
     */
    public static function getBuilderFrontendItems(): Builder
    {
        return self::query()->frontendItems();
    }

    /**
     * scope frontendItems()
     *
     * @param  Builder  $query
     *
     * @return Builder
     */
    public function scopeFrontendItems(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('is_enabled', true);
            $q->where('is_deleted', false);
            $q->whereNull('order_to_delete_at');
            $q->whereNotNull('users.name')->where('users.name', '<>', '');
            $q->whereNotNull('email')->where('email', '<>', '');
            $q->whereNotNull('shared_id')->where('shared_id', '<>', '');
        })
            // no puppets/non-humans ...
            ->with(['aclGroups.aclResources'])->whereDoesntHave('aclGroups.aclResources', function ($query) {
                return $query->where('code', '=', AclResource::RES_NON_HUMAN);
            });
    }

    /**
     * Overwrite this to get proper images by specific class!
     * Pivot tables can differ by class objects.
     *
     * @param  string  $contentCode
     * @param  bool    $forceAny  If true: Also select nullable pivots but order by pivots exists
     *
     * @return BelongsToMany
     * @todo: caching?
     *
     */
    public function getContentImages(string $contentCode = '', bool $forceAny = true): BelongsToMany
    {
        $images = $this->images()->userAvatars();
        $this->prepareContentImagesBuilder($images, $contentCode, 'media_item_user', $forceAny);

        return $images;
    }

    /**
     * @param  string       $purpose
     * @param  string|null  $minExpire
     *
     * @return HasMany
     */
    public function getValidTokens(string $purpose = Token::PURPOSE_LOGIN, ?string $minExpire = null): HasMany
    {
        return $this->tokens()->where('purpose', '=', $purpose)->whereNotNull('token')->where(function (Builder $b1) use (
            $minExpire
        ) {

            if ($minExpire === null) {
                $minExpire = Carbon::now()->format(SystemService::dateIsoFormat8601);
            }

            $b1->whereNull('expires_at');
            $b1->orWhere('expires_at', '>', $minExpire);
        });
    }

    /**
     * @param  string|null  $minExpire  minimum expire dateTime you need to be valid
     *
     * @return HasMany
     */
    public function getValidLoginTokens(?string $minExpire = null): HasMany
    {
        return $this->getValidTokens(minExpire: $minExpire);
    }

    /**
     * @param  string|null  $minExpire  minimum expire dateTime you need to be valid
     *
     * @return Token|null
     */
    public function getFirstValidLoginToken(?string $minExpire = null): ?Token
    {
        if ($token = $this->getValidLoginTokens($minExpire)->first()) {
            return $token;
        }

        return null;
    }

    /**
     * Get an existing token or create a new one if none exist.
     *
     * @param  string       $purpose
     * @param  string|null  $minExpire  minimum expiration left
     * @param  string|null  $newExpire  if new token have to be created, use this expiration This value should always be higher than minExpire
     *
     * @return Token|null
     */
    public function getOrCreateWebsiteToken(string $purpose = Token::PURPOSE_LOGIN, ?string $minExpire = null, ?string $newExpire = null): ?Token
    {
        /** @var Token $token */
        if ($token = $this->getValidTokens($purpose, $minExpire)->first()) {
            return $token;
        }

        if (!$newExpire) {
            $newExpire = $minExpire;
        }

        // Create the token
        $token = $this->createWebsiteToken(expire: $newExpire);

        // Reload user
        $this->refresh();

        //
        Log::info(sprintf("New token created for user: %s", $this->name), [
            $this->getKey(),
            __METHOD__,
        ]);

        return $token;
    }

    /**
     * @param  string       $purpose
     * @param  string|null  $expire
     *
     * @return Token
     */
    public function createWebsiteToken(string $purpose = Token::PURPOSE_LOGIN, ?string $expire = null): Token
    {
        return Token::create([
            'user_id'    => $this->getKey(),
            'purpose'    => $purpose,
            'token'      => uniqid('l', true),
            'expires_at' => $expire,
        ]);
    }

    /**
     * After replicated/duplicated/copied
     * but before save()
     *
     * @param  Model  $fromItem
     *
     * @return void
     */
    public function afterReplicated(Model $fromItem): void
    {
        $this->name = __('New').' '.$this->name.' '.uniqid();
        $this->email = uniqid('email_').'@local.test';
        $this->shared_id = uniqid('js_suid_');
    }

    /**
     * Returns relations to replicate.
     *
     * @return array
     */
    public function getReplicateRelations(): array
    {
        return ['mediaItems', 'aclGroups'];
    }

    /**
     * @return HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * @return bool
     */
    public function canLogin(): bool
    {
        return ($this->is_enabled && !$this->is_deleted && !$this->order_to_delete_at);
    }

    /**
     * @return array
     */
    public function getPreferredNotificationChannels(): array
    {
        if ($preferredChannels = $this->getExtraAttribute(self::ATTR_NOTIFICATION_CHANNELS, [])) {
            if (is_array($preferredChannels)) {
                return $preferredChannels;
            }
        }

        return [];
    }

    /**
     * Check if $channel is in user preferred notification channels and the channel can be used.
     *
     * @param  string  $channel  'email'
     *
     * @return bool
     */
    public function canSendNotificationToChannel(string $channel): bool
    {
        $notificationService = app(SendNotificationService::class);
        if ($prefList = $this->getPreferredNotificationChannels()) {
            foreach ($prefList as $preferredChannel) {
                if ($channel !== $preferredChannel) {
                    continue;
                }

                /** @var BaseChannel $c */
                if ($c = $notificationService->getRegisteredChannel($preferredChannel)) {
                    return $c->canNotifyUser($this);
                }
            }
        } else {
            // no preferred channels, so everything is valid,
            // check channel can be used in general
            /** @var BaseChannel $c */
            if ($c = $notificationService->getRegisteredChannel($channel)) {
                return $c->canNotifyUser($this);
            }
        }

        return false;
    }

    /**
     * @return string|null
     */
    public function calculatedNotificationChannel(): ?string
    {
        try {
            // check all user preferred channels first
            $notificationService = app(SendNotificationService::class);
            foreach ($this->getPreferredNotificationChannels() as $preferredChannel) {
                /** @var BaseChannel $c */
                if ($c = $notificationService->getRegisteredChannel($preferredChannel)) {
                    if ($c->canNotifyUser($this)) {
                        return $preferredChannel;
                    }
                }
            }

            // if no user preferred channels, get channel by store config
            if ($configValue = app('website_base_config')->getValue('notification.preferred_channel')) {
                // and check user can use it
                if ($this->canSendNotificationToChannel($configValue)) {
                    return $configValue;
                }
            }
        } catch (Throwable $e) {
            Log::error($e->getMessage(), [__METHOD__]);
        }

        return null;
    }

    /**
     * User could be created by different channels like telegram, and may fake email.
     *
     * @return bool
     */
    public function hasFakeEmail(): bool
    {
        // .test and .local ar official reserved by IETF and are not part of DNS,
        // so it's not needed and NOT WANTED to declare them here
        if (preg_match('#^.+?@(fake\..*|example\..*)$#', $this->email)) {
            return true;
        }

        return false;
    }

    /**
     * True is user has a valid email.
     *
     * @return bool
     */
    public function canUseEmail(): bool
    {
        return !!$this->email && !$this->hasFakeEmail();
    }

    /**
     * 3 steps deletion:
     * 1) "order_to_delete_at" set date deletion was wanted
     * 2) "is_deleted": soft deleted
     * 3) delete model
     *
     * @return array
     */
    public function deleteIn3Steps(): array
    {
        $result = [
            'success' => false,
            'message' => __('Failed to delete User. Please try it later again.'),
        ];
        // Is user already marked as deleted, so hard delete.
        if ($this->is_deleted) {
            if ($this->delete()) {
                $result['message'] = __("user_deleted_step_3", ['name' => $this->name]);
                $result['success'] = true;
            }
        } else {
            // Is user already marked as order_to_delete_at, so soft delete.
            if ($this->order_to_delete_at) {
                // if ($this->updateTimestamps()->update(['is_enabled' => false, 'is_deleted' => true])) {
                if ($this->updateTimestamps()->update(['is_deleted' => true])) {
                    // @todo: anonymize user data
                    $result['message'] = __("user_deleted_step_2", ['name' => $this->name]);
                    $result['success'] = true;
                }

            } else {
                // otherwise, set deletion timer
                // if ($this->updateTimestamps()->update(['is_enabled' => false, 'order_to_delete_at' => time()])) {
                if ($this->updateTimestamps()->update(['order_to_delete_at' => time()])) {
                    $result['message'] = __("user_deleted_step_1", ['name' => $this->name]);
                    $result['success'] = true;
                }
            }
        }

        return $result;
    }

    /**
     * @return $this
     */
    public function makeWithDefaults(array $attributes): static
    {
        $this->setRawAttributes(array_merge([
            'name'              => Str::uuid()->toString(),
            'email'             => Str::uuid()->toString().'@local.test',
            'shared_id'         => uniqid('js_suid_'),
            'email_verified_at' => now(),
            'password'          => '1234567',
            'remember_token'    => Str::random(10),
        ], $attributes));

        return $this;
    }

}
