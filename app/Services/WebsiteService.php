<?php

namespace Modules\WebsiteBase\app\Services;

use Closure;
use Exception;
use Illuminate\Cache\TaggedCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Acl\app\Models\AclResource;
use Modules\Acl\app\Services\UserService;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\WebsiteBase\app\Http\Middleware\StoreUserValid;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;
use Modules\WebsiteBase\app\Models\Changelog;
use Modules\WebsiteBase\app\Models\ModelAttribute;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;

class WebsiteService extends BaseService
{
    /**
     * Used to clear all extra attribute specific cache
     */
    const string cacheTag = 'website-base.cache.extra_attributes';

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
        // @todo: core config not readable at this point, because even config isn't ready.
        $publicPortal = true;//$this->websiteBaseConfig->getValue('site.public', false);

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

    /**
     *
     * @param  string  $attributeCode
     *
     * @return void
     */
    public function runAllExtraAttributes(string $attributeCode, Closure $callbackModel, ?Closure $callbackAttribute = null): void
    {
        try {

            /** @var ModelAttribute $modelAttribute */
            if (!($modelAttribute = app(ModelAttribute::class)->where('code', '=', $attributeCode)->first())) {
                return;
            }

            $attributeAssignments = ModelAttributeAssignment::with([])->where('model_attribute_id', '=', $modelAttribute->id);
            foreach ($attributeAssignments->get() as $attributeAssignment) {
                /** @var Model|TraitAttributeAssignment $modelBuilder */
                $modelBuilder = app($attributeAssignment->model);
                $tableName = $modelBuilder->getAttributeTypeTableName($attributeAssignment->attribute_type);

                if ($builder = DB::table($tableName)->where('model_attribute_assignment_id', '=', $attributeAssignment->id)) {
                    foreach ($builder->get() as $attributeAssignmentAsType) {
                        // try to find this model
                        /** @var Model|TraitAttributeAssignment $foundModel */
                        foreach ($modelBuilder::with([])->whereId($attributeAssignmentAsType->model_id)->get() as $foundModel) {
                            $this->debug(sprintf("Found attribute assignment class: '%s' table: '%s' model: '%s' value: '%s'",
                                get_class($foundModel), $attributeAssignment->attribute_type, $foundModel->getKey(), $attributeAssignmentAsType->value));

                            // model callback ...
                            $callbackModel($foundModel, $attributeAssignmentAsType);

                        }
                    }
                }

                if ($callbackAttribute !== null) {
                    // attribute callback
                    $callbackAttribute($attributeAssignment);
                }
            }

        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);
        }
    }

    /**
     * Extra Attribute specific cache.
     *
     * @return TaggedCache
     */
    public static function getExtraAttributeCache(): TaggedCache
    {
        return Cache::tags([self::cacheTag]);
    }

    /**
     * @param  int     $nearestSeconds
     * @param  string  $filter
     *
     * @return array
     */
    public function getChangelogGroupNearest(int $nearestSeconds = 300, string $filter = ''): array
    {
        $changeLogCollection = Changelog::with([]);
        // get only entries with public messages
        $changeLogCollection->whereNotNull('messages_public');
        // if staff user, also get staff messages ...
        if (Auth::user()->hasAclResource(AclResource::RES_STAFF)) {
            $changeLogCollection->orWhereNotNull('messages_staff');
        }
        // if 'all' wanted and user is admin or developer, get all messages ...
        if ($filter === 'all' && Auth::user()->hasAclResource([AclResource::RES_STAFF, AclResource::RES_ADMIN])) {
            $changeLogCollection->orWhereNotNull('messages');
        }

        $changeLogCollection->orderByDesc('commit_created_at')
            ->where('commit_created_at', '>', now()->subMonths(12));

        $groupIndex = 0;
        $lastGroupTime = '';
        $resultGroups = [];
        $changeLogCollection->chunk(200, function ($changelogs) use (&$resultGroups, &$groupIndex, &$lastGroupTime, $nearestSeconds) {
            /** @var Changelog $changelog */
            foreach ($changelogs as $changelog) {

                $t = Carbon::createFromFormat('Y-m-d H:i:s', $changelog->commit_created_at);
                if ($t->diffInSeconds($lastGroupTime, false) > $nearestSeconds) {
                    $groupIndex++;
                }
                $lastGroupTime = $changelog->commit_created_at;

                $map = [
                    'messages'        => trim($changelog->messages),
                    'messages_public' => trim($changelog->messages_public),
                    'messages_staff'  => trim($changelog->messages_staff),
                    'paths'           => $changelog->path,
                    'authors'         => $changelog->author,
                    'created'         => $changelog->commit_created_at,
                ];
                foreach ($map as $k => $v) {
                    if (!$v) {
                        continue;
                    }
                    if (!isset($resultGroups[$groupIndex][$k]) || !in_array($v, $resultGroups[$groupIndex][$k])) {
                        $resultGroups[$groupIndex][$k][] = $v;
                    }
                }
            }
        });

        return $resultGroups;
    }

}