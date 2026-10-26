<div class="space-y-4">
    <!-- Preview Trigger Button -->
    <div class="flex items-center gap-4">
        <div class="h-16 w-16 rounded-md border border-surface-300 bg-surface-100 flex items-center justify-center overflow-hidden">
            @if($selectedImageId)
                <img src="{{ $this->getImage($selectedImageId)['image'] }}" alt="Selected" class="h-full w-full object-cover">
            @else
                <x-icon name="photo" class="h-6 w-6 text-surface-400"/>
            @endif
        </div>

        <!-- FIX: Using the global $tsui helper object -->
        <x-button type="button"
                  x-on:click="$tsui.open.modal('image-picker-modal')"
                  color="primary">
            {{ __('Choose Image') }}
        </x-button>
    </div>

    <!-- Selection Modal -->
    <x-modal id="image-picker-modal" title="Select an Image" size="xl">
        <div class="grid grid-cols-3 gap-4 p-2">
            @foreach($images as $image)
                <!-- FIX: Using $tsui.close.modal to close the specific modal reference -->
                <div wire:click="selectImage('{{ $image['id'] }}')"
                     x-on:click="$tsui.close.modal('image-picker-modal')"
                        @class([
                            'relative cursor-pointer overflow-hidden rounded-lg border-2 p-1 transition-all duration-200 group',
                            'border-primary-500 bg-primary-50/50' => $selectedImageId === $image['id'],
                            'border-surface-200 hover:border-surface-400' => $selectedImageId !== $image['id']
                        ])>

                    <img src="{{ $image['image'] }}"
                         alt="{{ $image['label'] }}"
                         class="h-32 w-full rounded-md object-cover">

                    <div class="mt-2 text-center text-xs font-medium text-surface-700">
                        {{ $image['label'] }}
                    </div>

                    @if($selectedImageId === $image['id'])
                        <div class="absolute top-2 right-2 rounded-full bg-primary-500 p-1 text-white">
                            <x-icon name="check" class="h-3 w-3"/>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-modal>
</div>
