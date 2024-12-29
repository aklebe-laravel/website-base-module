<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Modules\Acl\app\Models\AclResource;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
use Modules\WebsiteBase\app\Services\WebsiteService;

class NotificationTemplate extends BaseDataTable
{
    use BaseWebsiteBaseDataTable;

    /**
     * Restrictions to allow this component.
     */
    public const array aclResources = [
        AclResource::RES_DEVELOPER,
        AclResource::RES_MANAGE_DESIGN,
        AclResource::RES_MANAGE_CONTENT,
    ];

    /**
     *
     */
    const string FILTER_NOTIFICATION_CHANNEL_ALL = '';

    /**
     * @return void
     */
    protected function initFilters(): void
    {
        parent::initFilters();

        $this->addFilterElement('notification_channel', [
            'label'      => 'Channel',
            'default'    => 10,
            'position'   => 1700, // between elements rows and search
            'soft_reset' => true,
            'css_group'  => 'col-12 col-md-3 text-start',
            'css_item'   => '',
            'options'    => app('system_base')->toHtmlSelectOptions(WebsiteService::NOTIFICATION_CHANNELS,
                first: app('system_base')->getHtmlSelectOptionNoValue('All Channels', NotificationEvent::FILTER_NOTIFICATION_CHANNEL_ALL)),
            'builder'    => function (Builder $builder, string $filterElementKey, string $filterValue) {
                if (!$filterValue || $filterValue === self::FILTER_NOTIFICATION_CHANNEL_ALL) {
                    return;
                }
                $builder->where(function (Builder $b) use ($filterValue) {
                    $b->where('notification_channel', $filterValue)
                      ->orWhere('notification_channel', $filterValue);
                });

            },
            'view'       => 'data-table::livewire.js-dt.filters.default-elements.select',
        ]);
    }

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
                'name'       => 'notification_channel',
                'label'      => __('Notification Channel'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'w-10',
            ],
            [
                'name'       => 'description',
                'label'      => __('Description'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-lg w-30',
            ],
        ];
    }

}
