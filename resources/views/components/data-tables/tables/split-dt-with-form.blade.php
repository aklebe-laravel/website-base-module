@php
    /**
     * @var string $modelName like "User" (@todo: only uses for bottom javascript, wich is used for image uploads)
     * @var string $livewireForm like "market::form.user"
     * @var string $livewireImportForm like "market::form.import-user"
     * @var string $livewireTable like "market::data-table.user"
     * @var mixed $formObjectId
     * @var bool $isFormOpen
     * @var array $objectInstanceDefaultValues
     */
    if (!isset($livewireTableOptions)) {
        $livewireTableOptions = [];
    }
    $livewireKeyForm = \Modules\SystemBase\app\Services\LivewireService::getKey('manage-default-form-key');
    $livewireKeyImportForm = \Modules\SystemBase\app\Services\LivewireService::getKey('manage-default-import-form-key');
    $livewireKeyDataTable = \Modules\SystemBase\app\Services\LivewireService::getKey('manage-default-dt-key');
@endphp
{{--@section('title', __($livewireTable))--}}
<div>
    {{--Import Form--}}
    @if ($livewireImportForm)
        <div>
            @livewire($livewireImportForm, [
                'relatedLivewireDataTable' => $livewireTable,
                'formObjectId' => null,
                'isFormOpen' => false,
            ], key($livewireKeyImportForm))
        </div>
    @endif

    {{--Form--}}
    @if ($livewireForm)
        <div>
            @livewire($livewireForm, [
                'relatedLivewireDataTable' => $livewireTable,
                'formObjectId' => $formObjectId ?? null,
                'isFormOpen' => $isFormOpen ?? false,
                'objectInstanceDefaultValues' => $objectInstanceDefaultValues,
            ], key($livewireKeyForm))
        </div>
    @endif

    {{--Data Table--}}
    <div>
        @livewire($livewireTable, array_merge([
            'relatedLivewireForm' => $livewireForm,
            'relatedLivewireImportForm' => $livewireImportForm,
            // 'editable' => true,
            // 'canAddRow' => true,
            // 'removable' => true,
            // 'selectable' => true,
            // 'hasCommands' => true,
        ], $livewireTableOptions), key($livewireKeyDataTable))
    </div>
</div>
