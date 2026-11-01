@php
    $selectedImage = $this->getImage($selectedImageId);
@endphp
<div class="space-y-4">
    {{-- Preview and trigger button --}}
    <div class="flex items-center gap-4">
        <div class="h-16 w-16 rounded-md border border-surface-300 bg-surface-100 flex items-center justify-center overflow-hidden">
            @if($selectedImage)
                <img src="{{ $selectedImage['image'] }}" alt="{{ $selectedImage['label'] }}" class="h-full w-full object-contain">
            @else
                <x-icon name="photo" class="h-6 w-6 text-surface-400"/>
            @endif
        </div>

        {{-- $tsui is the global TallStackUI helper --}}
        <x-button type="button"
                  x-on:click="$tsui.open.modal('image-picker-modal')"
                  color="primary">
            {{ __('Choose Image') }}
        </x-button>
    </div>

    {{-- Selection modal --}}
    <x-modal id="image-picker-modal" :title="__('Select an Image')" size="xl">
        <div class="grid grid-cols-3 gap-4 p-2">
            @foreach($images as $image)
                @php($isSelected = (int) $image['id'] === $selectedImageId)
                <div wire:click="selectImage({{ (int) $image['id'] }})"
                     x-on:click="$tsui.close.modal('image-picker-modal')"
                        @class([
                            'relative cursor-pointer overflow-hidden rounded-lg border-2 p-1 transition-all duration-200 group',
                            'border-primary-500 bg-primary-50/50' => $isSelected,
                            'border-surface-200 hover:border-surface-400' => !$isSelected,
                        ])>

                    <img src="{{ $image['image'] }}"
                         alt="{{ $image['label'] }}"
                         class="h-32 w-full rounded-md object-contain">

                    <div class="mt-2 text-center text-xs font-medium text-surface-700">
                        {{ $image['label'] }}
                    </div>

                    @if($isSelected)
                        <div class="absolute top-2 right-2 rounded-full bg-primary-500 p-1 text-white">
                            <x-icon name="check" class="h-3 w-3"/>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-modal>
</div>
