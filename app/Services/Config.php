<?php

namespace Modules\WebsiteBase\app\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Modules\WebsiteBase\app\Models\CoreConfig;

class Config
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
            $collection = CoreConfig::with([])->where('store_id', $storeId)->get();

            /** @var CoreConfig $config */
            foreach ($collection as $config) {
                Arr::set($this->configByStore[$storeId], $config->path, $config->value);
            }

        } catch (\Exception $ex) {
            Log::error("Error by getting config!", [__METHOD__]);
            Log::error($ex->getMessage());
        }

        return $this->configByStore;
    }

    /**
     * @param  string    $path
     * @param  mixed     $default
     * @param  int|null  $storeId
     *
     * @return mixed
     */
    public function get(string $path = '', mixed $default = null, ?int $storeId = self::CURRENT_STORE_MARKER): mixed
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
            return $this->get($path, $default, null);
        }

        if (!$path) {
            return $this->configByStore[$storeId];
        }

        // use store value ...
        $value = Arr::get($this->configByStore[$storeId], $path, $default);

        return $value;
    }

    /**
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
}
