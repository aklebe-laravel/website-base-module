<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Modules\Acl\app\Models\AclResource;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
use Modules\WebsiteBase\app\Services\WebsiteService;

class NotificationConcern extends BaseDataTable
{
    use BaseWebsiteBaseDataTable;

    /**
     * Restrictions to allow this component.
     */
    public const aclResources = [
        AclResource::RES_DEVELOPER,
        AclResource::RES_MANAGE_DESIGN,
        AclResource::RES_MANAGE_CONTENT,
    ];

    /**
     *
     */
    const FILTER_NOTIFICATION_CHANNEL_ALL = '';

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
                first: [NotificationEvent::FILTER_NOTIFICATION_CHANNEL_ALL => '['.__('All Channels').']']),
            'builder'    => function (Builder $builder, string $filterElementKey, string $filterValue) {
                if (!$filterValue || $filterValue === self::FILTER_NOTIFICATION_CHANNEL_ALL) {
                    return;
                }
                $builder->whereHas('notificationTemplate', function ($query) use ($filterValue) {
                    $query->where('notification_channel', $filterValue)
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
        $this->setSortAllCollections('reason_code', 'asc');
        // $this->setSortAllCollections('notificationTemplate.notification_channel', 'asc'); // unable to order by dot notations
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
                'name'       => 'store.code',
                'label'      => __('Store'),
                'searchable' => true,
                'sortable'   => false, // unable to order by dot notations
                'css_all'    => 'hide-mobile-show-lg hide-mobile-show-lg',
            ],
            [
                'name'       => 'reason_code',
                'label'      => __('Reason'),
                'searchable' => true,
                'sortable'   => true,
                'options'    => [
                    'str_limit'     => 20,
                    'has_open_link' => true,
                    'popups'        => [
                        [], // empty object means popup with value itself, but uncut
                    ],
                ],
                'css_all'    => '',
            ],
            [
                'name'       => 'notificationTemplate.notification_channel',
                'label'      => __('Notification Channel'),
                'searchable' => true,
                'sortable'   => false, // unable to order by dot notations
                'css_all'    => 'w-10',
            ],
            [
                'name'       => 'priority',
                'label'      => __('Priority'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-lg',
            ],
            [
                'name'       => 'description',
                'label'      => __('Description'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-md',
            ],
        ];
    }

}
