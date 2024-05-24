@php
    /**
     * @var string $modelName like "User" (@todo: only uses for bottom javascript, wich is used for image uploads)
     * @var string $livewireForm like "market-form-user"
     * @var string $livewireTable like "market-data-table-user"
     * @var mixed $formObjectId
     * @var bool $isFormOpen
     * @var array $objectModelInstanceDefaultValues
     */
    if (!isset($livewireTableOptions)) {
        $livewireTableOptions = [];
    }
    $livewireKey1 = \Modules\SystemBase\app\Services\LivewireService::getKey('manage-default-form-key');
    $livewireKey2 = \Modules\SystemBase\app\Services\LivewireService::getKey('manage-default-dt-key');
@endphp
{{--@section('title', __($livewireTable))--}}
<div>
    {{--Form--}}
    <div>
        {{--                            <div class="text-info">(before livewire form)</div>--}}
        @livewire($livewireForm, [
            'relatedLivewireDataTable' => $livewireTable,
            'formObjectId' => $formObjectId ?? null,
            'isFormOpen' => $isFormOpen ?? false,
            'objectModelInstanceDefaultValues' => $objectModelInstanceDefaultValues,
        ], key($livewireKey1))
    </div>

    {{--Data Table--}}
    <div>
        @livewire($livewireTable, array_merge([
            'relatedLivewireForm' => $livewireForm,
//            'editable' => true,
//            'canAddRow' => true,
//            'removable' => true,
//            'selectable' => true,
//            'hasCommands' => true,
        ], $livewireTableOptions), key($livewireKey2))
    </div>
</div>
