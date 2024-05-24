<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Modules\Acl\app\Models\AclResource;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
use Modules\WebsiteBase\app\Services\CreateChangeLogService;

class Changelog extends BaseDataTable
{
    /**
     * Minimum restrictions to allow this component.
     */
    public const aclResources = [AclResource::RES_DEVELOPER, AclResource::RES_MANAGE_CONTENT];

    /**
     *
     */
    const FILTER_ALL = '[ALL]';

    /**
     *
     */
    const FILTER_APP_ONLY = '[APP ONLY]';

    /**
     * @var array|array[]
     */
    protected array $filterConfig = [
        [
            'css_group' => 'col-12 col-md-3 text-start',
            'css_item'  => '',
            'view'      => 'data-table::livewire.js-dt.filters.rows-per-page.default',
        ],
        [
            'css_group' => 'col-12 col-md text-center',
            'css_item'  => '',
            'view'      => 'website-base::livewire.js-dt.commands.select-module',
        ],
        [
            'css_group' => 'col-12 col-md text-end',
            'css_item'  => '',
            'view'      => 'data-table::livewire.js-dt.filters.search.default',
        ],
        [
            'css_group' => 'col-12 col-md-1 text-end',
            'css_item'  => '',
            'view'      => 'data-table::livewire.js-dt.filters.settings.default',
        ],
    ];

    /**
     * Runs once, immediately after the component is instantiated, but before render() is called.
     * This is only called once on initial page load and never called again, even on component refreshes
     *
     * @return void
     */
    protected function initMount(): void
    {
        // Important to call this before calling parent::initMount()!
        foreach ($this->enabledCollectionNames as $collectionName => $enabled) {
            data_set($this->filtersDefaults, $collectionName.'.changelog_method', self::FILTER_APP_ONLY);
        }

        //
        parent::initMount();

        // add changelog_method to reset page to 1 when updated
        $this->filterSoftResetActivators[] = 'changelog_method';

        // update git histories
        /** @var CreateChangeLogService $changelog */
        $changelog = app(CreateChangeLogService::class);
        $changelog->updateGitHistories();
    }

    /**
     * Overwrite to init your sort orders before session exists
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('commit_created_at', 'desc');
    }

    /**
     * Runs on every request, after the component is mounted or hydrated, but before any update methods are called
     *
     * @return void
     */
    protected function initBooted(): void
    {
        parent::initBooted();

        if (!$this->canManage()) {
            $this->rowCommands = [];
        }
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
                'visible'    => $this->editable,
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'text-muted font-monospace text-end w-5',
            ],
            [
                'name'       => 'hash',
                'label'      => 'Hash',
                'visible'    => false,
                'sortable'   => true,
                'searchable' => true,
            ],
            [
                'name'       => 'path',
                'label'      => 'Path',
                'visible'    => false,
                'sortable'   => true,
                'searchable' => true,
            ],
            [
                'name'       => 'messages',
                'label'      => __('Messages'),
                'css_all'    => 'w-60',
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'website-base::livewire.js-dt.tables.columns.changelog-messages',
            ],
            [
                'name'       => 'messages_staff',
                'visible'    => false,
                'sortable'   => true,
                'searchable' => true,
            ],
            [
                'name'       => 'messages_public',
                'visible'    => false,
                'sortable'   => true,
                'searchable' => true,
            ],
            [
                'name'       => 'acl_resources',
                'label'      => 'acl',
                'visible'    => false,
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.count',
                'css_all'    => 'w-20',
            ],
            [
                'name'       => 'commit_created_at',
                'label'      => 'Created',
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
                'css_all'    => 'w-10',
            ],
        ];
    }

    /**
     * Overwrite this to add filters
     *
     * @param  Builder  $builder
     * @param  string  $collectionName
     *
     * @return void
     */
    protected function addCustomFilters(Builder $builder, string $collectionName)
    {
        switch ($clm = data_get($this->filters, $collectionName.'.changelog_method')) {

            case '':
            case self::FILTER_ALL:
                break;

            case self::FILTER_APP_ONLY:
                $builder->where('path', '')->orWhereNull('path');
                break;

            default:
                $builder->where('path', 'Modules/'.$clm);
                break;
        }
    }

}
