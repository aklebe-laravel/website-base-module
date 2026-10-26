<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Modules\Acl\app\Models\AclResource;
use Modules\WebsiteBase\app\Http\Livewire\DataTable\Base\DefaultDataTable;

class Address extends DefaultDataTable
{
    /**
     * Overwrite this to be able to filter by users.
     *
     * @var string
     */
    public string $columnUserId = 'user_id';

    /**
     * Minimum restrictions to allow this component.
     */
    public const array aclResources = [AclResource::RES_DEVELOPER, AclResource::RES_MEMBER];

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
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'show-lg text-muted font-monospace text-end w-5',
            ],
            [
                'name'       => 'email',
                'label'      => __('Email'),
                'visible'    => false,
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'phone',
                'label'      => __('Phone'),
                'visible'    => false,
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'firstname',
                'label'      => __('Firstname'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'show-lg w-5',
            ],
            [
                'name'       => 'lastname',
                'label'      => __('Lastname'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'w-5',
            ],
            [
                'name'       => 'street',
                'label'      => __('Street'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'show-md w-20',
            ],
            [
                'name'       => 'country_iso',
                'label'      => __('Country'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'show-md w-5',
            ],
            [
                'name'       => 'city',
                'label'      => __('City'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'show-sm w-5',
            ],
            [
                'name'       => 'region',
                'label'      => __('Region'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'show-lg w-5',
            ],
            [
                'name'       => 'zip',
                'label'      => __('Zip'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'show-md w-5',
            ],
            [
                'name'       => 'updated_at',
                'label'      => 'Updated',
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
                'css_all'    => 'show-lg w-5',
            ],
            [
                'name'       => 'user_description',
                'visible'    => false,
                'searchable' => true,
                'css_all'    => 'show-lg w-15',
            ],
        ];
    }
}
