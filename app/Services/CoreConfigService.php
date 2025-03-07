<?php

namespace Modules\WebsiteBase\app\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\SystemBase\app\Services\CacheService;
use Modules\WebsiteBase\app\Models\CoreConfig;

/**
 * Config value will read by optional sore_id and optional module.
 * store_id null is a fallback for all stores and defines the valid values.
 * module null means available for all modules.
 */
class CoreConfigService extends BaseService
{
    const int CURRENT_STORE_MARKER = -2;

    /**
     * If true, all $storeId parameters assigned to null will become the default store id.
     *
     * @var bool
     */
    protected bool $useDefaultStore = true;

    /**
     * Config tree of all values (and all modules) by store id.
     * The stores not null are already inherited by default store (null) if they not exists.
     *
     * $configByStore[null] is also possible to get the default tree.
     *
     * @var array
     */
    private array $configContainer = [];

    /**
     * @param  int|null  $storeId
     *
     * @return string
     */
    private function getCacheKey(?int $storeId): string
    {
        return sprintf("%s%s", config('website-base.cache.core_config.prefix'), $storeId);
    }

    /**
     * @param  int|null  $storeId
     *
     * @return void
     */
    private function resetCache(?int $storeId): void
    {
        Cache::forget($this->getCacheKey($storeId));
        Log::info(sprintf("Reset cache key for store: '%s'", $storeId));
    }

    /**
     * Build tree with soft cache
     *
     * @param  int|null  $storeId
     *
     * @return array
     */
    public function getConfigTree(?int $storeId = null): array
    {
        if (!($cacheKey = $this->getCacheKey($storeId))) {
            // someone is calling core config before env config is ready ...

            //throw new Exception(__('website-base cache not found'));

            Log::error(sprintf("Cache key not found for store: '%s'", $storeId));

            return [];
        }


        return $this->configContainer[$storeId] = app(CacheService::class)->rememberUseConfig($cacheKey, 'website-base.cache.core_config.ttl', function () use ($storeId) {

            $r['stores'] = [];
            $r['store_modules'] = [];

            // inherit from null store ...
            if ($storeId !== null) {
                // make sure null store is also filled up ...
                $r2 = $this->getConfigTree();

                $r['stores'] = app('system_base')->arrayMergeRecursiveDistinct($r['stores'], $r2['stores']);
                $r['store_modules'] = app('system_base')->arrayMergeRecursiveDistinct($r['store_modules'], $r2['store_modules']);
            }

            try {

                // get all entries of all modules by this store ...
                $builder = CoreConfig::with([])->where('store_id', $storeId)->orderBy('module')->orderBy('position')->orderBy('path');

                /** @var CoreConfig $config */
                foreach ($builder->get() as $config) {
                    Arr::set($r['stores'], $config->path, $config->value);
                    $r['store_modules'][$config->module] ??= [];
                    Arr::set($r['store_modules'][$config->module], $config->path, $config->value);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(), [__METHOD__]);
            }

            return $r;
        });
    }

    /**
     * @param  int|null     $storeId
     * @param  string|null  $module
     *
     * @return array
     */
    public function getConfigModuleTree(?int $storeId = null, ?string $module = null): array
    {
        // ensure data is set by calling getConfigTree($storeId) ... (for storeId and also null store)
        return $this->getConfigTree($storeId)['store_modules'][$module] ?? [];
    }

    /**
     * Get a config value by path.
     *
     * If module
     *
     * @param  string       $path
     * @param  mixed        $default  returned default value if no config was found
     * @param  int|null     $storeId  if not set try get from current store, if not found there, try get from null (default) store
     * @param  string|null  $module   if not set, searching the whole config
     *
     * @return mixed
     */
    public function getValue(string $path = '', mixed $default = null, ?int $storeId = self::CURRENT_STORE_MARKER, ?string $module = null): mixed
    {
        if ($storeId === self::CURRENT_STORE_MARKER) {
            $storeId = app('website_base_settings')->getStoreId();
        }

        // ensure data is set (for storeId and also null store)
        $this->getConfigTree($storeId);

        // if no path, return the whole tree ...
        if (!$path) {
            return $this->configContainer[$storeId]['stores'];
        }

        // if not exist the specific store, use the default store (null) ...
        if (($storeId !== null) && (!Arr::has($this->configContainer[$storeId]['stores'], $path))) {
            return $this->getValue($path, $default, null, $module);
        }

        // try to get by module at first
        if ($module !== null) { // && Arr::has($this->configContainer[$storeId]['store_modules'], $modulePath)) {
            $modulePath = $module.'.'.$path;

            return Arr::get($this->configContainer[$storeId]['store_modules'], $modulePath, $default);
        }

        // use store value ...
        return Arr::get($this->configContainer[$storeId]['stores'], $path, $default);
    }

