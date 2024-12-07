<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Modules\Acl\app\Models\AclResource;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

class Address extends BaseDataTable
{
    /**
     * Minimum restrictions to allow this component.
     */
    public const array aclResources = [AclResource::RES_DEVELOPER, AclResource::RES_TRADER];

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
                'css_all'    => 'hide-mobile-show-lg text-muted font-monospace text-end w-5',
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
                'css_all'    => 'hide-mobile-show-lg w-5',
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
                'css_all'    => 'hide-mobile-show-md w-20',
            ],
            [
                'name'       => 'country_iso',
                'label'      => __('Country'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-md w-5',
            ],
            [
                'name'       => 'city',
                'label'      => __('City'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-sm w-5',
            ],
            [
                'name'       => 'region',
                'label'      => __('Region'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-lg w-5',
            ],
            [
                'name'       => 'zip',
                'label'      => __('Zip'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-md w-5',
            ],
            [
                'name'       => 'updated_at',
                'label'      => 'Updated',
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
                'css_all'    => 'hide-mobile-show-lg w-5',
            ],
            [
                'name'       => 'user_description',
                'visible'    => false,
                'searchable' => true,
                'css_all'    => 'hide-mobile-show-lg w-15',
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

        if ($this->useCollectionUserFilter) {
            $builder->whereUserId($this->getUserId());
        }

        return $builder;
    }

}
