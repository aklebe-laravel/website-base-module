<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Modules\Acl\app\Models\AclResource;
use Modules\WebsiteBase\app\Http\Livewire\DataTable\Base\DefaultDataTable;
use Modules\WebsiteBase\app\Services\SendNotificationService;

class NotificationConcern extends DefaultDataTable
{
    /**
     * Restrictions to allow this component.
     */
    public const array aclResources = [
        AclResource::RES_DEVELOPER,
        AclResource::RES_MANAGE_DESIGN,
        AclResource::RES_MANAGE_CONTENT,
    ];

    /**
     * @var array|string[]
     */
    public array $objectRelations = [
        'store',
        'notificationTemplate.viewTemplate',
    ];

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
            'css_group'  => '',
            'css_item'   => '',
            'options'    => app('system_base')->toHtmlSelectOptions(app(SendNotificationService::class)->getRegisteredChannelNames(),
                null,
                null,
                app('system_base')->toSelectOptionSimple('All Channels', NotificationEvent::FILTER_NOTIFICATION_CHANNEL_ALL)),
            'builder'    => function (Builder $builder, string $filterElementKey, string $filterValue) {
                if (!$filterValue || $filterValue === self::FILTER_NOTIFICATION_CHANNEL_ALL) {
                    return;
                }
                $builder->whereHas('notificationTemplate', function ($query) use ($filterValue) {
                    $query->where('notification_channel', $filterValue)->orWhere('notification_channel', $filterValue);
                });
            },
            'view'       => 'data-table::livewire.js-dt.filters.default-elements.select',
        ]);
    }

    /**
     * Overwrite to init your sort orders before session exists
     *
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
                'css_all'    => 'show-lg text-muted font-monospace text-end w-5',
                'searchable' => true,
                'sortable'   => true,
            ],
            [
                'name'     => 'is_enabled',
                'label'    => __('Enabled'),
                'view'     => 'data-table::livewire.js-dt.tables.columns.bool-gray-green',
                'css_all'  => 'show-lg w-max text-center',
                'sortable' => true,
            ],
            [
                'name'     => 'is_valid',
                'label'    => __('Valid'),
                'view'     => 'data-table::livewire.js-dt.tables.columns.bool-red-green',
                'css_all'  => 'show-lg w-max text-center',
                'sortable' => true,
            ],
            [
                'name'       => 'store.code',
                'label'      => __('Store'),
                'searchable' => true,
                'sortable'   => false, // unable to order by dot notations
                'css_all'    => 'show-lg w-fit text-start',
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
                'css_all'    => 'w-fit text-start',
            ],
            [
                'name'       => 'notificationTemplate.notification_channel',
                'label'      => __('Notification Channel'),
                'searchable' => true,
                'sortable'   => false, // unable to order by dot notations
                'css_all'    => 'w-fit text-start',
            ],
            [
                'name'       => 'priority',
                'label'      => __('Priority'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'show-lg w-fit text-start',
            ],
            [
                'name'       => 'description',
                'label'      => __('Description'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'show-md w-fit text-start',
            ],
        ];
    }

}
