@php
    /**
     * @var string $modelName like "User" (@todo: only uses for bottom javascript, wich is used for image uploads)
     * @var string $livewireForm like "website-base::form.user"
     * @var string $livewireTable like "website-base::data-table.user"
     * @var mixed $formObjectId
     * @var bool $isFormOpen
     * @var array $objectInstanceDefaultValues
     */

    use Modules\SystemBase\app\Services\LivewireService;

    if (!isset($livewireTableOptions)) {
        $livewireTableOptions = [];
    }
    $livewireKeyForm = LivewireService::getKey('manage-default-form-key');
    $livewireKeyDataTable = LivewireService::getKey('manage-default-dt-key');
@endphp
{{--@section('title', __($livewireTable))--}}
<div>
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
            // 'editable' => true,
            // 'canAddRow' => true,
            // 'removable' => true,
            // 'selectable' => true,
            // 'hasCommands' => true,
        ], $livewireTableOptions), key($livewireKeyDataTable))
    </div>
</div>
