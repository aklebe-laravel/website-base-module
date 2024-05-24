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
            <span class="bi bi-check text-success"></span>
        @else
            <span class="bi bi-x text-danger"></span>
        @endif
    </span>
    <span class="{{ ($item->messages_staff) ? 'text-success' : '' }}">
        Staff Messages
    </span>

    <span class="badge rounded-pill bg-white">
        @if ($item->messages_public)
            <span class="bi bi-check text-success"></span>
        @else
            <span class="bi bi-x text-danger"></span>
        @endif
    </span>
    <span class="{{ ($item->messages_public) ? 'text-success' : '' }}">
        Public Messages
    </span>

</div>