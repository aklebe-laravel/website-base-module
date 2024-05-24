<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

class CoreConfig extends BaseDataTable
{
    /**
     * @var string
     */
    public string $modelName = 'CoreConfig';

    /**
     * Overwrite to init your sort orders before session exists
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('path', 'asc');
    }

    /**
     * @return array[]
     */
    public function getColumns(): array
    {
        return [
            [
                'name'       => 'id',
                'label'      => 'Link',
                'format'     => 'number',
                'css_all'    => 'text-muted font-monospace text-end w-5',
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
