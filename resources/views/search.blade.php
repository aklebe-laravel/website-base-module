@php
    /**
     * @var string $searchStringLike
     * @var string $renderMode
     */

    $relevantUserId = Auth::id();
    $objectModelInstanceDefaultValues = [
        'user_id' => $relevantUserId,
    ];
    $livewireTableOptions = [
        'editable' => false,
        'selectable' => false,
        'hasCommands' => false,
        'searchStringLike' => $searchStringLike,
        'renderMode' => $renderMode,
    ];
@endphp
@if ($searchString)
    <div>
        <h2>{{ __('Search Results in Users') }}</h2>
        <div class="text-danger dec">
            {{ __('Search') . ': ' . $searchString }}
        </div>
        @php $livewireTable = 'website-base::data-table.user-search'; @endphp
        @include('website-base::components.data-tables.tables.dt-simple')
    </div>
@else
    <div class="alert alert-warning">
        {{ __('Invalid Search Input.') }}
    </div>
@endif
