<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

class Token extends BaseDataTable
{
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
                'css_all'    => 'hide-mobile-show-md text-muted font-monospace text-end w-5',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'     => 'user_id',
                'label'    => 'User',
                'css_all'  => 'hide-mobile-show-lg w-10',
                'sortable' => true,
                'view'     => 'data-table::livewire.js-dt.tables.columns.user',
            ],
            [
                'name'       => 'purpose',
                'label'      => __('Purpose'),
                'css_all'    => 'hide-mobile-show-md w-10',
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
                'css_all'    => 'hide-mobile-show-lg w-25',
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.count',
            ],
            [
                'name'       => 'expires_at',
                'label'      => 'Expired',
                'css_all'    => 'hide-mobile-show-lg w-10',
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

    /**
     * The base builder before all filter manipulations.
     * Usually used for all collections (default, selected, unselected), but can be overwritten.
     *
     * @param  string  $collectionName
     *
     * @return Builder|null
     * @throws \Exception
     */
    public function getBaseBuilder(string $collectionName): ?Builder
    {
        $builder = parent::getBaseBuilder($collectionName);

        if ($this->filterByParentOwner) {
            $builder->whereUserId($this->getUserId());
        }

        return $builder;
    }

}
