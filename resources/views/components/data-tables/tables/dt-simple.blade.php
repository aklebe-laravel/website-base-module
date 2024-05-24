@php
    if (!isset($livewireTableOptions)) {
        $livewireTableOptions = [];
    }
    $livewireTableKey = \Modules\SystemBase\app\Services\LivewireService::getKey('manage-default-dt-key');
@endphp
<div>
    {{--Data Table--}}
    <div>
        @livewire($livewireTable, array_merge([
            'editable' => true,
            'selectable' => false,
            'hasCommands' => true,
        ], $livewireTableOptions), key($livewireTableKey))
    </div>
</div>

<div>
    @if($footerView ?? null)
        @include($footerView)
    @endif
</div>
