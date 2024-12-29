<?php

namespace Modules\WebsiteBase\app\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\WebsiteBase\app\Models\CoreConfig;

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
     * Config tree by store id.
     * $configByStore[null] is also possible to get the default tree.
     *
     * @var array
     */
    private array $configByStore = [];

    /**
     * @param  int|null  $storeId
     *
     * @return array
     */
    public function buildConfigTree(int $storeId = null): array
    {
        $this->configByStore[$storeId] = [];

        try {
            $builder = CoreConfig::with([])->where('store_id', $storeId)->orderBy('position')->orderBy('path');

            /** @var CoreConfig $config */
            foreach ($builder->get() as $config) {
                if (Arr::has($this->configByStore[$storeId], $config->path)) {
                    // Try to override deeper settings like "x.y=1" but "x.y.z" already exists
                    throw new Exception('Invalid override for path: '.$config->path);
                } else {
                    Arr::set($this->configByStore[$storeId], $config->path, $config->value);
                }
            }

        } catch (Exception $ex) {
            Log::error("Error by getting config!", [__METHOD__]);
            Log::error($ex->getMessage());
        }

        return $this->configByStore;
    }

    /**
     * @param  int|null  $storeId
     *
     * @return array
     */
    public function getConfigTree(int $storeId = null): array
    {
        if (isset($this->configByStore[$storeId])) {
            return $this->configByStore[$storeId];
        }

        return $this->buildConfigTree($storeId);
    }

    /**
     * Get a prepared and preloaded config
     *
     * @param  string       $path
     * @param  mixed        $default
     * @param  int|null     $storeId
     * @param  string|null  $module
     *
     * @return mixed
     */
    public function get(string $path = '', mixed $default = null, ?int $storeId = self::CURRENT_STORE_MARKER, ?string $module = null): mixed
    {
        if ($storeId === self::CURRENT_STORE_MARKER) {
            $storeId = app('website_base_settings')->getStore()->id ?? null;
        }

        if (!isset($this->configByStore[$storeId])) {
            $this->buildConfigTree($storeId);
        }

        if ($path) {
            $path = str_replace('/', '.', $path);
        }

        // if not exist the specific store, use the default store (null) ...
        if (($storeId !== null) && (!Arr::has($this->configByStore[$storeId], $path))) {
            return $this->get($path, $default, null, $module);
        }

        if (!$path) {
            return $this->configByStore[$storeId];
        }

        // use store value ...
        return Arr::get($this->configByStore[$storeId], $path, $default);
    }

    /**
     * Get a prepared and preloaded config
     *
     *
     * @param  string    $path
     * @param  mixed     $value
     * @param  int|null  $storeId
     * @param  bool      $persist
     *
     * @return void
     */
    public function set(string $path, mixed $value, ?int $storeId = self::CURRENT_STORE_MARKER, bool $persist = false): void
    {
        if ($storeId === self::CURRENT_STORE_MARKER) {
            $storeId = app('website_base_settings')->getStore()->id;
        }

        if (!isset($this->configByStore[$storeId])) {
            $this->buildConfigTree($storeId);
        }

        $path = str_replace('/', '.', $path);
        Arr::set($this->configByStore[$storeId], $path, $value);

        if ($persist) {
            /** @var CoreConfig $config */
            if ($config = CoreConfig::wherePath($path)->where('store_id', $storeId)->firstOrNew()) {
                $config->store_id = $storeId;
                $config->path = $path;
                $config->value = $value;
                $config->save();
            }
        }
    }

    /**
     * @param  string       $path
     * @param  mixed        $value
     * @param  int|null     $storeId
     * @param  string|null  $module
     *
     * @return bool
     */
    public function save(string $path, mixed $value, ?int $storeId, ?string $module = null): bool
    {
        /** @var CoreConfig $configFromNullStore */
        $configFromNullStore = CoreConfig::wherePath($path)->whereNull('store_id')->where('module', $module)->first();
        /** @var CoreConfig $config */
        if (!($config = CoreConfig::wherePath($path)->where('store_id', $storeId)->where('module', $module)->first())) {

            // If not four the current store, try to copy fields like 'label' and 'description' from null store by removing the id and change store_id
            $config2 = $configFromNullStore ? $configFromNullStore->toArray() : [];
            if (isset($config2['id'])) {
                unset($config2['id']);
            }
            $config = CoreConfig::make($config2);

            $config->id = null;
            $config->store_id = $storeId;

            // setup data in case of new creation
            $config->path = $path;
        }

        // no need to save?
        if ($config->getKey() && ($config->value == $value)) {
            return false;
        }

        // set the value and save
        $oldValue = $config->value;
        $config->value = $value;
        $result = $config->save();

        $this->debug(sprintf("core config changed: '%s' from '%s' to '%s' by user '%s'.", $module.' - '.$path, $oldValue, $value, Auth::user()->name));

        return $result;
    }
}
