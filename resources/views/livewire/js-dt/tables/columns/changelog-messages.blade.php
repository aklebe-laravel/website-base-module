@php
    /**
     * @var \Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable $this
     * @var \Modules\WebsiteBase\app\Models\Changelog $item
     * @var string $name
     * @var mixed $value
     **/
$_lines = explode("\n", $item->messages);
$_printed = 0;
@endphp
<div class="">
    <div class="fw-bold">{{ $item->path ? $item->path : '[APP]' }}</div>
    @foreach($_lines as $_line)
        @php
            if (!trim($_line)) continue;
            if ($_printed > 3) {
                break;
            }
            if ($_printed > 2) {
                $_line = '...';
            }
            $_printed++;
        @endphp
        {{ $_line }}<br>
    @endforeach
</div>

<div class="text-danger-emphasis decent">
    <span class="badge rounded-pill bg-white">
        @if ($item->messages_staff)
            <x-lucide-check class="h-5 w-5 text-success"/>
        @else
            <x-lucide-x class="h-5 w-5 text-danger"/>
        @endif
    </span>
    <span class="{{ ($item->messages_staff) ? 'text-success' : '' }}">
        Staff Messages
    </span>

    <span class="badge rounded-pill bg-white">
        @if ($item->messages_public)
            <x-lucide-check class="h-5 w-5 text-success"/>
        @else
            <x-lucide-x class="h-5 w-5 text-danger"/>
        @endif
    </span>
    <span class="{{ ($item->messages_public) ? 'text-success' : '' }}">
        Public Messages
    </span>

</div>