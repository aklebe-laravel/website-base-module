@php
    if (!isset($livewireTableOptions)) {
        $livewireTableOptions = [];
    }
    $livewireTableKey = 'manage-default-dt-non-edit';
@endphp
<div>
    {{--Data Table--}}
    <div>
        @livewire($livewireTable, array_merge([
            'editable' => false,
            'selectable' => false,
            'hasCommands' => false,
        ], $livewireTableOptions), key($livewireTableKey))
    </div>
</div>

<div>
    @if($footerView ?? null)
        @include($footerView)
    @endif
</div>
