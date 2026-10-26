@props([
    'label',
    'icon' => 'lucide-chevron-right',
    'width' => 'min-w-[12rem]'
])

<div
        x-data="{ subOpen: false }"
        data-dropdown-node="true"
        class="relative w-full"
>
    <!-- SUB-TRIGGER -->
    <button
            x-ref="subButton"
            type="button"
            data-sub-trigger="true"
            @mouseenter="subOpen = true; clearTimer();"
            @mouseleave="if (!$event.toElement?.closest('[data-dropdown-node]')) { subOpen = false; }"
            class="w-full text-left px-4 py-2.5 text-sm text-surface-700 hover:bg-surface-100 hover:text-secondary-500 transition-colors flex items-center justify-between"
    >
        <span>{{ $label }}</span>
        <x-dynamic-component :component="$icon" class="h-4 w-4 opacity-60" />
    </button>

    <!-- SUB-CONTENT (Ermöglicht rekursive Verschachtelung im Slot) -->
    <div
            x-show="subOpen"
            x-anchor.right-start="$refs.subButton"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @mouseenter="clearTimer(); subOpen = true;"
            @mouseleave="subOpen = false; leaveElement($event.toElement);"
            class="z-55 {{ $width }} pl-1"
            style="display: none;"
    >
        <div class="w-full flex flex-col rounded-xl border border-surface-200 bg-surface-50 shadow-xl py-1.5 overflow-visible">
            {{ $slot }}
        </div>
    </div>
</div>
