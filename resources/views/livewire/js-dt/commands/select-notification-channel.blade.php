@php
    use Modules\WebsiteBase\app\Http\Livewire\DataTable\NotificationEvent;use Modules\WebsiteBase\app\Services\WebsiteService;

    /** @var \Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable $this */
    /** @var string $collectionName */
    $options = app('system_base')->toHtmlSelectOptions(WebsiteService::NOTIFICATION_CHANNELS,
        first: [NotificationEvent::FILTER_NOTIFICATION_CHANNEL_ALL => '['.__('All Channels').']']);
@endphp
<select wire:model="filters.{{ $collectionName }}.notification_channel"
        class="form-control {{ $this->isFilterDefault($collectionName, 'notification_channel') ? '' : 'bg-warning-subtle' }}">
    @foreach($options as $k => $v)
        <option value="{{ $k }}">{{ $v }}</option>
    @endforeach
</select>
