<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Modules\Acl\app\Models\AclResource;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
use Modules\SystemBase\app\Services\ModuleService;
use Nwidart\Modules\Module;

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
     * @return void
     */
    protected function initFilters(): void
    {
        parent::initFilters();

        /** @var ModuleService $moduleService */
        $moduleService = app(ModuleService::class);

        $options = [
            Changelog::FILTER_ALL      => Changelog::FILTER_ALL,
            // app only has special builder
            Changelog::FILTER_APP_ONLY => [
                'label'   => Changelog::FILTER_APP_ONLY,
                'builder' => function (Builder $builder, string $filterElementKey, string $filterValue) {
                    $builder->where(function (Builder $b) { // important: condition in parentheses here!
                        $b->where('path', '')->orWhereNull('path');
                    });
                    //Log::debug("Builder extended to filter '$filterElementKey' to '$filterValue'");
                },
            ],

        ];
        $moduleService->runOrderedEnabledModules(function (Module $module) use (&$options) {
            $options[$module->getStudlyName()] = $module->getName();

            return true;
        });

        $this->addFilterElement('select-module', [
            'label'      => 'Module',
            'default'    => 10,
            'position'   => 1700, // between elements rows and search
            'soft_reset' => true,
            'css_group'  => 'col-12 col-md-3 text-start',
            'css_item'   => '',
            'options'    => $options,
            'builder'    => function (Builder $builder, string $filterElementKey, string $filterValue) {
                if ($filterValue !== Changelog::FILTER_ALL) {
                    $builder->where('path', 'Modules/'.$filterValue);
                    //Log::debug("Builder extended to filter '$filterElementKey' to '$filterValue'");
                }
            },
            'view'       => 'data-table::livewire.js-dt.filters.default-elements.select',
        ]);
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

}
