<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\On;
use Modules\Acl\app\Models\AclResource;
use Modules\KlaraDeployment\app\Jobs\MediaItemImport as MediaItemImportJob;
use Modules\WebsiteBase\app\Models\MediaItem as MediaItemModel;
use Modules\WebsiteBase\app\Services\CoreConfigService;

class MediaItemImport extends MediaItem
{
    use BaseWebsiteBaseDataTable;

    /**
     * Restrictions to allow this component.
     */
    public const array aclResources = [AclResource::RES_TRADER];

    /**
     * @var string
     */
    public string $description = "import_description";

    /**
     * Runs on every request, after the component is mounted or hydrated, but before any update methods are called
     *
     * @return void
     */
    protected function initBooted(): void
    {
        parent::initBooted();

        $this->rowCommands = [
            //'launch' => 'website-base::livewire.js-dt.tables.columns.buttons.launch',
            'import' => 'website-base::livewire.js-dt.tables.columns.buttons.import',
            ...$this->rowCommands,
        ];

        $this->addBaseWebsiteMessageBoxes();
    }

    /**
     * Overwrite to init your sort orders before session exists
     *
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('updated_at', 'desc');
    }

    /**
     * @return void
     */
    protected function initFilters(): void
    {
        parent::initFilters();

        // remove media type
        $this->removeFilterElement('filter_media_type');

        // use object types for import only
        $types = [];
        foreach (data_get(MediaItemModel::MEDIA_TYPES, MediaItemModel::MEDIA_TYPE_IMPORT.'.objects') as $type) {
            $types[$type] = $type;
        }
        data_set($this->filterElementConfig, 'filter_object_type.options', [
            '' => '[All Object Types]',
            ... $types,
        ]);
    }

    /**
     * @return array[]
     */
    public function getColumns(): array
    {
        return [
            [
                'name'       => 'id',
                'label'      => 'ID',
                'format'     => 'number',
                'css_all'    => 'hide-mobile-show-lg text-muted font-monospace text-end w-5',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'user_id',
                'label'      => __('User'),
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.user',
                'css_all'    => 'hide-mobile-show-md text-center w-10',
                'icon'       => 'person',
            ],
            [
                'name'       => 'name',
                'label'      => __('Name'),
                'options'    => [
                    'has_open_link' => $this->canEdit(),
                    'str_limit'     => 30,
                ],
                'css_all'    => 'hide-mobile-show-sm w-30',
                'searchable' => true,
                'sortable'   => true,
                'icon'       => 'tag',
            ],
            [
                'name'       => 'object_type',
                'label'      => __('Object Type'),
                'css_all'    => 'hide-mobile-show-lg w-10',
                'searchable' => true,
                'sortable'   => true,
                'icon'       => 'code',
            ],
            [
                'name'       => 'description',
                'label'      => __('Description'),
                'css_all'    => 'hide-mobile-show-lg w-40',
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'website-base::livewire.js-dt.tables.columns.media-item-file-info',
                'icon'       => 'card-text',
            ],
            [
                'name'       => 'updated_at',
                'label'      => __('Updated At'),
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
                'css_all'    => 'hide-mobile-show-md w-10',
                'icon'       => 'arrow-clockwise',
            ],
        ];
    }

    /**
     * The base builder before all filter manipulations.
     * Usually used for all collections (default, selected, unselected), but can be overwritten.
     *
     * @param  string  $collectionName
     *
     * @return Builder|null
     * @throws Exception
     */
    public function getBaseBuilder(string $collectionName): ?Builder
    {
        /** @var Builder|MediaItemModel $builder */
        $builder = parent::getBaseBuilder($collectionName);

        $builder->imports();

        return $builder;
    }

    /**
     * @param  string|int  $livewireId
     * @param  string|int  $itemId
     *
     * @return bool
     */
    #[On('import')]
    public function upload(string|int $livewireId, string|int $itemId): bool
    {
        if (!$this->checkLivewireId($livewireId)) {
            return false;
        }

        /** @var CoreConfigService $config */
        $config = app('website_base_config');

        if (!$config->getValue('import.enabled', false)) {
            $this->addErrorMessage("Import disabled!");

            return false;
        }

        // max attempts 0 = no limiter
        if ($maxAttempts = (int) $config->getValue('import.rate-limiter.max', 0)) {
            $secondsToReset = (int) $config->getValue('import.rate-limiter.reset', 60 * 60 * 24);

            // @todo: another rate limiter per user per import file
            // ...

            // The attempt method returns false when the callback has no remaining attempts available; otherwise, the attempt method will return the callback's result or true.
            // https://laravel.com/docs/11.x/rate-limiting
            $executed = RateLimiter::attempt('import-rate-limiter-amount-and-user-'.(Auth::id() ?? 0), $maxAttempts, function () use ($config, $itemId) {

                if ($maxAttemptsPerFile = (int) $config->getValue('import.rate-limiter.file.max', 1)) {
                    $secondsToReset = (int) $config->getValue('import.rate-limiter.file.reset', 300);
                    $executed = RateLimiter::attempt('import-rate-limiter-per-file-'.(Auth::id() ?? 0), $maxAttemptsPerFile, function () use ($config, $itemId) {

                        $dispatchInMinutes = (int) $config->getValue('import.rate-limiter.delay', 0);
                        /** @var MediaItemModel $mediaItem */
                        if (($mediaItem = MediaItemModel::find($itemId)) && ($mediaItem->getKey())) {
                            MediaItemImportJob::dispatch($mediaItem)->delay(now()->addMinutes($dispatchInMinutes));
                            $this->addSuccessMessage("Import was queued and should started in $dispatchInMinutes minutes! You will received a notification after importing is done.");
                        }

                    }, $secondsToReset);

                    if (!$executed) {
                        $this->addErrorMessage("Import limit for this file was reached!");

                        return false;
                    }

                }

                return true;
            }, $secondsToReset);


            if (!$executed) {
                $this->addErrorMessage("Per user import limit or for this file was reached!");

                return false;
            }

            return true;
        }

        return false;
    }
}
