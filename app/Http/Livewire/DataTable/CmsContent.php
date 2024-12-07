<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Modules\Acl\app\Models\AclResource;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

class CmsContent extends BaseDataTable
{
    /**
     * Minimum restrictions to allow this component.
     */
    public const array aclResources = [AclResource::RES_DEVELOPER, AclResource::RES_MANAGE_CONTENT];

    /**
     * Overwrite to init your sort orders before session exists
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('code', 'asc');
        $this->setSortAllCollections('store_id', 'asc');
        $this->setSortAllCollections('locale', 'asc');
    }

    /**
     * @return array[]
     */
    public function getColumns(): array
    {
        return [
            [
                'name'       => 'id',
                'label'      => __('ID'),
                'format'     => 'number',
                'css_all'    => 'text-muted font-monospace text-end w-5',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'     => 'is_enabled',
                'label'    => __('Enabled'),
                'view'     => 'data-table::livewire.js-dt.tables.columns.bool-red-green',
                'css_all'  => 'text-center w-5',
                'sortable' => true,
            ],
            [
                'name'       => 'parent_id',
                'label'      => __('Parent'),
                'format'     => 'number',
                'css_all'    => 'text-muted font-monospace text-end w-5',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'store_id',
                'label'      => __('Store'),
                'format'     => 'number',
                'css_all'    => 'text-muted font-monospace text-end w-5',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'code',
                'label'      => __('Code'),
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'locale',
                'label'      => __('Locale'),
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'updated_at',
                'label'      => __('Updated At'),
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
            ],
        ];
    }

}
