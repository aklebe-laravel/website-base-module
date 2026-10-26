@php
    declare(strict_types=1);
    $isActive = $active ?? false;
@endphp

<button
        type="button"
        class="{{
         \Illuminate\Support\Arr::toCssClasses([
            'w-full text-left px-4 py-2.5 text-sm transition-colors duration-150 ease-in-out flex items-center gap-3 whitespace-normal break-words',
            'bg-secondary-50 text-secondary-600 font-semibold' => $isActive,
            'text-surface-700 hover:bg-surface-100 hover:text-secondary-500' => !$isActive,
        ])
    }}"
        {{ $attributes }}
>
    <span class="block truncate w-full text-left">
        {{ $slot }}
    </span>
</button>
