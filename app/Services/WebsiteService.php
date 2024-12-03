<?php

namespace Modules\WebsiteBase\app\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Acl\app\Services\UserService;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\WebsiteBase\app\Http\Middleware\StoreUserValid;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class WebsiteService extends BaseService
{
    const NOTIFICATION_CHANNEL_NOTHING = 'nothing';
    const NOTIFICATION_CHANNEL_EMAIL = 'email';
    const NOTIFICATION_CHANNEL_SMS = 'sms';
    const NOTIFICATION_CHANNEL_TELEGRAM = 'telegram';
    const NOTIFICATION_CHANNEL_WHATSAPP = 'whatsapp';
    const NOTIFICATION_CHANNELS = [
        self::NOTIFICATION_CHANNEL_EMAIL,
        // self::NOTIFICATION_CHANNEL_SMS,
        self::NOTIFICATION_CHANNEL_TELEGRAM,
        // self::NOTIFICATION_CHANNEL_WHATSAPP,
    ];

    /**
     * Depends on config setting and user ACL.
     *
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isStoreVisibleForUser(): bool
    {
        if ($publicPortal = app('website_base_config')->get('site.public', false)) {
            return true;
        }

        /** @var UserService $userService */
        $userService = app(UserService::class);
        if ($userService->hasUserResource(Auth::user(), 'trader')) {
            return true;
        }

        return false;
    }

    /**
     * @return array|string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDefaultMiddleware(): array
    {
        $publicPortal = app('website_base_config')->get('site.public', false);

        //
        $forceAuthMiddleware = [
            'auth',
            StoreUserValid::class,
        ];

        //$forceAuthMiddleware = ['auth', 'verified'];
        $defaultMiddleware = $publicPortal ? [] : $forceAuthMiddleware;

        return $defaultMiddleware;
    }

    /**
     * @return bool
     */
    public function isEmailEnabled(): bool
    {
        /** @var Config $websiteBaseConfig */
        $websiteBaseConfig = app('website_base_config');

        return !!$websiteBaseConfig->get('channels.email.enabled', false);
    }

    /**
     * @return bool
     */
    public function isTelegramEnabled(): bool
    {
        /** @var Config $websiteBaseConfig */
        $websiteBaseConfig = app('website_base_config');

        return !!$websiteBaseConfig->get('channels.telegram.enabled', false);
    }

    /**
     * Removes all extra attribute assignments in type tables where
     * object no longer exists.
     *
     * @return void
     */
    public function cleanupExtraAttributes(): void
    {
        $deleted = 0;
        $attributeAssignments = ModelAttributeAssignment::with([]);
        foreach ($attributeAssignments->get() as $attributeAssignment) {
            /** @var TraitAttributeAssignment $model */
            $model = app($attributeAssignment->model);
            $tableName = $model->getAttributeTypeTableName($attributeAssignment->attribute_type);

            if ($builder = DB::table($tableName)->where('model_attribute_assignment_id', '=', $attributeAssignment->id)) {
                $toDelete = [];
                foreach ($builder->get() as $attributeAssignmentAsType) {
                    // try to find this model
                    if (!$model::with([])->whereId($attributeAssignmentAsType->model_id)->count()) {
                        // not found, attr assignment can be deleted
                        // $this->debug(sprintf("Remove attribute assignment class: '%s' type: '%s' id: '%s'",
                        //     $attributeAssignment->model, $attributeAssignment->attribute_type,
                        //     $attributeAssignmentAsType->model_id));
                        $toDelete[] = $attributeAssignmentAsType->id;
                    }
                }
                // $this->debug(sprintf("To delete '%s': ", $attributeAssignment->description), $toDelete);
                foreach ($toDelete as $id) {
                    // do not move the builder in a var
                    if (DB::table($tableName)->delete($id)) {
                        $deleted++;
                    }
                }
            }
        }

        if ($deleted) {
            $this->info(sprintf("%s unused attribute assignments deleted.", $deleted));
        }
    }

}