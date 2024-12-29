<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Support\Collection as SupportCollection;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
use Modules\SystemBase\app\Services\ModuleService;

class Module extends BaseDataTable
{
    /**
     * @var string
     */
    public string $columnNameId = 'name';

    /**
     * Overwrite to init your sort orders before session exists
     *
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('priority', 'asc');
        $this->setSortAllCollections('name', 'desc');
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
                'name'     => 'is_enabled',
                'label'    => __('Enabled'),
                'view'     => 'data-table::livewire.js-dt.tables.columns.bool-red-green',
                'css_all'  => 'hide-mobile-show-md text-center w-5',
                'sortable' => true,
                'icon'     => 'check',
            ],
            [
                'name'       => 'name',
                'label'      => __('Name'),
                'css_all'    => 'w-50',
                'options'    => [
                    'has_open_link' => $this->canEdit(),
                    'str_limit'     => 30,
                ],
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'priority',
                'label'      => __('Priority'),
                'css_all'    => 'hide-mobile-show-lg w-25',
                'searchable' => true,
                'sortable'   => true,
            ],
        ];
    }

    /**
     * @param  string  $collectionName
     *
     * @return SupportCollection|null
     */
    public function getFixCollection(string $collectionName): ?SupportCollection
    {
        /** @var ModuleService $moduleService */
        $moduleService = app('system_base_module');
        $moduleList = $moduleService->getItemInfoList(false);

        return $moduleList;
    }


}
