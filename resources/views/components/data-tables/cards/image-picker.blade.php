{{-- Card of the image picker (MediaItemPicker), see DataTable BaseDataTable::$cardView --}}
@php
    use Illuminate\Support\Facades\Auth;
    use Modules\WebsiteBase\app\Http\Livewire\DataTable\MediaItemPicker;
    use Modules\WebsiteBase\app\Models\MediaItem;

    /** @var MediaItemPicker $this */
    /** @var MediaItem $item */
    $_itemId = (int) $item->id;
    $_isSelected = $_itemId === $this->pickedId;
    $_isOwn = Auth::id() && (int) $item->user_id === (int) Auth::id();
@endphp
<div wire:key="card-{{ $_itemId }}"
     x-on:click="$dispatch('media-item-picked', { id: {{ $_itemId }}, picker: @js($this->pickerId) })"
     title="{{ $item->description ?: $item->name }}"
        @class([
            'relative cursor-pointer overflow-hidden rounded-lg border-2 p-1 transition-all duration-200',
            'border-primary-500 bg-primary-50/50' => $_isSelected,
            'border-surface-200 hover:border-surface-400' => !$_isSelected,
        ])>

    <img src="{{ $item->final_thumb_medium_url }}"
         alt="{{ $item->name }}"
         loading="lazy"
         class="h-32 w-full rounded-md object-contain">

    <div class="mt-2 text-center text-xs font-medium text-surface-700 truncate">
        {{ $item->name }}
    </div>

    @if($_isOwn)
        <div class="absolute top-2 left-2 rounded-full bg-surface-50/80 p-1 text-secondary-600" title="{{ __('Own image') }}">
            <x-lucide-user class="h-3 w-3"/>
        </div>
    @endif

    @if($_isSelected)
        <div class="absolute top-2 right-2 rounded-full bg-primary-500 p-1 text-white">
            <x-lucide-check class="h-3 w-3"/>
        </div>
    @endif
</div>
