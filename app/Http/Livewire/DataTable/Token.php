<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Modules\WebsiteBase\app\Http\Livewire\DataTable\Base\DefaultDataTable;

class Token extends DefaultDataTable
{
    /**
     * Overwrite this to be able to filter by users.
     *
     * @var string
     */
    public string $columnUserId = 'user_id';

    /**
     * Overwrite to init your sort orders before session exists
     *
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('purpose', 'asc');
        $this->setSortAllCollections('expires_at', 'desc');
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
                'css_all'    => 'show-md text-muted font-monospace text-end w-5',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'     => 'user_id',
                'label'    => 'User',
                'css_all'  => 'show-lg w-10',
                'sortable' => true,
                'view'     => 'data-table::livewire.js-dt.tables.columns.user',
            ],
            [
                'name'       => 'purpose',
                'label'      => __('Purpose'),
                'css_all'    => 'show-md w-10',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'token',
                'label'      => __('Token'),
                'css_all'    => 'w-25',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'values',
                'label'      => 'Json Values',
                'css_all'    => 'show-lg w-25',
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.count',
            ],
            [
                'name'       => 'expires_at',
                'label'      => 'Expired',
                'css_all'    => 'show-lg w-10',
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
            ],
            [
                'name'       => 'updated_at',
                'label'      => 'Updated',
                'css_all'    => 'w-10',
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
            ],
        ];
    }
}
