<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Modules\Acl\app\Models\AclResource;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;

class NotificationConcern extends BaseDataTable
{
    /**
     * Restrictions to allow this component.
     */
    public const aclResources = [
        AclResource::RES_DEVELOPER,
        AclResource::RES_MANAGE_DESIGN,
        AclResource::RES_MANAGE_CONTENT
    ];

    /**
     * @var array|array[]
     */
    protected array $filterConfig = [
        [
            'css_group' => 'col-12 col-md-3 text-start',
            'css_item'  => '',
            'view'      => 'data-table::livewire.js-dt.filters.rows-per-page.default',
        ],
        [
            'css_group' => 'col-12 col-md text-center',
            'css_item'  => '',
            'view'      => 'website-base::livewire.js-dt.commands.select-notification-channel',
        ],
        [
            'css_group' => 'col-12 col-md text-end',
            'css_item'  => '',
            'view'      => 'data-table::livewire.js-dt.filters.search.default',
        ],
        [
            'css_group' => 'col-12 col-md-1 text-end',
            'css_item'  => '',
            'view'      => 'data-table::livewire.js-dt.filters.settings.default',
        ],
    ];

    /**
     * Runs once, immediately after the component is instantiated, but before render() is called.
     * This is only called once on initial page load and never called again, even on component refreshes
     *
     * @return void
     */
    protected function initMount(): void
    {
        // $this->resetFilters();
        // Important to call this before calling parent::initMount()!
        foreach ($this->enabledCollectionNames as $collectionName => $enabled) {
            data_set($this->filtersDefaults, $collectionName.'.notificationTemplate.notification_channel',
                NotificationTemplate::FILTER_NOTIFICATION_CHANNEL_ALL);
        }

        //
        parent::initMount();

        // add notification_channel to reset page to 1 when updated
        $this->filterSoftResetActivators[] = 'notificationTemplate.notification_channel';
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
                    ]
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

    /**
     * Overwrite this to add filters
     *
     * @param  Builder  $builder
     * @param  string  $collectionName
     *
     * @return void
     */
    protected function addCustomFilters(Builder $builder, string $collectionName)
    {
        switch ($v = data_get($this->filters, $collectionName.'.notification_channel')) {

            // no filter here
            case NotificationTemplate::FILTER_NOTIFICATION_CHANNEL_ALL:
                break;

            default:
                $builder->whereHas('notificationTemplate', function ($query) use ($v) {
                    $query->where('notification_channel', $v)->orWhere('notification_channel', $v);
                });
                break;
        }
    }
}
