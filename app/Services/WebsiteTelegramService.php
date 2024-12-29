<?php

namespace Modules\WebsiteBase\app\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Acl\app\Models\AclGroup;
use Modules\Acl\app\Services\UserService;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\TelegramApi\app\Models\TelegramIdentity;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;
use Modules\WebsiteBase\app\Models\User;

class WebsiteTelegramService extends BaseService
{
    /**
     * @var WebsiteService|null
     */
    protected ?WebsiteService $websiteBaseService = null;

    /**
     * @var UserService|null
     */
    protected ?UserService $userService = null;

    /**
     * @param  WebsiteService  $websiteBaseService
     * @param  UserService  $userService
     */
    public function __construct(WebsiteService $websiteBaseService, UserService $userService)
    {
        parent::__construct();

        $this->websiteBaseService = $websiteBaseService;
        $this->userService = $userService;
    }

    /**
     * @return bool
     */
    public function isTelegramEnabled(): bool
    {
        return $this->websiteBaseService->isTelegramEnabled();
    }

    /**
     * @return ModelAttributeAssignment|Model|null
     */
    public function getUserAttributeWithTelegramId(): ModelAttributeAssignment|Model|null
    {
        return ModelAttributeAssignment::with(['modelAttribute'])->where('model', '=', \App\Models\User::class)
            ->whereHas('modelAttribute', function ($query) {
                return $query->where('code', '=', 'telegram_id');
            })->first();
    }

    /**
     * @return array
     */
    public function findUsersHavingTelegramId(): array
    {
        if ($attr = $this->getUserAttributeWithTelegramId()) {
            if ($builder = DB::table(User::getAttributeTypeTableName($attr->attribute_type))
                ->where('model_attribute_assignment_id', '=', $attr->id)) {
                $attrIds = $builder->pluck('model_id')->toArray(); // model_id is user id
                return $attrIds;
            }
        }
        return [];
    }

    /**
     * @param  string  $telegramId
     * @return int user id or 0
     */
    public function findUserByTelegramId(string $telegramId): int
    {
        if ($attr = $this->getUserAttributeWithTelegramId()) {
            if ($builder = DB::table(User::getAttributeTypeTableName($attr->attribute_type))
                ->where('model_attribute_assignment_id', '=', $attr->id)
                ->where('value', '=', $telegramId)) {
                if ($attrIds = $builder->pluck('model_id')->toArray()) { // model_id is user id

                    // should not more than 1 ...
                    if (count($attrIds) > 1) {
                        $this->error("Telegram id was assigned to more than one users.", [$attrIds, __METHOD__]);
                        return 0;
                    }

                    // @todo: check specific model exists?

                    return reset($attrIds);
                }
            }
        }
        return 0;
    }

    /**
     * Create or update a TelegramIdentity
     * Optionally also calls ensureUserByTelegramIdentity() to create a user
     *
     * @param  array  $telegramIdentityModelData
     * @param  array  $typeFilterForUserModel  in user models only add this types. for humans types use [null]
     * @return array
     */
    public function ensureTelegramUser(array $telegramIdentityModelData, array $typeFilterForUserModel = [
        TelegramIdentity::TYPE_GROUP,
        TelegramIdentity::TYPE_CHANNEL
    ]): array {

        $result = [
            'User'             => null,
            'TelegramIdentity' => null,
        ];

        if ($telegramIdentityFound = $this->ensureTelegramIdentity($telegramIdentityModelData)) {

            // create or update a user represent telegram user, channel or group
            if (in_array($telegramIdentityFound->type, $typeFilterForUserModel)) {
                $user = $this->ensureUserByTelegramIdentity($telegramIdentityFound);
                $result['User'] = $user;
            }

            //
            $result['TelegramIdentity'] = $telegramIdentityFound;
        }

        return $result;
    }

    /**
     * Create or update a TelegramIdentity
     *
     * @param  array  $telegramIdentityModelData
     * @return TelegramIdentity|null
     */
    public function ensureTelegramIdentity(array $telegramIdentityModelData): ?TelegramIdentity
    {
        if ($telegramUserId = data_get($telegramIdentityModelData, 'telegram_id')) {
            if ($telegramIdentityFound = TelegramIdentity::with([])->where('telegram_id', $telegramUserId)->first()) {
                $telegramIdentityFound->update($telegramIdentityModelData);
            } else {
                $telegramIdentityFound = TelegramIdentity::create([
                    'telegram_id' => $telegramUserId,
                    ... $telegramIdentityModelData
                ]);
            }

            //
            return $telegramIdentityFound;
        }

        return null;
    }

    /**
     * Creates or update a user related to a TelegramIdentity
     *
     * @param  TelegramIdentity  $telegramEntity
     * @return User|null
     */
    public function ensureUserByTelegramIdentity(TelegramIdentity $telegramEntity): ?User
    {
        if ($userId = $this->findUserByTelegramId($telegramEntity->telegram_id)) {

            // $this->debug("User already exists: $userId");
            return app(User::class)->with([])->whereId($userId)->first();

        } else {
            // create user assigned/related to this telegram id ...
            /** @var User $user */
            $user = app(User::class);
            /** @var User $user */
            $user = $user->makeWithDefaults([
                'name'     => $this->userService->getNextAvailableUserName($telegramEntity->display_name),
                'email'    => 'fake_'.Str::orderedUuid().'@local.test',
                'password' => Str::random(30),
            ]);

            // add extra attribute: telegram_id
            $user->setExtraAttribute('telegram_id', $telegramEntity->telegram_id);
            $user->setExtraAttribute('use_telegram', true);
            $user->setExtraAttribute('preferred_notification_channel', WebsiteService::NOTIFICATION_CHANNEL_TELEGRAM);

            // save user
            $user->save();

            $groupsAndChannels = [TelegramIdentity::TYPE_GROUP, TelegramIdentity::TYPE_CHANNEL];
            if (($telegramEntity->is_bot) || (in_array($telegramEntity->type, $groupsAndChannels))) {
                //  Call this AFTER saving (to get a valid user_id). Add acl group puppets/non humans ...
                if ($aclGroup = AclGroup::where('name', AclGroup::GROUP_NON_HUMANS)->first()) {
                    $user->aclGroups()->attach($aclGroup);
                }
            }
        }

        return $user;
    }

}