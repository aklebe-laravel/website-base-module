{{--
    Inline drop zone for media uploads.
    Replaces <x-upload> (TallStackUI), which hides the drop zone inside a floating dropdown.
    The invisible file input covers the whole zone, so the browser handles both
    clicking (file dialog) and dropping natively. Upload goes through $wire.uploadMultiple
    into $files, which triggers MediaItemFileUpload::updatedFiles().
--}}
<div
    x-data="{
        dragging: false,
        uploading: false,
        progress: 0,
        error: false,
        upload(fileList) {
            if (this.uploading || !fileList.length) return;

            // Copy the FileList, so the input can be reset without losing the files
            const files = Array.from(fileList);

            this.uploading = true;
            this.error = false;
            this.progress = 0;

            $wire.uploadMultiple('files', files,
                () => { this.uploading = false; this.progress = 0; },
                () => { this.uploading = false; this.progress = 0; this.error = true; },
                (event) => { this.progress = event.detail.progress; }
            );

            // Allow selecting the same file again
            this.$refs.input.value = '';
        }
    }"
>
    <div
        class="dark:border-dark-600 dark:bg-dark-800/50 relative flex h-28 w-full cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 transition"
        :class="{ 'bg-primary-100': dragging, 'opacity-60': uploading }"
    >
        <input
            type="file"
            x-ref="input"
            multiple
            @if($acceptExtensions) accept="{{ $acceptExtensions }}" @endif
            class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0 outline-hidden"
            :disabled="uploading"
            @dragenter="dragging = true"
            @dragleave="dragging = false"
            @drop="dragging = false"
            @change="upload($event.target.files)"
        />

        {{-- Upload icon --}}
        <svg class="dark:text-dark-300 h-6 w-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z"/>
        </svg>

        <span class="text-md dark:text-dark-300 mt-1 font-bold text-gray-600"
              x-text="uploading ? '{{ __('Uploading ...') }}' : (dragging ? '{{ __('Drop files here') }}' : '{{ __('Drag & drop images or click to select') }}')">
            {{ __('Drag & drop images or click to select') }}
        </span>

        @if($acceptExtensions)
            <span class="mx-4 mt-1 text-center text-sm text-gray-500 dark:text-dark-400">
                {{ str_replace(',', ', ', $acceptExtensions) }}
            </span>
        @endif
    </div>

    {{-- Progress bar --}}
    <div x-show="uploading" x-cloak class="mt-2 flex h-1 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-dark-800">
        <div class="flex flex-col justify-center overflow-hidden whitespace-nowrap rounded-full bg-green-600 text-center text-xs text-white transition duration-500"
             :style="'width: ' + progress + '%'"></div>
    </div>

    {{-- Error --}}
    <div x-show="error" x-cloak class="mt-2 flex w-full items-center justify-center">
        <span class="font-semibold text-red-500">{{ __('Upload failed.') }}</span>
    </div>

    @error('files.*')
        <span class="mt-1 block text-sm font-medium text-red-500">{{ $message }}</span>
    @enderror
</div>