    ///**
    // * Get a prepared and preloaded config
    // *
    // *
    // * @param  string    $path
    // * @param  mixed     $value
    // * @param  int|null  $storeId
    // * @param  bool      $persist
    // *
    // * @return void
    // */
    //public function set(string $path, mixed $value, ?int $storeId = self::CURRENT_STORE_MARKER, bool $persist = false): void
    //{
    //    if ($storeId === self::CURRENT_STORE_MARKER) {
    //        $storeId = app('website_base_settings')->getStore()->getKey();
    //    }
    //
    //    if (!isset($this->configContainer[$storeId]['stores'])) {
    //        $this->buildConfigTree($storeId);
    //    }
    //
    //    $path = str_replace('/', '.', $path);
    //    Arr::set($this->configContainer[$storeId]['stores'], $path, $value);
    //
    //    if ($persist) {
    //        /** @var CoreConfig $config */
    //        if ($config = CoreConfig::wherePath($path)->where('store_id', $storeId)->firstOrNew()) {
    //            $config->store_id = $storeId;
    //            $config->path = $path;
    //            $config->value = $value;
    //            $config->save();
    //        }
    //    }
    //}

    /**
     * @param  string       $path
     * @param  mixed        $value
     * @param  int|null     $storeId
     * @param  string|null  $module
     * @param  bool         $autoDelete
     *
     * @return bool
     */
    public function save(string $path, mixed $value, ?int $storeId, ?string $module = null, bool $autoDelete = true): bool
    {
        /** @var CoreConfig $configFromNullStore */
        if (!($configFromNullStore = CoreConfig::wherePath($path)->whereNull('store_id')->where('module', $module)->first())) {
            $this->error(sprintf("Core Config Path '%s' not found for null store.", $path), [__METHOD__]);

            return false;
        }

        $sameAsDefaultStoreValue = ($configFromNullStore->value === $value);

        if ($storeId === null) {
            $config = $configFromNullStore;
        } else {
            /** @var CoreConfig $config */
            if (!($config = CoreConfig::wherePath($path)->where('store_id', $storeId)->where('module', $module)->first())) {

                if ($sameAsDefaultStoreValue) {
                    return false;
                }

                // If not four the current store, try to copy fields like 'label' and 'description' from null store by removing the id and change store_id
                $config2 = $configFromNullStore->toArray();
                if (isset($config2['id'])) {
                    unset($config2['id']);
                }
                $config = CoreConfig::make($config2);

                $config->id = null;
                $config->store_id = $storeId;

                // setup data in case of new creation
                $config->path = $path;
            } else {
                // if value is same like default null store, delete this override
                if ($autoDelete && $sameAsDefaultStoreValue) {
                    $config->delete();
                    $this->warning(sprintf("Core Config deleted: '%s' for store: '%s'.", $path, $storeId), [__METHOD__]);
                    $this->resetCache($storeId);

                    return false;
                }
            }
        }

        // no need to save?
        if ($config->getKey() && ($config->value == $value)) {
            return false;
        }

        // set the value and save
        $oldValue = $config->value;
        $config->value = $value;
        $result = $config->save();

        $this->info(sprintf("Core Config changed: '%s' from value '%s' to '%s'. Store: '%s', User '%s'.", $module.' - '.$path, $oldValue, $value, $storeId, Auth::user()->name));
        $this->resetCache($storeId);

        return $result;
    }
}
