<?php

namespace Modules\WebsiteBase\app\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\WebsiteBase\app\Models\CoreConfig;

/**
 * Config value will read by optional sore_id and optional module.
 * store_id null is a fallback for all stores and defines the valid values.
 * module null means available for all modules.
 */
class ConfigService extends BaseService
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
    private array $configByStore = [];

    /**
     * Like $configByStore but also indexed by module.
     *
     * @var array
     */
    private array $configByStoreAndModule = [];

    /**
     * @param  int|null  $storeId
     *
     * @return array
     */
    public function buildConfigTree(?int $storeId = null): array
    {
        $this->configByStore[$storeId] = [];
        $this->configByStoreAndModule[$storeId] = [];

        // inherit from null store ...
        if ($storeId !== null) {
            // make sure null store is also filled up ...
            $this->getConfigTree();
            app('system_base')::arrayMergeRecursiveDistinct($this->configByStore[$storeId], $this->configByStore[null]);
            app('system_base')::arrayMergeRecursiveDistinct($this->configByStoreAndModule[$storeId], $this->configByStoreAndModule[null]);
        }

        try {

            // get all entries of all modules by this store ...
            $builder = CoreConfig::with([])->where('store_id', $storeId)->orderBy('module')->orderBy('position')->orderBy('path');

            /** @var CoreConfig $config */
            foreach ($builder->get() as $config) {
                Arr::set($this->configByStore[$storeId], $config->path, $config->value);
                $this->configByStoreAndModule[$storeId][$config->module] ??= [];
                Arr::set($this->configByStoreAndModule[$storeId][$config->module], $config->path, $config->value);
            }

        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);
        }

        return $this->configByStore[$storeId];
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
        if (isset($this->configByStore[$storeId])) {
            return $this->configByStore[$storeId];
        }

        return $this->buildConfigTree($storeId);
    }

    /**
     * @param  int|null     $storeId
     * @param  string|null  $module
     *
     * @return array
     */
    public function getConfigModuleTree(?int $storeId = null, ?string $module = null): array
    {
        // ensure data is set (for storeId and also null store)
        $this->getConfigTree($storeId);

        return $this->configByStoreAndModule[$storeId][$module] ?? [];
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
            return $this->configByStore[$storeId];
        }

        // if not exist the specific store, use the default store (null) ...
        if (($storeId !== null) && (!Arr::has($this->configByStore[$storeId], $path))) {
            return $this->getValue($path, $default, null, $module);
        }

        // try to get by module at first
        if ($module !== null) { // && Arr::has($this->configByStoreAndModule[$storeId], $modulePath)) {
            $modulePath = $module.'.'.$path;

            return Arr::get($this->configByStoreAndModule[$storeId], $modulePath, $default);
        }

        // use store value ...
        return Arr::get($this->configByStore[$storeId], $path, $default);
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
    //    if (!isset($this->configByStore[$storeId])) {
    //        $this->buildConfigTree($storeId);
    //    }
    //
    //    $path = str_replace('/', '.', $path);
    //    Arr::set($this->configByStore[$storeId], $path, $value);
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

        return $result;
    }
}
