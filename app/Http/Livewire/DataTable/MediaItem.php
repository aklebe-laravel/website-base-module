<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Modules\Acl\app\Models\AclResource;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

class MediaItem extends BaseDataTable
{
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
        $this->setSortAllCollections('updated_at', 'desc');
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
                'name'       => 'user_id',
                'label'      => __('User'),
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.user',
                'css_all'    => 'hide-mobile-show-md text-center w-10',
                'icon'       => 'person',
            ],
            [
                'name'    => 'final_thumb_small_url',
                'view'    => 'data-table::livewire.js-dt.tables.columns.image',
                'label'   => __('Image'),
                'css_all' => 'text-center w-10',
                'icon'    => 'image',
            ],
            [
                'name'       => 'name',
                'label'      => __('Name'),
                'options'    => [
                    'has_open_link' => $this->canEdit(),
                    'str_limit'     => 30,
                ],
                'css_all'    => 'hide-mobile-show-sm w-50',
                'searchable' => true,
                'sortable'   => true,
                'icon'       => 'tag',
            ],
            [
                'name'       => 'media_type',
                'label'      => __('Media Type'),
                'css_all'    => 'hide-mobile-show-lg w-10',
                'visible'    => false,
                'searchable' => true,
                'sortable'   => true,
                'icon'       => 'code',
            ],
            [
                'name'       => 'object_type',
                'label'      => __('Object Type'),
                'css_all'    => 'hide-mobile-show-lg w-10',
                'searchable' => true,
                'sortable'   => true,
                'icon'       => 'code',
            ],
            [
                'name'       => 'description',
                'label'      => __('Description'),
                'css_all'    => 'hide-mobile-show-lg w-30',
                'searchable' => true,
                'sortable'   => true,
                'icon'       => 'card-text',
            ],
            [
                'name'       => 'updated_at',
                'label'      => __('Updated At'),
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
                'css_all'    => 'hide-mobile-show-md w-10',
                'icon'       => 'arrow-clockwise',
            ],
            [
                'name'       => 'meta_description',
                'visible'    => false,
                'searchable' => true,
            ],
            [
                'name'       => 'file_name',
                'visible'    => false,
                'searchable' => true,
            ],
            [
                'name'       => 'position',
                'visible'    => false,
                'searchable' => true,
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
     * @throws Exception
     */
    public function getBaseBuilder(string $collectionName): ?Builder
    {
        $builder = parent::getBaseBuilder($collectionName);
        if ($this->useCollectionUserFilter) {
            $builder = $builder->where(function ($b) {
                $b = $b->whereUserId($this->getUserId())->orWhereNull('user_id');
            });
        }
        return $builder;
    }

}
