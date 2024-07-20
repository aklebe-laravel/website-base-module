<?php

namespace Modules\WebsiteBase\app\Http\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
use Modules\WebsiteBase\app\Services\NotificationEventService;

class NotificationEvent extends BaseDataTable
{
    /**
     *
     */
    const FILTER_NOTIFICATION_CHANNEL_ALL = '';

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
        // Important to call this before calling parent::initMount()!
        foreach ($this->enabledCollectionNames as $collectionName => $enabled) {
            data_set($this->filtersDefaults, $collectionName.'.notification_channel',
                self::FILTER_NOTIFICATION_CHANNEL_ALL);
        }

        //
        parent::initMount();

        // add notification_channel to reset page to 1 when updated
        $this->filterSoftResetActivators[] = 'notification_channel';
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
            ...$this->rowCommands
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
                'name'       => 'name',
                'searchable' => true,
                'sortable'   => true,
                'label'      => __('Description'),
                'css_all'    => 'w-50',
                'view'       => 'website-base::livewire.js-dt.tables.columns.notification-event-info',
            ],
            // need this for default sort
            [
                'name'       => 'event_code',
                'visible'    => false,
                'searchable' => true,
                'sortable'   => true,
            ],
        ];
    }

    /**
     * @param $livewireId
     * @param $itemId
     *
     * @return bool
     */
    #[On('launch')]
    public function launch(mixed $livewireId, mixed $itemId): bool
    {
        if (!$this->checkLivewireId($livewireId)) {
            return false;
        }

        /** @var NotificationEventService $service */
        $service = app(NotificationEventService::class);

        // check for validItems() before send it to $service->launch()
        if (!($event = \Modules\WebsiteBase\app\Models\NotificationEvent::with([])
            ->validItems()
            ->whereId($itemId)
            ->count())) {
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

    /**
     * Overwrite this to add filters
     *
     * @param  Builder  $builder
     * @param  string   $collectionName
     *
     * @return void
     */
    protected function addCustomFilters(Builder $builder, string $collectionName)
    {
        switch ($v = data_get($this->filters, $collectionName.'.notification_channel')) {

            // no filter here
            case self::FILTER_NOTIFICATION_CHANNEL_ALL:
                break;

            default:
                $builder->where(function (Builder $b) use ($v) {
                    $b->where('force_channel', $v);
                    $b->orWhereHas('notificationConcerns.notificationTemplate', function (Builder $b2) use ($v) {
                        $b2->where('notification_channel', $v);
                    });
                });
                break;
        }
    }


}
