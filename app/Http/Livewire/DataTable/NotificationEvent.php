<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Modules\Acl\app\Models\AclResource;
use Modules\WebsiteBase\app\Http\Livewire\DataTable\Base\DefaultDataTable;
use Modules\WebsiteBase\app\Models\NotificationEvent as NotificationEventModel;
use Modules\WebsiteBase\app\Services\NotificationEventService;
use Modules\WebsiteBase\app\Services\SendNotificationService;

class NotificationEvent extends DefaultDataTable
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
        'users',
        'aclResources',
        'notificationConcerns.notificationTemplate',
    ];

    /**
     * @return void
     */
    protected function initFilters(): void
    {
        parent::initFilters();

        $this->addFilterElement('notification_channel', [
            'label'     => 'Channel',
            'default'   => 10,
            'position'  => 1700, // between elements rows and search
            'css_group' => '',
            'css_item'  => '',
            'options'   => app('system_base')->toHtmlSelectOptions(app(SendNotificationService::class)->getRegisteredChannelNames(),
                first: app('system_base')->toSelectOptionSimple('All Channels', NotificationEvent::FILTER_NOTIFICATION_CHANNEL_ALL)),
            'builder'   => function (Builder $builder, string $filterElementKey, string $filterValue) {
                if (!$filterValue || $filterValue === self::FILTER_NOTIFICATION_CHANNEL_ALL) {
                    return;
                }
                $builder->where(function (Builder $b) use ($filterValue) {
                    $b->where('force_channel', $filterValue);
                    $b->orWhereHas('notificationConcerns.notificationTemplate', function (Builder $b2) use ($filterValue) {
                        $b2->where('notification_channel', $filterValue);
                    });
                });
            },
            'view'      => 'data-table::livewire.js-dt.filters.default-elements.select',
        ]);
    }

    /**
     * Overwrite to init your sort orders before session exists
     *
     * @return void
     */
    protected function initSort(): void
    {
        $this->setSortAllCollections('is_enabled', 'desc');
        $this->setSortAllCollections('name', 'asc');
    }

    /**
     * Runs on every request, after the component is mounted or hydrated, but before any update methods are called
     *
     * @return void
     */
    protected function initBooted(): void
    {
        parent::initBooted();

        $this->rowCommands = [
            'notification_event_preview' => 'website-base::livewire.js-dt.tables.columns.buttons.notification-event-preview',
            'notification_event_launch'  => 'website-base::livewire.js-dt.tables.columns.buttons.notification-event-launch',
            ...$this->rowCommands,
        ];
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
                'css_all'    => 'show-lg w-max text-muted font-monospace justify-end text-end',
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
                'name'       => 'name',
                'searchable' => true,
                'sortable'   => true,
                'label'      => __('Description'),
                'css_all'    => 'w-fit text-start',
                'view'       => 'website-base::livewire.js-dt.tables.columns.notification-event-info',
            ],
            //// need this for default sort
            //[
            //    'name'       => 'event_code',
            //    'visible'    => false,
            //    'searchable' => true,
            //    'sortable'   => true,
            //    'css_all'  => 'w-fit text-start',
            //],
        ];
    }

    /**
     * @param  string|int  $livewireId
     * @param  string|int  $itemId
     *
     * @return bool
     */
    #[On('launch')]
    public function launch(string|int $livewireId, string|int $itemId): bool
    {
        if (!$this->checkLivewireId($livewireId)) {
            return false;
        }

        /** @var NotificationEventService $service */
        $service = app(NotificationEventService::class);

        // check for validItems() before send it to $service->launch()
        if (!($event = NotificationEventModel::with([])->validItems()->whereId($itemId)->count())) {
            $this->addErrorMessage(__('Event not found or disabled/invalid.'));

            return false;
        }

        //
        if ($service->launch((int) $itemId)) {
            $this->addSuccessMessage("Notification event was queued.");
        } else {
            $this->addErrorMessage("Something goes wrong.");
            $this->addErrorMessages($service->getErrors());
        }

        return false;
    }

}
