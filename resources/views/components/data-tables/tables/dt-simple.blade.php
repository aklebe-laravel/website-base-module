@php
    if (!isset($livewireTableOptions)) {
        $livewireTableOptions = [];
    }
    $livewireTableKey = 'manage-default-dt-simple';
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
