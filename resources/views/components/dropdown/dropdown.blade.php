@php
    declare(strict_types=1);
    $uniqueId = 'dd-' . app('system_base')->addUniqueCounter('dd-');
@endphp

<div
        wire:ignore
        x-data="{
        open: false,
        timeout: null,
        clearTimer() {
            if (this.timeout) {
                clearTimeout(this.timeout);
                this.timeout = null;
            }
        },
        leaveElement(el) {
            this.clearTimer();
            if (el && el.closest('[data-dropdown-node]')) {
                return;
            }
            this.timeout = setTimeout(() => {
                this.closeAll();
            }, 150);
        },
        closeAll() {
            this.open = false;
            // Close all nested submenus dynamically using DOM state
            $el.querySelectorAll('[data-dropdown-node]').forEach(node => {
                if (node.__x) node.__x.$data.subOpen = false;
            });
        }
    }"
        class="inline-block"
        @click.away="closeAll()"
        {{-- Globaler Listener: Schließt das Menü bei jedem Item-Klick, außer bei Sub-Triggern --}}
        @click="if ($event.target.closest('button, a') && !$event.target.closest('[data-sub-trigger]')) { closeAll(); }"
>

    <!-- TRIGGER -->
    <div
            x-ref="button"
            @if($component->hover)
                @mouseenter="clearTimer(); open = true"
            @mouseleave="leaveElement($event.toElement)"
            @else
                @click.stop="open = !open"
            @endif
            class="inline-flex {{ $component->triggerClass }} cursor-pointer"
    >
        {{ $trigger ?? '' }}
    </div>

    <!-- CONTENT -->
    <div
            x-show="open"
            x-anchor.{{ $component->placement }}="$refs.button"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @if($component->hover)
                @mouseenter="clearTimer()"
            @mouseleave="leaveElement($event.toElement)"
            @endif
            class="z-50 {{ $component->width }} max-w-[95vw] sm:max-w-xs md:max-w-md break-words pt-1"
            style="display: none;"
    >
        <div class="w-full rounded-xl border border-surface-200 bg-surface-50 shadow-xl py-2 overflow-visible">
            {{ $content ?? '' }}
        </div>
    </div>
</div>
