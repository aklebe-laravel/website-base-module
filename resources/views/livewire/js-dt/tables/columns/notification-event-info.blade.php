@php
    /**
     * @var \Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable $this
     * @var \Modules\WebsiteBase\app\Models\NotificationEvent $item
     * @var string $name
     * @var mixed $value
     **/

    use Modules\Acl\app\Services\UserService;

    /** @var UserService $userService */
    $userService = app(UserService::class);

    $namesStr = '';
    if ($eventUsers = $item->getEventUsers()) {
        $eventUsers->limit(10);
        $namesStr = implode(', ', $eventUsers->pluck('name')->toArray());
    }
    // renew builder
    $eventUsers = $item->getEventUsers();
    $eventConcernChannels = $item->notificationConcerns->pluck('notificationTemplate.notification_channel')->toArray();
@endphp
{{--<div class="{{ $column['css_all'] }} {{ $column['css_body'] }}">--}}
<div class="d-flex">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-6">
                <button
                        class="btn-link link-primary"
                        wire:click="$dispatchTo('{{ $this->relatedLivewireForm }}', 'open-form', {id: '{{ data_get($item, $this->columnNameId) }}' })"
                >
                    {{ $item->name }}
                </button>
            </div>
            <div class="col-12 col-md-6">
                <span class="decent">{{ $item->event_code }}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <span class="{{ ($item->force_channel ? 'text-danger' : 'text-secondary') }}">Force: {{ $item->force_channel ?: '-' }}</span>
            </div>
            <div class="col-12 col-md-6">
                @if($item->notificationConcerns)
                    {{ $item->notificationConcerns->count() }} Concerns: <span
                            class="{{ ($eventConcernChannels ? 'text-danger' : 'text-secondary') }}">
                        {{ implode(', ', $eventConcernChannels) ?: '-' }}
                    </span>
                @else
                    -
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                @if (!$item->notificationConcerns->count())
                    <span class="text-warning-emphasis">
                        Use special content:
                        @include('data-table::livewire.js-dt.tables.columns.strlen-kb', ['value' => $item->content])
                    </span>
                @endif
            </div>
            <div class="col-12 col-md-6">
                {{--Users: <span class="badge rounded-pill bg-white text-muted">{{ count($item->users) }}</span>,--}}
                {{--Acl's: <span class="badge rounded-pill bg-white text-muted">{{ count($item->aclResources) }}</span>,--}}
                Total Users:
                <span class="p-0 m-0"
                      data-bs-toggle="popover"
                      data-bs-trigger="hover focus"
                      title="{{ __('Total Users') }}"
                      data-bs-content="{{ $namesStr }}">
                    <span class="badge bg-secondary cursor-pointer">{{ $eventUsers ? $eventUsers->count() : 0 }}</span>
                </span>

            </div>
        </div>
        <div class="row hide-mobile-show-md">
            <div class="col-12">
                <span class="fst-italic decent">{{ $item->description ?: '-' }}</span>
            </div>
        </div>
    </div>
</div>

</div>
