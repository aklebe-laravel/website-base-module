@php
    use Modules\WebsiteBase\app\Http\Livewire\ImagePicker;

    /** @var ImagePicker $this */
    $selectedImage = $this->selectedImage;
    // unique per picker, a form may contain several
    $modalId = 'image-picker-modal-'.$this->getId();
@endphp
{{-- window: the modal may teleport its content, so the event does not bubble up to this element --}}
<div class="space-y-4"
     x-on:media-item-picked.window="if ($event.detail.picker === @js($this->getId())) { $wire.selectImage($event.detail.id); $tsui.close.modal(@js($modalId)) }">
    {{-- Preview and trigger button --}}
    <div class="flex items-center gap-4">
        <div class="h-16 w-16 rounded-md border border-surface-300 bg-surface-100 flex items-center justify-center overflow-hidden">
            @if($selectedImage)
                <img src="{{ $selectedImage['image'] }}" alt="{{ $selectedImage['label'] }}" class="h-full w-full object-contain">
            @else
                <x-icon name="photo" class="h-6 w-6 text-surface-400"/>
            @endif
        </div>

        {{-- $tsui is the global TallStackUI helper. Component attributes compile {{ }} only, no @js() --}}
        <x-button type="button"
                  x-on:click="$tsui.open.modal('{{ $modalId }}')"
                  color="primary">
            {{ __('Choose Image') }}
        </x-button>

        @if($selectedImage)
            <x-button type="button" flat color="secondary" wire:click="selectImage(null)">
                {{ __('Remove') }}
            </x-button>
        @endif
    </div>

    {{-- Selection: data table as cards, loaded when the modal becomes visible --}}
    <x-modal :id="$modalId" :title="__('Select an Image')" size="5xl">
        <livewire:website-base::data-table.media-item-picker
                :picked-id="$selectedImageId"
                :picker-id="$this->getId()"
                wire:key="{{ $this->getId() }}-media-item-picker"
                lazy
        />
    </x-modal>
</div>
