<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Modules\Acl\app\Models\AclResource;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

class ViewTemplate extends BaseDataTable
{
    /**
     * @var string
     */
    public string $description = "View templates will be used for content of notification templates, cms content or just everything where you need dynamic content.";

    /**
     * Restrictions to allow this component.
     */
    public const aclResources = [AclResource::RES_TRADER];

    /**
     * Overwrite to init your sort orders before session exists
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('code', 'asc');
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
                'name'     => 'is_enabled',
                'label'    => __('Enabled'),
                'view'     => 'data-table::livewire.js-dt.tables.columns.bool-red-green',
                'css_all'  => 'hide-mobile-show-lg text-center w-5',
                'sortable' => true,
            ],
            [
                'name'     => 'is_valid',
                'label'    => __('Valid'),
                'view'     => 'data-table::livewire.js-dt.tables.columns.bool-red-green',
                'css_all'  => 'hide-mobile-show-lg text-center w-5',
                'sortable' => true,
            ],
            [
                'name'       => 'code',
                'label'      => __('Code'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'w-30',
            ],
            [
                'name'       => 'content',
                'label'      => __('Content'),
                'view'       => 'data-table::livewire.js-dt.tables.columns.strlen-kb',
                'css_all'    => 'hide-mobile-show-md text-center w-5',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'description',
                'label'      => __('Description'),
                'css_all'    => 'hide-mobile-show-lg w-30',
                'searchable' => true,
                'sortable'   => true,
            ],
        ];
    }

}
