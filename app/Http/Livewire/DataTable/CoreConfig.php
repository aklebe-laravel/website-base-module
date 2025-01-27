<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Modules\WebsiteBase\app\Models\CoreConfig as CoreConfigModel;

class CoreConfig extends WebsiteBase
{
    /**
     * @var string
     */
    public string $eloquentModelName = CoreConfigModel::class;

    /**
     * Overwrite to init your sort orders before session exists
     *
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('path', 'asc');
    }

    /**
     * @return void
     */
    protected function initFilters(): void
    {
        parent::initFilters();

        $this->addStoreFilter();
    }

    /**
     * @return array[]
     */
    public function getColumns(): array
    {
        return [
            [
                'name'       => 'store_id',
                'label'      => 'Store',
                'css_all'    => 'small w-5',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'module',
                'label'      => 'Module',
                'options'    => [
                    'str_limit' => 3,
                ],
                'css_all'    => 'small w-5',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'path',
                'label'      => 'Path',
                'options'    => [
                    'has_open_link' => $this->canEdit(),
                    // 'str_limit'     => 30,
                ],
                'css_all'    => 'w-20',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'value',
                'label'      => 'Value',
                'css_all'    => 'w-20',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'description',
                'label'      => 'Description',
                'css_all'    => 'w-30',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'updated_at',
                'label'      => 'Updated',
                'css_all'    => 'w-10',
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
                'searchable' => true,
                'sortable'   => true,
            ],
        ];
    }

}
