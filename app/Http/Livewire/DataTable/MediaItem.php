<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Modules\Acl\app\Models\AclResource;
use Modules\WebsiteBase\app\Http\Livewire\DataTable\Base\DefaultDataTable;
use Modules\WebsiteBase\app\Models\MediaItem as MediaItemModel;

class MediaItem extends DefaultDataTable
{
    /**
     * Overwrite this to be able to filter by users.
     *
     * @var string
     */
    public string $columnUserId = 'user_id';

    /**
     * Prepared for inheritances
     * *
     *
     * @var string
     */
    public string $eloquentModelName = MediaItemModel::class;

    /**
     * Restrictions to allow this component.
     */
    public const array aclResources = [AclResource::RES_MEMBER];

    /**
     * @var array|string[]
     */
    public array $objectRelations = ['user'];

    /**
     * Overwrite to init your sort orders before session exists
     *
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('updated_at', 'desc');
    }

    /**
     * @return void
     */
    protected function initFilters(): void
    {
        parent::initFilters();

        $this->addFilterElement('filter_media_type', [
            'label'      => 'Filter',
            'default'    => '',
            'position'   => 1700, // between elements rows and search
            'soft_reset' => true,
            'css_group'  => '',
            'css_item'   => '',
            'options'    => [
                '' => '[All Media Types]',
                ... MediaItemModel::getMediaTypesAsSelectOptions(),
            ],
            'builder'    => function (Builder $builder, string $filterElementKey, string $filterValue) {
                if (!$filterValue) {
                    return;
                }

                $builder->where('media_type', $filterValue);
            },
            'view'       => 'data-table::livewire.js-dt.filters.default-elements.select',
        ]);

        $this->addFilterElement('filter_object_type', [
            'label'      => 'Filter',
            'default'    => '',
            'position'   => 1700, // between elements rows and search
            'soft_reset' => true,
            'css_group'  => '',
            'css_item'   => '',
            'options'    => [
                '' => '[All Object Types]',
                ... MediaItemModel::getObjectTypesAsSelectOptions(),
            ],
            'builder'    => function (Builder $builder, string $filterElementKey, string $filterValue) {
                if (!$filterValue) {
                    return;
                }

                $builder->where('object_type', $filterValue);
            },
            'view'       => 'data-table::livewire.js-dt.filters.default-elements.select',
        ]);
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
                'css_all'    => 'show-lg w-20 text-muted font-monospace justify-end text-end',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'       => 'user_id',
                'label'      => __('User'),
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.user',
                'css_all'  => 'show-lg w-20 text-left',
                'icon'       => 'user',
            ],
            [
                'name'    => 'final_thumb_small_url',
                'label'   => __('Image'),
                'view'    => 'data-table::livewire.js-dt.tables.columns.image',
                'css_all'  => 'show-lg w-40 text-left',
                'icon'    => 'book-image',
            ],
            [
                'name'       => 'name',
                'label'      => __('Name'),
                'options'    => [
                    'has_open_link' => $this->canEdit(),
                    'str_limit'     => 30,
                ],
                'css_all'    => 'w-[20%] truncate text-left',
                'searchable' => true,
                'sortable'   => true,
                'icon'       => 'tag',
            ],
            [
                'name'       => 'media_type',
                'label'      => __('Media Type'),
                'css_all'    => 'show-lg w-[20%] truncate text-left',
                'visible'    => false,
                'searchable' => true,
                'sortable'   => true,
                'icon'       => 'square-terminal',
            ],
            [
                'name'       => 'object_type',
                'label'      => __('Object Type'),
                'css_all'    => 'show-lg w-[20%] truncate text-left',
                'searchable' => true,
                'sortable'   => true,
                'icon'       => 'square-terminal',
            ],
            [
                'name'       => 'description',
                'label'      => __('Description'),
                'css_all'    => 'show-lg w-[40%] truncate text-left',
                'searchable' => true,
                'sortable'   => true,
                'icon'       => 'file-text',
            ],
            [
                'name'       => 'updated_at',
                'label'      => __('Updated At'),
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
                'css_all'    => 'show-md w-40 justify-end text-end',
                'icon'       => 'clock',
            ],
            //[
            //    'name'       => 'meta_description',
            //    'visible'    => false,
            //    'searchable' => true,
            //],
            //[
            //    'name'       => 'file_name',
            //    'visible'    => false,
            //    'searchable' => true,
            //],
            //[
            //    'name'       => 'position',
            //    'visible'    => false,
            //    'searchable' => true,
            //],
        ];
    }
}
