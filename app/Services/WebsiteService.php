<?php

namespace Modules\WebsiteBase\app\Services;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Acl\app\Services\UserService;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\WebsiteBase\app\Http\Middleware\StoreUserValid;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;

class WebsiteService extends BaseService
{
    /**
     * @var ConfigService
     */
    protected mixed $websiteBaseConfig;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->websiteBaseConfig = app('website_base_config');
    }

    /**
     * Depends on config setting and user ACL.
     *
     * @return bool
     */
    public function isStoreVisibleForUser(): bool
    {
        if ($this->websiteBaseConfig->getValue('site.public', false)) {
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
     */
    public function getDefaultMiddleware(): array
    {
        $publicPortal = $this->websiteBaseConfig->getValue('site.public', false);

        //
        $forceAuthMiddleware = [
            'auth',
            StoreUserValid::class,
        ];

        //$forceAuthMiddleware = ['auth', 'verified'];
        return $publicPortal ? [] : $forceAuthMiddleware;
    }

    /**
     * Removes all extra attribute assignments in type tables where
     * object no longer exists.
     *
     * @return void
     */
    public function cleanupExtraAttributes(): void
    {
        try {

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

        } catch (Exception $e) {
            $this->error("Failed to cleanup extra attributes!");
            $this->error($e->getMessage(), [__METHOD__]);
        }
    }

}